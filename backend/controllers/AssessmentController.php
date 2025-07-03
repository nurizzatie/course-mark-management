<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Assessment;

class AssessmentController {
    protected $db;

    public function __construct($container) {
        $this->db = $container->get('db');
    }

    public function getByCourse(Request $request, Response $response, $args) {
        $courseId = $args['courseId'];
        $type = $request->getQueryParams()['type'] ?? null;

        $model = new Assessment($this->db);
        $assessments = $model->getByCourseId($courseId, $type);

        return $response->withJson([
            'assessments' => $assessments
        ]);
    }
}
