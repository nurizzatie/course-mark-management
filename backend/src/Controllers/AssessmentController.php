<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Assessment;
use Psr\Container\ContainerInterface;

class AssessmentController {
    protected $db;

    public function __construct(ContainerInterface $container) {
        $this->db = $container->get('db');
    }

    public function getWithStudent(Request $request, Response $response, $args) {
        $courseId = $args['course_id'];
        $studentId = $args['student_id'];

        $stmt = $this->db->prepare("
            SELECT a.*, s.obtained_mark
            FROM assessments a
            LEFT JOIN student_assessments s
                ON a.id = s.assessment_id AND s.student_id = :studentId
            WHERE a.course_id = :courseId
        ");
        $stmt->execute([
            ':courseId' => $courseId,
            ':studentId' => $studentId
        ]);
        $data = $stmt->fetchAll();

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}



