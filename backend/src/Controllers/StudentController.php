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

    // ===============================
    // STUDENT DASHBOARD ENDPOINT
    // ===============================
    public function dashboard(Request $request, Response $response, array $args): Response
    {
        $studentId = $args['id'];

        // 1. Get student info
        $stmt = $this->db->prepare("SELECT name, matric_number FROM users WHERE id = ?");
        $stmt->execute([$studentId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Student not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // 2. Get enrolled courses with progress data
        $stmt = $this->db->prepare("
            SELECT
              c.id,
              c.course_code,
              c.course_name,
              c.semester,
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

        // 3. Add progress field to each course
        foreach ($courses as &$course) {
            $total = (int) $course['total_assessments'];
            $marked = (int) $course['marked_assessments'];
            $course['progress'] = $total > 0 ? round(($marked / $total) * 100) : 0;
        }

        // 4. Derive semester
        $semester = $courses[0]['semester'] ?? 'Unknown';

        // 5. Fake rank (boleh tukar nanti ikut logic betul)
        $rank = 'Top 15%';

        // 6. Summary Cards (sample values)
        $summaryCards = [
            ['title' => 'Average Mark', 'value' => '78%', 'icon' => '📊'],
            ['title' => 'Courses Enrolled', 'value' => count($courses), 'icon' => '📘'],
            ['title' => 'Completed Assessments', 'value' => '7 / 12', 'icon' => '✅'],
            ['title' => 'Upcoming', 'value' => 'Final Exam – 14 Jul', 'icon' => '⏰'],
        ];

        // 7. Final response
        $data = [
            'student' => [
                'name' => $user['name'],
                'matric_number' => $user['matric_number'],
                'semester' => $semester,
                'rank' => $rank
            ],
            'summaryCards' => $summaryCards,
            'courses' => $courses
        ];

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getCourseInfo(Request $request, Response $response, array $args): Response
{
    $courseId = $args['id'];
    $stmt = $this->db->prepare("SELECT course_name AS name, course_code AS code FROM courses WHERE id = :id");

    $stmt->execute([':id' => $courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($course));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}


    public function viewCourseMarks(Request $request, Response $response, array $args): Response
{
    $studentId = $args['studentId'] ?? null;
    $courseId = $args['courseId'] ?? null;

    $stmt = $this->db->prepare("
        SELECT 
            a.id AS assessment_id,
            a.title AS component,
            a.max_mark,
            a.course_id,
            a.weight_percentage,
            c.course_name,
            c.course_code,
            sa.obtained_mark,
            rr.status AS remark_status
        FROM assessments a
        JOIN courses c ON a.course_id = c.id
        JOIN student_assessments sa 
            ON sa.assessment_id = a.id AND sa.student_id = :student_id
        LEFT JOIN remark_requests rr 
            ON rr.assessment_id = a.id AND rr.student_id = :student_id
        WHERE a.course_id = :course_id
    ");

    $stmt->execute([
        ':student_id' => $studentId,
        ':course_id' => $courseId
    ]);

    $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalObtained = 0;
    $totalMax = 0;

    foreach ($components as &$comp) {
        $obtained = isset($comp['obtained_mark']) ? (float) $comp['obtained_mark'] : null;
        $max = (float) $comp['max_mark'];
        $weight = (float) $comp['weight_percentage'];

        // Calculate contribution
        if ($obtained !== null && $max > 0) {
            $comp['contribution'] = round(($obtained / $max) * $weight, 2);
        } else {
            $comp['contribution'] = 0;
        }

        $totalMax += $max;
        $totalObtained += $obtained ?? 0;

        // Add flag to disable button in frontend
        $comp['remark_requested'] = $comp['remark_status'] !== null;
    }

    $summary = [
        'total_obtained' => $totalObtained,
        'total_max' => $totalMax,
        'percentage' => $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0
    ];

    $response->getBody()->write(json_encode([
        'components' => $components,
        'summary' => $summary
    ]));

    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}


    public function submitRemarkRequest(Request $request, Response $response, array $args): Response
    {
        $parsedBody = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $studentId = $parsedBody['student_id'] ?? null;
        $assessmentId = $parsedBody['assessment_id'] ?? null;
        $justification = $parsedBody['justification'] ?? null;

        if (!$studentId || !$assessmentId || !$justification) {
            $response->getBody()->write(json_encode(['error' => 'Missing required fields.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);

        }

        // Handle optional file upload
        $filePath = null;
        if (isset($uploadedFiles['file'])) {
            $file = $uploadedFiles['file'];
            if ($file->getError() === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/remarks';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = uniqid() . '_' . $file->getClientFilename();
                $filePath = $uploadDir . '/' . $filename;
                $file->moveTo($filePath);
            }
        }

        $stmt = $this->db->prepare("
        INSERT INTO remark_requests (student_id, assessment_id, justification, status, created_at)
        VALUES (:student_id, :assessment_id, :justification, 'pending', NOW())
    ");

        $stmt->execute([
            ':student_id' => $studentId,
            ':assessment_id' => $assessmentId,
            ':justification' => $justification
        ]);

        $response->getBody()->write(json_encode(['message' => 'Remark request submitted successfully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

    }

     // ✅ Fetch list of assessments for a course (for dropdown)
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

    // ✅ Fetch comparison data for a selected assessment
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

        // Anonymize labels
        foreach ($results as &$row) {
            if ($studentId && $row['student_id'] == $studentId) {
                $row['student_label'] = 'You';
            } else {
                $row['student_label'] = 'Student ' . substr(md5($row['student_id']), 0, 5);
            }
            unset($row['student_name'], $row['student_id']);
        }

        $response->getBody()->write(json_encode($results));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    
}


}
