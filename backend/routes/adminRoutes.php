<?php

use App\Controllers\AdminController;
use Slim\App;

return function (App $app) {
    $db = $app->getContainer()->get('db');
    $controller = new AdminController($db);

    $app->group('/api/admin', function ($group) use ($controller) {
        $group->get('/users', [$controller, 'getUsers']);
        $group->post('/assign-lecturer', [$controller, 'assignLecturer']);
        $group->get('/logs', [$controller, 'getLogs']);
        $group->post('/reset-password', [$controller, 'resetPassword']);
    });
};
