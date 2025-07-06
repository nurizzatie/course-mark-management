<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;

class StudentController
{
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('db');
    }

    // =========================
    // 📌 STUDENT DASHBOARD
    // =========================
    public function dashboard(Request $request, Response $response, array $args): Response
    {
        $studentId = $args['id'];

        // Get student info
        $stmt = $this->db->prepare("SELECT name, matric_number FROM users WHERE id = ?");
        $stmt->execute([$studentId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return $this->json($response, ['error' => 'Student not found'], 404);
        }

        // Get enrolled courses with progress
        $stmt = $this->db->prepare("
            SELECT c.id, c.course_code, c.course_name, c.semester,
                   COUNT(a.id) AS total_assessments,
                   COUNT(CASE WHEN sa.obtained_mark IS NOT NULL THEN 1 END) AS marked_assessments
            FROM courses c
            JOIN student_courses sc ON sc.course_id = c.id
            JOIN assessments a ON a.course_id = c.id
            LEFT JOIN student_assessments sa ON sa.assessment_id = a.id AND sa.student_id = ?
            WHERE sc.student_id = ?
            GROUP BY c.id
        ");
        $stmt->execute([$studentId, $studentId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add progress and percentile to each course
        foreach ($courses as &$course) {
            $courseId = $course['id'];
            $course['progress'] = ($course['total_assessments'] > 0)
                ? round(($course['marked_assessments'] / $course['total_assessments']) * 100)
                : 0;
            $course['percentile'] = $this->getCoursePercentile($courseId, $studentId);
        }

        // Summary data
        $summaryCards = [
            ['title' => 'Average Mark', 'value' => '78%', 'icon' => '📊'],
            ['title' => 'Courses Enrolled', 'value' => count($courses), 'icon' => '📘'],
            ['title' => 'Completed Assessments', 'value' => '7 / 12', 'icon' => '✅'],
            ['title' => 'Upcoming', 'value' => 'Final Exam – 14 Jul', 'icon' => '⏰'],
        ];

        return $this->json($response, [
            'student' => [
                'name' => $user['name'],
                'matric_number' => $user['matric_number'],
                'semester' => $courses[0]['semester'] ?? 'Unknown',
                'rank' => 'Top 15%',
                'percentile' => 75, // Optional: overall percentile
                'total_students' => 40
            ],
            'summaryCards' => $summaryCards,
            'courses' => $courses
        ]);
    }

    private function getCoursePercentile($courseId, $studentId)
    {
        $stmt = $this->db->prepare("
            SELECT sa.student_id,
                   SUM((sa.obtained_mark / a.max_mark) * a.weight_percentage) AS total_contribution
            FROM student_assessments sa
            JOIN assessments a ON sa.assessment_id = a.id
            WHERE a.course_id = ?
            GROUP BY sa.student_id
            ORDER BY total_contribution DESC
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = count($students);
        foreach ($students as $i => $s) {
            if ($s['student_id'] == $studentId) {
                return round((($total - ($i + 1)) / $total) * 100, 2);
            }
        }
        return 0;
    }

    // =========================
    // 📌 COURSE INFO & MARKS
    // =========================
    public function getCourseInfo(Request $request, Response $response, array $args): Response
    {
        $stmt = $this->db->prepare("SELECT course_name AS name, course_code AS code FROM courses WHERE id = :id");
        $stmt->execute([':id' => $args['id']]);
        return $this->json($response, $stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function viewCourseMarks(Request $request, Response $response, array $args): Response
    {
        $stmt = $this->db->prepare("
            SELECT a.id AS assessment_id, a.title AS component, a.max_mark, a.weight_percentage,
                   c.course_name, c.course_code,
                   sa.obtained_mark, rr.status AS remark_status
            FROM assessments a
            JOIN courses c ON a.course_id = c.id
            JOIN student_assessments sa ON sa.assessment_id = a.id AND sa.student_id = :student_id
            LEFT JOIN remark_requests rr ON rr.assessment_id = a.id AND rr.student_id = :student_id
            WHERE a.course_id = :course_id
        ");
        $stmt->execute([
            ':student_id' => $args['studentId'],
            ':course_id' => $args['courseId']
        ]);
        $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = ['total_obtained' => 0, 'total_max' => 0];
        foreach ($components as &$comp) {
            $obtained = $comp['obtained_mark'] ?? 0;
            $max = $comp['max_mark'];
            $weight = $comp['weight_percentage'];
            $contribution = $max > 0 ? ($obtained / $max) * $weight : 0;

            $summary['total_obtained'] += $obtained;
            $summary['total_max'] += $max;
            $comp['contribution'] = round($contribution, 2);
            $comp['remark_requested'] = $comp['remark_status'] !== null;
        }
        $summary['percentage'] = $summary['total_max'] > 0
            ? round(($summary['total_obtained'] / $summary['total_max']) * 100, 2)
            : 0;

        return $this->json($response, [
            'components' => $components,
            'summary' => $summary
        ]);
    }

    // =========================
    //  REMARK REQUEST
    // =========================
    public function submitRemarkRequest(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        if (!$body['student_id'] || !$body['assessment_id'] || !$body['justification']) {
            return $this->json($response, ['error' => 'Missing required fields.'], 400);
        }

        $stmt = $this->db->prepare("
            INSERT INTO remark_requests (student_id, assessment_id, justification, status, created_at)
            VALUES (:student_id, :assessment_id, :justification, 'pending', NOW())
        ");
        $stmt->execute([
            ':student_id' => $body['student_id'],
            ':assessment_id' => $body['assessment_id'],
            ':justification' => $body['justification']
        ]);

        return $this->json($response, ['message' => 'Remark request submitted successfully.'], 201);
    }

    // =========================
    //  ASSESSMENT COMPARISON
    // =========================
    //  Fetch list of assessments for a course (for dropdown)
    public function getAssessmentsByCourse(Request $request, Response $response, array $args): Response
    {
        $courseId = $args['id'];

        $stmt = $this->db->prepare("
            SELECT id, title 
            FROM assessments 
            WHERE course_id = :courseId
        ");
        $stmt->execute([':courseId' => $courseId]);
        $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($assessments));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

     public function compareAssessmentMarks(Request $request, Response $response, array $args): Response
{
    $courseId = $args['courseId'];
    $assessmentId = $args['assessmentId'];
    $studentId = $request->getQueryParams()['student_id'] ?? null;

    $stmt = $this->db->prepare("
        SELECT 
            sa.student_id,
            u.name AS student_name,
            sa.obtained_mark AS total_contribution
        FROM student_assessments sa
        JOIN assessments a ON sa.assessment_id = a.id
        JOIN users u ON sa.student_id = u.id
        WHERE a.course_id = :courseId AND sa.assessment_id = :assessmentId
    ");
    $stmt->execute([
        ':courseId' => $courseId,
        ':assessmentId' => $assessmentId
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalMarks = 0;
    $studentCount = count($results);

    foreach ($results as &$row) {
        $totalMarks += $row['total_contribution'];

        // Anonymize labels
        if ($studentId && $row['student_id'] == $studentId) {
            $row['student_label'] = 'You';
        } else {
            $row['student_label'] = 'Student ' . substr(md5($row['student_id']), 0, 5);
        }

        unset($row['student_name'], $row['student_id']);
    }

    $average = $studentCount > 0 ? round($totalMarks / $studentCount, 2) : 0;

    $response->getBody()->write(json_encode([
        'data' => $results,
        'class_average' => $average
    ]));

    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}




    // =========================
    // 📌 RANKING & TABLE
    // =========================
    public function getStudentRank(Request $request, Response $response, array $args): Response
    {
        $courseId = $args['id'];
        $studentId = $args['studentId'];

        $stmt = $this->db->prepare("
            SELECT sa.student_id,
                   SUM((sa.obtained_mark / a.max_mark) * a.weight_percentage) AS total_contribution
            FROM student_assessments sa
            JOIN assessments a ON sa.assessment_id = a.id
            WHERE a.course_id = ?
            GROUP BY sa.student_id
            ORDER BY total_contribution DESC
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = count($students);
        foreach ($students as $i => $s) {
            if ($s['student_id'] == $studentId) {
                return $this->json($response, [
                    'rank' => $i + 1,
                    'percentile' => round((($total - ($i + 1)) / $total) * 100, 2),
                    'total_students' => $total
                ]);
            }
        }

        return $this->json($response, ['rank' => null, 'percentile' => null, 'total_students' => $total]);
    }

    public function getRankTable(Request $request, Response $response, array $args): Response
    {
        $courseId = $args['id'];

        $stmt = $this->db->prepare("
            SELECT sa.student_id, sa.assessment_id, sa.obtained_mark,
                   a.title, a.max_mark, u.name
            FROM student_assessments sa
            JOIN assessments a ON sa.assessment_id = a.id
            JOIN users u ON sa.student_id = u.id
            WHERE a.course_id = ?
        ");
        $stmt->execute([$courseId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($rows as $r) {
            $sid = $r['student_id'];
            if (!isset($students[$sid])) {
                $students[$sid] = [
                    'student_id' => $sid,
                    'name' => $r['name'],
                    'total' => 0,
                    'assessments' => []
                ];
            }
            $percentage = $r['obtained_mark'] / $r['max_mark'];
            $students[$sid]['total'] += $percentage;
            $students[$sid]['assessments'][] = [
                'title' => $r['title'],
                'score' => $r['obtained_mark'],
                'max' => $r['max_mark']
            ];
        }

        usort($students, fn($a, $b) => $b['total'] <=> $a['total']);
        $total = count($students);
        foreach ($students as $i => &$s) {
            $s['rank'] = $i + 1;
            $s['percentile'] = round((($total - ($i + 1)) / $total) * 100, 2);
            unset($s['total']);
        }

        return $this->json($response, array_values($students));
    }

    public function getPerformanceChart(Request $request, Response $response, array $args): Response
{
    $studentId = $args['studentId'];

    // 1. Get list of courses the student enrolled in
    $stmt = $this->db->prepare("
    SELECT c.id, c.course_name AS name
    FROM courses c
    JOIN student_courses sc ON sc.course_id = c.id
    WHERE sc.student_id = :studentId
");

    $stmt->execute([':studentId' => $studentId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    $assessmentTitles = [];

    foreach ($courses as $course) {
        // 2. Get assessments for this course
        $stmtAssessments = $this->db->prepare("
            SELECT a.id, a.title
            FROM assessments a
            WHERE a.course_id = :courseId
            ORDER BY a.id ASC
        ");
        $stmtAssessments->execute([':courseId' => $course['id']]);
        $assessments = $stmtAssessments->fetchAll(PDO::FETCH_ASSOC);

        // Store titles only once (for the first course)
        if (empty($assessmentTitles)) {
            $assessmentTitles = array_column($assessments, 'title');
        }

        // 3. Get student's marks for each assessment
        $marks = [];
        foreach ($assessments as $assessment) {
            $stmtMark = $this->db->prepare("
                SELECT obtained_mark 
                FROM student_assessments 
                WHERE student_id = :studentId AND assessment_id = :assessmentId
            ");
            $stmtMark->execute([
                ':studentId' => $studentId,
                ':assessmentId' => $assessment['id']
            ]);
            $mark = $stmtMark->fetchColumn();
            $marks[] = $mark !== false ? floatval($mark) : null;
        }

        $results[] = [
            'course' => $course['name'],
            'marks' => $marks
        ];
    }

    // Final output with titles
    $final = [
        'assessments' => $assessmentTitles,
        'courses' => $results
    ];

    $response->getBody()->write(json_encode($final));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}

public function getStudentProfile(Request $request, Response $response, array $args): Response
{
    $userHeader = $request->getHeaderLine('X-User');
    $user = json_decode($userHeader, true);

    if (!$user || !isset($user['id'])) {
        $response->getBody()->write(json_encode([
            'error' => 'Invalid or missing user header.'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $userId = $user['id'];

    try {
        // Get user details
        $stmt = $this->db->prepare("SELECT id, name, email, matric_number FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC); 
        if (!$profile) {
            $response->getBody()->write(json_encode(['error' => 'User not found.']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Get enrolled courses
        $stmt = $this->db->prepare("
            SELECT c.course_code, c.course_name, c.semester, c.year
            FROM courses c
            JOIN student_courses sc ON sc.course_id = c.id
            WHERE sc.student_id = ?
        ");
        $stmt->execute([$userId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Use latest course semester (if any)
        $latestSemester = $courses[0]['semester'] ?? 'Unknown';

        // Add semester manually into profile
        $profile['semester'] = $latestSemester;

        $response->getBody()->write(json_encode([
            'profile' => $profile,
            'courses' => $courses
        ]));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (\PDOException $e) {
        $response->getBody()->write(json_encode(['error' => 'Server error while fetching profile.']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}

public function updateStudentProfile(Request $request, Response $response, array $args): Response
{
    $userHeader = $request->getHeaderLine('X-User');
    if (!$userHeader) {
        $response->getBody()->write(json_encode(['error' => 'Missing X-User header.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $user = json_decode($userHeader, true);
    $userId = $user['id'] ?? null;

    if (!$userId) {
        $response->getBody()->write(json_encode(['error' => 'Invalid user data.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $data = $request->getParsedBody();

    $name = $data['name'] ?? null;
    $email = $data['email'] ?? null;
    $matric = $data['matric_number'] ?? null;

    if (!$name || !$email || !$matric) {
        $response->getBody()->write(json_encode(['error' => 'Missing required fields.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, matric_number = ? WHERE id = ?");
        $stmt->execute([$name, $email, $matric, $userId]);

        $response->getBody()->write(json_encode(['message' => 'Profile updated successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\PDOException $e) {
        $response->getBody()->write(json_encode(['error' => 'Failed to update profile']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}

 // 📌 NOTIFICATIONS
    // =========================
    public function getNotifications(Request $request, Response $response, $args): Response
    {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$args['id']]);
        return $this->json($response, ['notifications' => $stmt->fetchAll()]);
    }

    public function markNotificationSeen(Request $request, Response $response, $args): Response
    {
        $stmt = $this->db->prepare("UPDATE notifications SET seen = 1 WHERE id = ?");
        $stmt->execute([$args['id']]);
        return $this->json($response, ['message' => 'Notification marked as seen']);
    }

    // =========================
    // 📌 Helper
    // =========================
    private function json(Response $response, $data, $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
