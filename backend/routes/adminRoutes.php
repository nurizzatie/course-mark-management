<?php

use Slim\App;
use App\Controllers\AdminController;
use App\Controllers\ResetController;

return function (App $app) {
    $db = $app->getContainer()->get('db');


    // ========== ADMIN CONTROLLER ROUTES ==========
    $adminController = new AdminController($db);

    $app->group('/api/admin', function ($group) use ($adminController) {
        // 🔐 Manage user accounts
        $group->get('/users', [$adminController, 'getUsers']);
        $group->put('/users/{id}/role', [$adminController, 'updateUserRole']);
        $group->post('/create-user', [$adminController, 'createUser']);
        $group->delete('/users/{id}', [$adminController, 'deleteUser']);

        // 🔄 Password reset by admin
        $group->put('/reset-password', [$adminController, 'resetPassword']);

        // 📚 System logs
        $group->get('/logs', [$adminController, 'getLogs']);

        // 🎯 Assign lecturers
        $group->get('/assign-data', [$adminController, 'getCoursesAndLecturers']);
        $group->post('/assign-lecturer', [$adminController, 'assignLecturer']);
        $group->post('/assign-lecturer-direct', [$adminController, 'assignLecturerDirect']);

        // ➕ Add new course
        $group->post('/courses', [$adminController, 'createCourse']);

        $group->get('/dashboard-stats', [$adminController, 'getDashboardStats']);



    });

};
