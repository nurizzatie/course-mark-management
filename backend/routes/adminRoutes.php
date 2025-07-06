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
        $group->post('/create-user', [$controller, 'createUser']);
        $group->delete('/users/{id}', [$controller, 'deleteUser']);

        // 🔄 Password reset
        $group->put('/reset-password', [$controller, 'resetPassword']);

        // 📚 System logs
        $group->get('/logs', [$controller, 'getLogs']);

        // 🎯 Assign lecturers to courses
        $group->get('/assign-data', [$controller, 'getCoursesAndLecturers']);

        // Option A: Assign using `courses` table `lecturer_id`
        $group->post('/assign-lecturer', [$controller, 'assignLecturer']);

        // Option B: Assign using `course_assignments` table (optional alternative)
        $group->post('/assign-lecturer-direct', [$controller, 'assignLecturerToCourse']);

        // ➕ Add new course
        $group->post('/courses', [$controller, 'createCourse']);
    });
};
