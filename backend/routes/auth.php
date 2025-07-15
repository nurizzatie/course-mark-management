<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function ($app) {
    $app->post('/api/login', function (Request $request, Response $response) {
        $db = $this->get('db');

        // Get JSON input body (for Axios/fetch)
        $input = json_decode($request->getBody()->getContents(), true);

        $matric_number = $input['matric_number'] ?? '';
        $password = $input['password'] ?? '';

        // Fetch user from DB
        $stmt = $db->prepare("SELECT * FROM users WHERE matric_number = ?");
        $stmt->execute([$matric_number]);
        $user = $stmt->fetch();

        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Matric number not found']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        if (!password_verify($password, $user['password'])) {
            $response->getBody()->write(json_encode(['error' => 'Invalid password']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        unset($user['password']);
        $response->getBody()->write(json_encode(['user' => $user]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
