<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class AdminController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getUsers(Request $request, Response $response): Response {
        $stmt = $this->db->query("SELECT id, name, email, role FROM users");
        $users = $stmt->fetchAll();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function assignLecturer(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $stmt = $this->db->prepare("INSERT INTO course_assignments (course_id, lecturer_id) VALUES (?, ?)");
        $stmt->execute([$data['course_id'], $data['lecturer_id']]);
        $response->getBody()->write(json_encode(['message' => 'Lecturer assigned']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getLogs(Request $request, Response $response): Response {
        $stmt = $this->db->query("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 100");
        $logs = $stmt->fetchAll();
        $response->getBody()->write(json_encode($logs));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function resetPassword(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $hashed = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $data['user_id']]);
        $response->getBody()->write(json_encode(['message' => 'Password reset']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getCoursesAndLecturers(Request $request, Response $response): Response {
        $lecturers = $this->db->query("SELECT id, name FROM users WHERE role = 'Lecturer'")->fetchAll();
        $courses = $this->db->query("SELECT id, course_code, course_name FROM courses")->fetchAll();

        $response->getBody()->write(json_encode([
            'lecturers' => $lecturers,
            'courses' => $courses
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function assignLecturerToCourse(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $courseId = $data['course_id'] ?? null;
        $lecturerId = $data['lecturer_id'] ?? null;

        if (!$courseId || !$lecturerId) {
            $response->getBody()->write(json_encode(['error' => 'Missing course_id or lecturer_id']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $this->db->prepare("REPLACE INTO course_assignments (course_id, lecturer_id) VALUES (:course_id, :lecturer_id)");
        $stmt->execute(['course_id' => $courseId, 'lecturer_id' => $lecturerId]);

        $response->getBody()->write(json_encode(['message' => 'Lecturer assigned to course']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function updateUserRole(Request $request, Response $response, array $args): Response {
        $userId = $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);
        $newRole = $data['role'] ?? '';

        $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute([
            'role' => $newRole,
            'id' => $userId
        ]);

        $response->getBody()->write(json_encode(['message' => 'Role updated']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
