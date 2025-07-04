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
}
