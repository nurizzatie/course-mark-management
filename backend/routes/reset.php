<?php
use App\Controllers\ResetController;
use Slim\App;

return function (App $app) {
    $container = $app->getContainer();
    $controller = $container->get(ResetController::class);

    $app->post('/api/reset-request', [$controller, 'requestReset']);
    $app->get('/api/reset-requests', [$controller, 'getRequests']);
    $app->put('/api/reset-done', [$controller, 'markResetDone']);
    $app->get('/api/admin/notifications/{id}', [$controller, 'getNotifications']);

};
