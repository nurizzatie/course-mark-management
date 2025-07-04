<?php

use App\Controllers\AdvisorController;

return function ($app) {
    $container = $app->getContainer();

    $app->get('/api/advisor/students', function ($request, $response, $args) use ($container) {
        $controller = new AdvisorController($container);
        return $controller->getStudents($request, $response, $args);
    });
};



