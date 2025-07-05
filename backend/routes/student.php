<?php

use Slim\App;
use App\Controllers\StudentController;
use App\Controllers\RemarkController;

return function (App $app) {
    $app->get('/api/student/{id}/dashboard', [StudentController::class, 'dashboard']);
    $app->get('/api/student/{studentId}/course/{courseId}/marks', [StudentController::class, 'viewCourseMarks']);
    $app->post('/api/remark/request', [StudentController::class, 'submitRemarkRequest']);

};
