<?php

use App\Controllers\AdvisorController;

return function ($app) {
    $container = $app->getContainer();

    // GET /api/advisor/students
    $app->get('/api/advisor/students', function ($request, $response, $args) use ($container) {
        $controller = new AdvisorController($container);
        return $controller->getStudents($request, $response, $args);
    });

    // GET student assessment marks
    $app->get('/api/advisor/marks', function ($request, $response, $args) use ($container) {
        $controller = new AdvisorController($container);
        return $controller->getStudentMarks($request, $response, $args);
    });

    // GET performance analytics
    $app->get('/api/advisor/analytics', function ($request, $response, $args) use ($container) {
        $controller = new AdvisorController($container);
        return $controller->getAnalytics($request, $response, $args);
    });

    // GET high risk students
    $app->get('/api/advisor/high-risk-students', function ($request, $response, $args) use ($container) {
        $controller = new AdvisorController($container);
        return $controller->getHighRiskStudents($request, $response, $args);
    });

    // GET advisor notes
    $app->get('/api/advisor/notes', function ($request, $response, $args) use ($container) {
        $controller = new AdvisorController($container);
        return $controller->getAdvisorNotes($request, $response, $args);
    });

    // POST new advisor note
    $app->post('/api/advisor/notes', function ($request, $response, $args) use ($container) {
        $controller = new AdvisorController($container);
        return $controller->addAdvisorNote($request, $response, $args);
    });

    // GET advisor profile
    $app->get('/api/advisor/profile/{id}', function ($request, $response, $args) {
        $controller = new \App\Controllers\AdvisorController($this);
        return $controller->getProfile($request, $response, $args);
    });

    // Get advisee progress (full mark breakdown for each course and overall course performance)
    $app->get('/api/advisor/advisee/{id}/progress', function ($request, $response, $args) use ($container) {
        $controller = new \App\Controllers\AdvisorController($container);
        return $controller->getAdviseeProgress($request, $response, $args);
    });

// GET advisor dashboard stats
$app->get('/api/advisor/{id}/dashboard-stats', function ($request, $response, $args) use ($app) {
    $controller = new \App\Controllers\AdvisorController($app->getContainer());
    return $controller->getDashboardStats($request, $response, $args);
});

};






