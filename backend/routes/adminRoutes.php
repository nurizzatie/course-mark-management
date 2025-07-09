<?php

use Slim\App;
use App\Controllers\AdminController;
use App\Controllers\ResetController;

return function (App $app) {
    $db = $app->getContainer()->get('db');


    // ========== ADMIN CONTROLLER ROUTES ==========
    $adminController = new AdminController($db);

    $app->group('/api/admin', function ($group) use ($adminController) {
        $group->get('/users', [$adminController, 'getUsers']);
        $group->put('/users/{id}/role', [$adminController, 'updateUserRole']);
        $group->post('/create-user', [$adminController, 'createUser']);
        $group->delete('/users/{id}', [$adminController, 'deleteUser']);
        $group->put('/reset-password', [$adminController, 'resetPassword']);
        $group->get('/logs', [$adminController, 'getLogs']);
        $group->get('/assign-data', [$adminController, 'getCoursesAndLecturers']);
        $group->post('/assign-lecturer', [$adminController, 'assignLecturer']);
        $group->post('/assign-lecturer-direct', [$adminController, 'assignLecturerDirect']);
        $group->post('/courses', [$adminController, 'createCourse']);
        $group->get('/dashboard-stats', [$adminController, 'getDashboardStats']);
        $group->get('/profile', [$adminController, 'getProfile']);
        $group->put('/profile', [$adminController, 'updateProfile']);

    });

};
