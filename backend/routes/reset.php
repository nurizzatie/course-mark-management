<?php
use Slim\App;
use App\Controllers\ResetController;

return function (App $app) {
    $container = $app->getContainer();
    $controller = $container->get(ResetController::class);

    $app->post('/api/reset-request', [$controller, 'requestReset']);
    $app->get('/api/reset-requests', [$controller, 'getRequests']);
};
