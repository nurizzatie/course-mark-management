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
    $matric = $data['matric_number'] ?? null;

    if (!$email || !$matric) {
        $response->getBody()->write(json_encode(['error' => 'Email and matric number are required']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Check if user exists (by email + matric number)
    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND matric_number = ?");
    $stmt->execute([$email, $matric]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'No user with this email and matric number']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    // Insert reset request
    $stmt = $this->db->prepare("INSERT INTO reset_requests (user_id, status, created_at) VALUES (?, 'pending', NOW())");
    $stmt->execute([$user['id']]);

    // Optional: include response data
    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'Reset request submitted',
        'data' => [
            'id' => $user['id'],
            'email' => $email,
            'matric_number' => $matric
        ]
    ]));

    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}



    // GET /api/reset-requests
    public function getRequests(Request $request, Response $response): Response {
    $stmt = $this->db->query("
        SELECT 
            rr.id, 
            u.email, 
            u.matric_number, 
            rr.status, 
            rr.created_at 
        FROM reset_requests rr
        JOIN users u ON rr.user_id = u.id
        WHERE rr.status = 'pending'
        ORDER BY rr.created_at DESC
    ");

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($requests));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}

}
