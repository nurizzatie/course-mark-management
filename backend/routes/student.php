<?php

use Slim\App;
use App\Controllers\StudentController;

return function (App $app) {

    // Standalone route for course info
    $app->get('/api/course/{id}', [StudentController::class, 'getCourseInfo']);

    // Group all student-related routes
    $app->group('/api/student', function ($group) {
        $group->get('/{id}/dashboard', [StudentController::class, 'dashboard']);
        $group->get('/{studentId}/course/{courseId}/marks', [StudentController::class, 'viewCourseMarks']);
        $group->get('/course/{id}/assessment-list', [StudentController::class, 'getAssessmentsByCourse']);
        $group->get('/course/{courseId}/compare/{assessmentId}', [StudentController::class, 'compareAssessmentMarks']);
        $group->get('/course/{id}/rank/{studentId}', [StudentController::class, 'getStudentRank']);
        $group->get('/course/{id}/rank-table', [StudentController::class, 'getRankTable']);
        $group->get('/{studentId}/performance-chart', [StudentController::class, 'getPerformanceChart']);

    });

    // Remark request route (can be grouped later under /api/remark if needed)
    $app->post('/api/remark/request', [StudentController::class, 'submitRemarkRequest']);
    $app->get('/api/students/{id}/notifications', StudentController::class . ':getNotifications');
    $app->post('/api/student/notifications/{id}/seen', StudentController::class . ':markNotificationSeen');
    


};
