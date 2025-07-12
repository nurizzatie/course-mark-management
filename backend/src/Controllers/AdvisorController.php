<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdvisorController
{
    protected $db;

    public function __construct($container)
    {
        $this->db = $container->get('db');
    }

    // Get all students under supervision
    public function getStudents(Request $request, Response $response, $args): Response
    {
        // Parse X-User header
        $userHeader = $request->getHeaderLine('X-User');
        if (!$userHeader) {
            $response->getBody()->write(json_encode(['error' => 'Missing X-User header']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $user = json_decode($userHeader);
        $advisorId = $user->id ?? null;

        if (!$advisorId) {
            $response->getBody()->write(json_encode(['error' => 'Invalid advisor ID']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Query assigned students
        $sql = "
            SELECT 
                u.id,
                u.name,
                u.email,
                u.matric_number
            FROM advisor_students a
            JOIN users u ON a.student_id = u.id
            WHERE a.advisor_id = :advisor_id
            ORDER BY u.name
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['advisor_id' => $advisorId]);
            $students = $stmt->fetchAll();

            $response->getBody()->write(json_encode($students));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Error fetching advisor students: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Failed to fetch students']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    // Get student marks
        public function getStudentMarks(Request $request, Response $response, $args): Response
    {
        try {
            $sql = "
            SELECT 
            u.id AS student_id,
            u.name AS student_name,
            u.matric_number,
            a.title AS assessment_name,
            sa.obtained_mark,
            a.max_mark,
            sa.updated_at
        FROM student_assessments sa
        JOIN users u ON sa.student_id = u.id
        JOIN assessments a ON sa.assessment_id = a.id
        WHERE u.role = 'student'
        ORDER BY u.name, a.title
            ";

            $stmt = $this->db->query($sql);
            $marks = $stmt->fetchAll();

            $response->getBody()->write(json_encode($marks));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Error fetching marks: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Failed to fetch marks']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    //get performance analytics
    public function getAnalytics(Request $request, Response $response, $args): Response
{
    try {
        $userHeader = $request->getHeaderLine('X-User');
        $advisor = json_decode($userHeader);
        $advisorId = $advisor->id ?? null;

        if (!$advisorId) {
            $response->getBody()->write(json_encode(['error' => 'Missing advisor ID']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $this->db->prepare("
    SELECT 
        u.id            AS student_id,
        u.name          AS student_name,
        u.matric_number,
        c.course_name,
        ad.total_mark,
        ad.final_exam_mark,
        ad.overall_percentage,
        ad.rank,
        ad.percentile,
        ad.risk_level
    FROM advisor_students s
    JOIN users u           ON u.id = s.student_id
    LEFT JOIN analytics_data ad ON ad.student_id = u.id
    LEFT JOIN courses c         ON c.id = ad.course_id
    WHERE s.advisor_id = ?
");
$stmt->execute([$advisorId]);

        $data = $stmt->fetchAll();

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (\PDOException $e) {
        error_log('Error fetching analytics data: ' . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to fetch analytics']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}


    public function getHighRiskStudents(Request $request, Response $response, $args): Response
    {
        try {
            $sql = "
                SELECT 
                    a.student_id,
                    u.name AS student_name,
                    u.matric_number,
                    u.email,
                    a.course_id,
                    c.course_code,
                    a.overall_percentage,
                    a.percentile,
                    a.risk_level
                FROM analytics_data a
                JOIN users u ON a.student_id = u.id
                JOIN courses c ON a.course_id = c.id
                WHERE a.overall_percentage < 50 OR a.percentile < 20
                ORDER BY a.percentile ASC, a.overall_percentage ASC
            ";

            $stmt = $this->db->query($sql);
            $highRisk = $stmt->fetchAll();

            $response->getBody()->write(json_encode($highRisk));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Error fetching high risk students: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Failed to fetch high-risk students']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    // GET advisor notes
    public function getAdvisorNotes(Request $request, Response $response, $args): Response
    {
        $sql = "
            SELECT 
                an.id,
                an.meeting_date,
                an.note,
                u.name AS student_name,
                u.matric_number,
                u.email
            FROM advisor_notes an
            JOIN users u ON an.student_id = u.id
            WHERE an.advisor_id = :advisor_id
            ORDER BY an.meeting_date DESC
        ";

        $advisorId = $request->getQueryParams()['advisor_id'] ?? null;

        if (!$advisorId) {
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
                            ->write(json_encode(['error' => 'Missing advisor_id']));
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['advisor_id' => $advisorId]);
            $notes = $stmt->fetchAll();

            $response->getBody()->write(json_encode($notes));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Fetch advisor notes error: ' . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    // POST add advisor note
    public function addAdvisorNote(Request $request, Response $response, $args): Response
    {
        $data = $request->getParsedBody();

        $advisorId = $data['advisor_id'] ?? null;
        $studentId = $data['student_id'] ?? null;
        $meetingDate = $data['meeting_date'] ?? null;
        $note = $data['note'] ?? null;

        if (!$advisorId || !$studentId || !$meetingDate || !$note) {
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
                            ->write(json_encode(['error' => 'Missing required fields']));
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO advisor_notes (advisor_id, student_id, meeting_date, note)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$advisorId, $studentId, $meetingDate, $note]);

            $response->getBody()->write(json_encode(['message' => 'Note added successfully']));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Insert advisor note error: ' . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    //Get Advisor Profile
    public function getProfile(Request $request, Response $response, $args): Response
    {
        $id = $args['id'];

        try {
            // Fetch advisor profile
            $stmt = $this->db->prepare("SELECT id, name, email, matric_number FROM users WHERE id = :id AND role = 'advisor'");
            $stmt->execute(['id' => $id]);
            $advisor = $stmt->fetch();

            if (!$advisor) {
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json')
                                ->write(json_encode(['error' => 'Advisor not found']));
            }

            // Fetch students assigned to the advisor, filtered to only students
            $sqlStudents = "
                SELECT u.id, u.name, u.email, u.matric_number
                FROM advisor_students a
                JOIN users u ON a.student_id = u.id
                WHERE a.advisor_id = :advisor_id
                AND u.role = 'student'
            ";

            $stmt2 = $this->db->prepare($sqlStudents);
            $stmt2->execute(['advisor_id' => $id]);
            $students = $stmt2->fetchAll();

            // Return structured response
            $data = [
                'profile' => $advisor,
                'students' => $students
            ];

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\PDOException $e) {
            error_log('Error fetching advisor profile: ' . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json')
                            ->write(json_encode(['error' => 'Failed to fetch advisor profile']));
        }
    }
public function getDashboardStats(Request $request, Response $response, array $args): Response {
    try {
        $advisorId = $args['id'];
        $db = $this->db;

        // Count assigned students
        $stmt1 = $db->prepare("SELECT COUNT(*) as total FROM advisor_students WHERE advisor_id = ?");
        $stmt1->execute([$advisorId]);
        $assignedStudents = $stmt1->fetch()['total'];

        // Count consultation notes
        $stmt2 = $db->prepare("SELECT COUNT(*) as total FROM advisor_notes WHERE advisor_id = ?");
        $stmt2->execute([$advisorId]);
        $consultGiven = $stmt2->fetch()['total'];

        // Count reviewed marks from student_assessments
        $stmt3 = $db->prepare("
            SELECT COUNT(*) as total
            FROM student_assessments sa
            JOIN advisor_students a ON sa.student_id = a.student_id
            WHERE a.advisor_id = ?
        ");
        $stmt3->execute([$advisorId]);
        $totalReviews = $stmt3->fetch()['total'];

        // Count analytics entries for assigned students
        $stmt4 = $db->prepare("
            SELECT COUNT(*) as total
            FROM analytics_data ad
            JOIN advisor_students a ON ad.student_id = a.student_id
            WHERE a.advisor_id = ?
        ");
        $stmt4->execute([$advisorId]);
        $analyticsReports = $stmt4->fetch()['total'];

        // Return stats
        $data = [
            "assigned_students" => $assignedStudents,
            "feedback_given" => $consultGiven,
            "pending_reviews" => $totalReviews,
            "analytics_reports" => $analyticsReports
        ];

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (\Exception $e) {
        error_log('Dashboard stats error: ' . $e->getMessage());
        $response->getBody()->write(json_encode([
            "error" => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}


    public function getAdviseeProgress(Request $request, Response $response, $args): Response
    {
        $studentId = $args['id'];

        $sql = "
            SELECT 
                u.name AS student_name,
                c.course_name AS course_name,
                a.type AS assessment_type,
                a.title AS assessment_title,
                a.max_mark,
                a.weight_percentage,
                sa.obtained_mark
            FROM student_assessments sa
            JOIN assessments a ON sa.assessment_id = a.id
            JOIN courses c ON a.course_id = c.id
            JOIN users u ON sa.student_id = u.id
            WHERE sa.student_id = :student_id
            ORDER BY c.course_name, a.type
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['student_id' => $studentId]);
            $results = $stmt->fetchAll();

            $response->getBody()->write(json_encode($results));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Error fetching advisee progress: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Failed to fetch advisee progress']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

// Get all courses for selection review mark page
    public function getAllCourses(Request $request, Response $response): Response
    {
        $sql = "SELECT id, course_name FROM courses ORDER BY course_name";

        try {
            $stmt = $this->db->query($sql);
            $courses = $stmt->fetchAll();

            $response->getBody()->write(json_encode($courses));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Error fetching all courses: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Failed to fetch courses']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    // Get course mark breakdown for all student in each courses
    public function getCourseMarkBreakdown(Request $request, Response $response, $args): Response
    {
        $courseId = $args['id'];

        $sql = "
            SELECT 
                u.id AS student_id,
                u.name AS student_name,
                u.matric_number,
                a.title AS assessment_title,
                a.type AS assessment_type,
                a.max_mark,
                a.weight_percentage,
                sa.obtained_mark,
                sc.remarks
            FROM student_assessments sa
            JOIN assessments a ON sa.assessment_id = a.id
            JOIN users u ON sa.student_id = u.id
            JOIN student_courses sc ON sc.student_id = u.id AND sc.course_id = a.course_id
            WHERE a.course_id = :course_id
            ORDER BY u.name, a.type
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['course_id' => $courseId]);
            $data = $stmt->fetchAll();

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Error fetching mark breakdown: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Failed to fetch breakdown']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    // Compare with Coursemates
    public function getCourseComparison(Request $request, Response $response, $args): Response
    {
        $courseId = $args['id'];

        $sql = "
            SELECT 
                u.id AS student_id,
                u.name AS student_name,
                u.matric_number,
                a.max_mark,
                a.weight_percentage,
                sa.obtained_mark
            FROM student_assessments sa
            JOIN assessments a ON sa.assessment_id = a.id
            JOIN users u ON sa.student_id = u.id
            WHERE a.course_id = :course_id
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['course_id' => $courseId]);
            $data = $stmt->fetchAll();

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Error fetching course comparison: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Failed to fetch comparison']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    // Get class ranking
    public function getCourseRanking(Request $request, Response $response, $args): Response
    {
        $courseId = $args['id'];

        $sql = "
            SELECT 
                u.id AS student_id,
                u.name AS student_name,
                u.matric_number,
                a.max_mark,
                a.weight_percentage,
                sa.obtained_mark,
                sc.remarks
            FROM student_assessments sa
            JOIN assessments a ON sa.assessment_id = a.id
            JOIN users u ON sa.student_id = u.id
            JOIN student_courses sc ON sc.student_id = u.id AND sc.course_id = a.course_id
            WHERE a.course_id = :course_id
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['course_id' => $courseId]);
            $data = $stmt->fetchAll();

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Error fetching course ranking: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Failed to fetch ranking']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    // Get average by component
    public function getClassAverageByComponent(Request $request, Response $response, $args): Response
    {
        $courseId = $args['id'];

        $sql = "
            SELECT 
                a.type AS component_type,
                sa.obtained_mark,
                a.max_mark
            FROM student_assessments sa
            JOIN assessments a ON sa.assessment_id = a.id
            WHERE a.course_id = :course_id
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['course_id' => $courseId]);
            $rows = $stmt->fetchAll();

            $response->getBody()->write(json_encode($rows));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log('Error fetching class average: ' . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Failed to fetch class average']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    // Update profile
    public function updateProfile(Request $request, Response $response, $args): Response
    {
        $id = $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);

        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, matric_number = ? WHERE id = ?");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['matric_number'],
            $id
        ]);

        $response->getBody()->write(json_encode(['message' => 'Profile updated']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);

    }

  private function buildAnalyticsForStudent(int $studentId): void
{
    error_log("📊 Building analytics for student ID: $studentId");

    $sql = "
        SELECT
    a.course_id,
    SUM(sa.obtained_mark) AS total_mark,
    MAX(CASE WHEN LOWER(a.type) IN ('final', 'final exam') THEN sa.obtained_mark ELSE 0 END)
    AS final_exam_mark,
    ROUND(SUM(
    CASE 
    WHEN a.max_mark > 0 AND a.weight_percentage > 0 
    THEN (sa.obtained_mark / a.max_mark) * a.weight_percentage 
    ELSE 0 
    END
    ),2) AS overall_percentage
    FROM assessments a
    JOIN student_assessments sa ON sa.assessment_id = a.id
    WHERE sa.student_id = :student_id
    GROUP BY a.course_id;

    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['student_id' => $studentId]);
    $rows = $stmt->fetchAll();
    error_log("Analytics raw rows: " . json_encode($rows));


    if (!$rows || count($rows) === 0) {
        error_log("⚠️ No analytics rows generated for student $studentId");
        return;
    }

    foreach ($rows as $r) {
        $courseId = $r['course_id'];
        $totalMark = $r['total_mark'] ?? 0;
        $finalExamMark = $r['final_exam_mark'] ?? 0;
        $overall = $r['overall_percentage'] ?? 0;

        // Determine risk level
        $risk = 'Low';
        if ($overall < 50) {
            $risk = 'High';
        } elseif ($overall < 65) {
            $risk = 'Medium';
        }

        $insert = $this->db->prepare("
            INSERT INTO analytics_data
                (student_id, course_id, total_mark, final_exam_mark, overall_percentage, rank, percentile, risk_level)
            VALUES
                (:student_id, :course_id, :total_mark, :final_exam_mark, :overall, 0, 0, :risk_level)
            ON DUPLICATE KEY UPDATE
                total_mark = VALUES(total_mark),
                final_exam_mark = VALUES(final_exam_mark),
                overall_percentage = VALUES(overall_percentage),
                risk_level = VALUES(risk_level)
        ");

        $insert->execute([
            'student_id'      => $studentId,
            'course_id'       => $courseId,
            'total_mark'      => $totalMark,
            'final_exam_mark' => $finalExamMark,
            'overall'         => $overall,
            'risk_level'      => $risk
        ]);

        error_log("✅ Inserted/updated analytics for student $studentId in course $courseId");

        // 🔁 Recalculate rank/percentile if method exists
        if (method_exists($this, 'reRankCourse')) {
            $this->reRankCourse($courseId);
        }
    }
}


private function reRankCourse(int $courseId): void
{
    // Fetch all analytics rows for this course ordered by overall desc
    $rows = $this->db
       ->prepare("SELECT id, overall_percentage 
                  FROM analytics_data
                  WHERE course_id = ?
                  ORDER BY overall_percentage DESC");
    $rows->execute([$courseId]);
    $students = $rows->fetchAll();

    $total = count($students);
    foreach ($students as $idx => $row) {
        $rank       = $idx + 1;
        $percentile = round((1 - ($rank - 1) / $total) * 100, 2);
        $risk       = $row['overall_percentage'] < 50 ? 'High'
                   : ($row['overall_percentage'] < 65 ? 'Medium' : 'Low');

        $upd = $this->db->prepare("
            UPDATE analytics_data
            SET rank = ?, percentile = ?, risk_level = ?
            WHERE id = ?
        ");
        $upd->execute([$rank, $percentile, $risk, $row['id']]);
    }
}

public function assignStudent(Request $request, Response $response): Response
{
    $body       = json_decode($request->getBody()->getContents(), true);
    $matric     = trim($body['matric_number'] ?? '');
    $advisorHdr = json_decode($request->getHeaderLine('X-User') ?: '{}');
    $advisorId  = $advisorHdr->id ?? null;

    if (!$matric || !$advisorId) {
        return $response->withStatus(400)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode(['error' => 'Missing advisor ID or matric']));
    }

    // 1. locate student
    $stu = $this->db->prepare(
        "SELECT id FROM users WHERE matric_number = ? AND role='student'"
    );
    $stu->execute([$matric]);
    $studentId = $stu->fetchColumn();
    if (!$studentId) {
        return $response->withStatus(404)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode(['error' => 'Student not found']));
    }

    // 2. insert into advisor_students (ignore duplicates)
    $this->db->prepare("
        INSERT IGNORE INTO advisor_students (advisor_id, student_id) VALUES (?, ?)
    ")->execute([$advisorId, $studentId]);

    // 3. build analytics row(s) for the new student
    $this->buildAnalyticsForStudent((int)$studentId);

    return $response->withJson(['message' => 'Student assigned & analytics generated'], 201);
}

public function removeStudent(Request $request, Response $response, array $args): Response
{
    $studentId = (int) $args['id'];
    $advisorHdr = json_decode($request->getHeaderLine('X-User') ?: '{}');
    $advisorId  = $advisorHdr->id ?? null;

    if (!$advisorId || !$studentId) {
        $data = ['error' => 'Missing advisor ID or student ID'];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Delete advisor-student mapping
    $del = $this->db->prepare(
        "DELETE FROM advisor_students WHERE advisor_id = ? AND student_id = ?"
    );
    $del->execute([$advisorId, $studentId]);

    // Optionally delete analytics data
    $this->db->prepare("DELETE FROM analytics_data WHERE student_id = ?")
             ->execute([$studentId]);

    $data = ['message' => 'Student removed'];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}


}
