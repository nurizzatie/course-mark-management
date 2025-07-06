<?php

use Slim\App;
use App\Controllers\StudentController;
use App\Controllers\RemarkController;

return function (App $app) {

    // Standalone route for course info
    $app->get('/api/course/{id}', [StudentController::class, 'getCourseInfo']);
    

    // Group all student-related routes
    $app->group('/api/student', function ($group) {
        $group->get('/{id}/dashboard', [StudentController::class, 'dashboard']);
        $group->get('/{id}/profile', [StudentController::class, 'getStudentProfile']);
        $group->put('/{id}/profile', [StudentController::class, 'updateStudentProfile']);
        $group->get('/{studentId}/course/{courseId}/marks', [StudentController::class, 'viewCourseMarks']);
        $group->get('/course/{id}/assessment-list', [StudentController::class, 'getAssessmentsByCourse']);
        $group->get('/course/{courseId}/compare/{assessmentId}', [StudentController::class, 'compareAssessmentMarks']);
        $group->get('/course/{id}/rank/{studentId}', [StudentController::class, 'getStudentRank']);
        $group->get('/course/{id}/rank-table', [StudentController::class, 'getRankTable']);
        $group->get('/{studentId}/performance-chart', [StudentController::class, 'getPerformanceChart']);
    });


    $app->get('/api/students/{id}/notifications', StudentController::class . ':getNotifications');
    $app->post('/api/student/notifications/{id}/seen', StudentController::class . ':markNotificationSeen');

    $app->group('/api/remark', function ($group) {
        $group->post('/request', [RemarkController::class, 'submitRemarkRequest']);
        $group->post('/appeal', [RemarkController::class, 'submitAppeal']);
        $group->get('/appeal-count', [RemarkController::class, 'getAppealCount']);
    });


};
