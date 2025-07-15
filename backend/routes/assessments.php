<?php
use Slim\App;
use App\Controllers\AssessmentController;

return function (App $app) {
    $app->group('/api', function ($group) {
        $group->get('/course/{course_id}/assessments/with-student/{student_id}', AssessmentController::class . ':getWithStudent');
    });
};
