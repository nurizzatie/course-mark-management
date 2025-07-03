<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class LecturerController
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Get all students in a course
    public function getCourseStudents(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];

        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.matric_number, u.email 
            FROM student_courses sc
            JOIN users u ON sc.student_id = u.id
            WHERE sc.course_id = ?
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode(['students' => $students]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Add student to course
    public function addStudentToCourse(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];
        $data = json_decode($request->getBody()->getContents(), true);
        $studentId = $data['student_id'] ?? null;

        if (!$studentId) {
            $response->getBody()->write(json_encode(['error' => 'Student ID is required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');      
        }

        // Check if student is already enrolled
        $check = $this->db->prepare("SELECT * FROM student_courses WHERE course_id = ? AND student_id = ?");
        $check->execute([$courseId, $studentId]);
        if ($check->fetch()) {
            $response->getBody()->write(json_encode(['error' => 'Student already enrolled']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $this->db->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
        $stmt->execute([$studentId, $courseId]);

        $response->getBody()->write(json_encode(['message' => 'Student added successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Remove student from course
    public function removeStudentFromCourse(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];
        $studentId = $args['student_id'];

        $stmt = $this->db->prepare("DELETE FROM student_courses WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);

        $response->getBody()->write(json_encode(['message' => 'Student removed successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Get courses based on logged-in lecturer
    public function getMyCourses(Request $request, Response $response): Response
    {
        $userHeader = $request->getHeaderLine('X-User');
        if (!$userHeader) {
            $response->getBody()->write(json_encode(['error' => 'Missing X-User header']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $user = json_decode($userHeader);
        $lecturerId = $user->id ?? null;

        if (!$lecturerId) {
            $response->getBody()->write(json_encode(['error' => 'Invalid lecturer ID']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $this->db->prepare("
            SELECT c.id, c.course_code, c.course_name
            FROM lecturer_courses lc
            JOIN courses c ON lc.course_id = c.id
            WHERE lc.lecturer_id = ?
        ");
        $stmt->execute([$lecturerId]);
        $courses = $stmt->fetchAll();

        $response->getBody()->write(json_encode(['courses' => $courses]));
        return $response->withHeader('Content-Type', 'application/json');
    }


}
