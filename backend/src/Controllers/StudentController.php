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

    private function getTotalStudentCount(): int
{
    $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    return (int) $stmt->fetchColumn();
    
}

private function getOverallPercentile($studentId): float
{
    // 1. Get this student's total marks
    $stmt = $this->db->prepare("
        SELECT SUM(obtained_mark) AS total_mark
        FROM student_assessments
        WHERE student_id = ?
    ");
    $stmt->execute([$studentId]);
    $studentTotal = (float) $stmt->fetchColumn();

    // 2. Get total marks of all students
    $stmt = $this->db->query("
        SELECT student_id, SUM(obtained_mark) AS total_mark
        FROM student_assessments
        GROUP BY student_id
    ");
    $allTotals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Count how many students this student outperformed
    $below = 0;
    foreach ($allTotals as $row) {
        if ((float)$row['total_mark'] < $studentTotal) {
            $below++;
        }
    }

    $totalStudents = count($allTotals);
    if ($totalStudents === 0) return 0;

    return round(($below / $totalStudents) * 100);
}

private function calculateStudentRank(int $studentId): string
{
    $stmt = $this->db->query("
        SELECT student_id, SUM(obtained_mark) AS total_mark
        FROM student_assessments
        GROUP BY student_id
        ORDER BY total_mark DESC
    ");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rank = 1;
    foreach ($all as $row) {
        if ((int)$row['student_id'] === (int)$studentId) {
            break;
        }
        $rank++;
    }

    $total = count($all);
    return "Rank $rank of $total";
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

        return $this->json($response, [
   'student' => [
    'name' => $user['name'],
    'matric_number' => $user['matric_number'],
    'semester' => $courses[0]['semester'] ?? 'Unknown',
    'rank' => $this->calculateStudentRank($studentId),
    'percentile' => $this->getOverallPercentile($studentId),
    'total_students' => $this->getTotalStudentCount(),
],

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

  public function viewCourseMarks(Request $request, Response $response, $args)
{
    $studentId = $args['studentId'];
    $courseId = $args['courseId'];

    // Validate inputs
    if (!is_numeric($studentId) || !is_numeric($courseId)) {
        $response->getBody()->write(json_encode(['error' => 'Invalid student or course ID']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $sql = "
    SELECT 
        a.id AS assessment_id,
        a.title AS component,
        a.max_mark,
        a.weight_percentage,
        sa.obtained_mark,
        a.course_id,
        c.course_name,
        c.course_code,
        u.name AS lecturer_name,
        rr.status AS remark_status
    FROM assessments a
    JOIN courses c ON c.id = a.course_id
    JOIN lecturer_courses lc ON lc.course_id = c.id
    JOIN users u ON u.id = lc.lecturer_id
    LEFT JOIN student_assessments sa 
        ON sa.assessment_id = a.id AND sa.student_id = :student_id
    LEFT JOIN remark_requests rr 
        ON rr.id = (
            SELECT id FROM remark_requests 
            WHERE assessment_id = a.id AND student_id = sa.student_id
            ORDER BY created_at DESC LIMIT 1
        )
    WHERE a.course_id = :course_id
    ORDER BY a.id ASC
";


    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':student_id' => $studentId,
            ':course_id' => $courseId
        ]);
        $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($components)) {
            $response->getBody()->write(json_encode(['error' => 'No assessments found for this course and student']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $totalObtained = 0;
        $totalMax = 0;

        foreach ($components as $c) {
            if ($c['obtained_mark'] !== null && is_numeric($c['max_mark']) && $c['max_mark'] > 0) {
                $totalObtained += $c['obtained_mark'];
                $totalMax += $c['max_mark'];
            }
        }

        $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;

        $data = [
            'components' => $components,
            'summary' => [
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage
            ]
        ];

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
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
        $assessmentTypes = [];

        foreach ($courses as $course) {
            // 2. Get assessments for this course (using type now)
            $stmtAssessments = $this->db->prepare("
            SELECT a.id, a.type
            FROM assessments a
            WHERE a.course_id = :courseId
            ORDER BY a.id ASC
        ");
            $stmtAssessments->execute([':courseId' => $course['id']]);
            $assessments = $stmtAssessments->fetchAll(PDO::FETCH_ASSOC);

            // Store types only once
            if (empty($assessmentTypes)) {
                $assessmentTypes = array_column($assessments, 'type');
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

        // Final output with types instead of titles
        $final = [
            'assessments' => $assessmentTypes,
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

    public function updateProfile(Request $request, Response $response, array $args): Response
    {
        $user = $request->getHeaderLine('X-User');
        $student = json_decode($user, true);
        $studentId = $student['id'] ?? null;

        if (!$studentId || $studentId != $args['id']) {
            return $this->json($response, ['error' => 'Unauthorized'], 401);
        }

        $data = $request->getParsedBody();

        $fields = [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null
        ];

        // Optional password update
        if (!empty($data['password'])) {
            $fields['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Build dynamic SQL SET clause
        $setClause = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($fields)));
        $stmt = $this->db->prepare("UPDATE users SET $setClause WHERE id = :id");
        $fields['id'] = $studentId;
        $stmt->execute($fields);

        // Return fixed structure
        return $this->json($response, [
            'message' => 'Profile updated successfully',
            'profile' => [
                'id' => $studentId,
                'name' => $fields['name'],
                'email' => $fields['email'],
                'matric_number' => $student['matric_number'],
                'semester' => $student['semester'] ?? null,
                'role' => 'student'
            ]
        ]);
    }




    // 
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
