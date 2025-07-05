<?php

use App\Controllers\AdminController;
use Slim\App;

return function (App $app) {
    // Get the DB connection from the container
    $db = $app->getContainer()->get('db');
    $controller = new AdminController($db);

    // Group all admin-related routes under /api/admin
    $app->group('/api/admin', function ($group) use ($controller) {
        // 🔐 Manage user accounts
        $group->get('/users', [$controller, 'getUsers']);
        $group->put('/users/{id}/role', [$controller, 'updateUserRole']);

        // 🔄 Password reset
        $group->post('/reset-password', [$controller, 'resetPassword']);

        // 📚 System logs
        $group->get('/logs', [$controller, 'getLogs']);

        // 🎯 Assign lecturers to courses
        $group->get('/assign-data', [$controller, 'getCoursesAndLecturers']); // get list of lecturers + courses
        $group->post('/assign-lecturer', [$controller, 'assignLecturerToCourse']); // assign lecturer to a course
    });
};
