<?php


use Slim\App;
use App\Controllers\GradePredictorController;

return function (App $app) {
    $app->group('/api', function ($group) {
        $group->post('/grade-predictor', GradePredictorController::class . ':predict');
    });
};
