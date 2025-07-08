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
    //get student analytics
    public function getAnalytics(Request $request, Response $response, $args): Response
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    a.student_id,
                    u.name AS student_name,
                    u.matric_number,
                    a.course_id,
                    c.course_name,
                    a.total_mark,
                    a.final_exam_mark,
                    a.overall_percentage,
                    a.rank,
                    a.percentile,
                    a.risk_level
                FROM analytics_data a
                JOIN users u ON a.student_id = u.id
                JOIN courses c ON a.course_id = c.id
            ");
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

        // Count consultations given
        $stmt2 = $db->prepare("SELECT COUNT(*) as total FROM advisor_consult_notes WHERE advisor_id = ?");
        $stmt2->execute([$advisorId]);
        $consultGiven = $stmt2->fetch()['total'];

        // Count mark reviews (based on feedback)
        $stmt3 = $db->prepare("SELECT COUNT(*) as total FROM marks_feedback WHERE advisor_id = ?");
        $stmt3->execute([$advisorId]);
        $totalReviews = $stmt3->fetch()['total'];

        // Count analytics reports generated
        $stmt4 = $db->prepare("SELECT COUNT(*) as total FROM analytics_reports WHERE advisor_id = ?");
        $stmt4->execute([$advisorId]);
        $analyticsReports = $stmt4->fetch()['total'];

        // Return data
        $data = [
            "assigned_students" => $assignedStudents,
            "feedback_given" => $consultGiven,
            "pending_reviews" => $totalReviews,
            "analytics_reports" => $analyticsReports
        ];

        return $response->withJson($data);

    } catch (\Exception $e) {
        return $response->withStatus(500)->withJson([
            "error" => $e->getMessage()
        ]);
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


}
