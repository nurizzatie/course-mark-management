<?php

use App\Controllers\AdvisorController;

return function ($app) {
    $container = $app->getContainer();

    // GET /api/advisor/students
    $app->get('/api/advisor/students', function ($request, $response, $args) use ($container) {
        $controller = new AdvisorController($container);
        return $controller->getStudents($request, $response, $args);
    });

    // POST /api/advisor/feedback
    $app->post('/api/advisor/feedback', function ($request, $response, $args) use ($container) {
        $controller = new \App\Controllers\AdvisorController($container);
        return $controller->submitFeedback($request, $response, $args);
    });

    // NEW: GET student assessment marks
    $app->get('/api/advisor/marks', function ($request, $response, $args) use ($container) {
        $controller = new AdvisorController($container);
        return $controller->getStudentMarks($request, $response, $args);
    });

    $app->get('/api/advisor/analytics', function ($request, $response, $args) use ($container) {
    $controller = new AdvisorController($container);
    return $controller->getAnalytics($request, $response, $args);
});
};





