<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class ResetController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // 🔒 POST /api/reset-request
    public function requestReset(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? null;

        if (!$email) {
            $response->getBody()->write(json_encode(['error' => 'Email is required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'No user with this email']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Insert reset request
        $stmt = $this->db->prepare("INSERT INTO reset_requests (email, status, created_at) VALUES (?, 'pending', NOW())");
$stmt->execute([$email]);


        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Reset request submitted']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // 🔍 GET /api/reset-requests
    public function getRequests(Request $request, Response $response): Response {
        $stmt = $this->db->query("SELECT id, email, status, created_at FROM reset_requests WHERE status = 'pending' ORDER BY created_at DESC");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($requests));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
