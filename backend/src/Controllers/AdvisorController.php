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
        $stmt = $this->db->query("SELECT id, name, email, matric_number FROM users WHERE role = 'student'");
        $students = $stmt->fetchAll();

        $response->getBody()->write(json_encode($students));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Submit advisor feedback
    public function submitFeedback(Request $request, Response $response, $args): Response
{
    $data = $request->getParsedBody();
    $advisorId = $data['advisor_id'] ?? null;
    $studentId = $data['student_id'] ?? null;
    $meetingDate = $data['meeting_date'] ?? null;
    $note = $data['note'] ?? null;

    if (!$advisorId || !$studentId || !$meetingDate || !$note) {
        $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        $stmt = $this->db->prepare("INSERT INTO advisor_note (advisor_id, student_id, meeting_date, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([$advisorId, $studentId, $meetingDate, $note]);

        $response->getBody()->write(json_encode(['message' => 'Feedback submitted successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\PDOException $e) {
        error_log('Feedback Insert Error: ' . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Database insert failed.']));
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

public function getAnalytics(Request $request, Response $response, $args): Response
{
    try {
        $stmt = $this->db->query("
            SELECT 
                a.student_id,
                u.name AS student_name,
                a.course_id,
                c.course_code,
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


}
