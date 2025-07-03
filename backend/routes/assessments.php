<?php
use App\Controllers\AssessmentController;

$app->get('/api/courses/{courseId}/assessments', AssessmentController::class . ':getByCourse');
