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

    // Get all students with role = 'student'
    public function getStudents(Request $request, Response $response, $args): Response
{
    $sql = "
        SELECT 
            u.id AS id,
            u.name AS name,
            u.email,
            u.matric_number,
            sc.remarks
        FROM users u
        LEFT JOIN student_courses sc ON u.id = sc.student_id
        WHERE u.role = 'student'
        ORDER BY u.name
    ";

    try {
        $stmt = $this->db->query($sql);
        $students = $stmt->fetchAll();

        $response->getBody()->write(json_encode($students));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\PDOException $e) {
        error_log('Error fetching students with remarks: ' . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to fetch student data']));
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
    $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id = :id AND role = 'advisor'");
    $stmt->execute(['id' => $id]);
    $advisor = $stmt->fetch();

    if ($advisor) {
        $response->getBody()->write(json_encode($advisor));
        return $response->withHeader('Content-Type', 'application/json');
    }

    return $response->withStatus(404)->withHeader('Content-Type', 'application/json')
                    ->write(json_encode(['error' => 'Advisor not found']));
}

//POST Advisor Profile
public function updateProfile(Request $request, Response $response, $args): Response
{
    $id = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id AND role = 'advisor'";
    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        'id' => $id,
        'name' => $data['name'],
        'email' => $data['email']
    ]);

    $response->getBody()->write(json_encode(['message' => 'Profile updated']));
    return $response->withHeader('Content-Type', 'application/json');
}


}
