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

    public function getStudents(Request $request, Response $response, $args): Response
    {
        $stmt = $this->db->query("SELECT id, name, email, matric_number FROM users WHERE role = 'student'");
        $students = $stmt->fetchAll();

        $response->getBody()->write(json_encode($students));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
