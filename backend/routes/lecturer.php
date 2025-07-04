<?php
namespace App\Controllers;

use Slim\App;
use App\Controllers\LecturerController;

return function (App $app) {
    $app->group('/api/lecturer', function ($group) {
        $group->get('/courses/{course_id}/students', LecturerController::class . ':getCourseStudents');
        $group->post('/courses/{course_id}/students', LecturerController::class . ':addStudentToCourse');
        $group->delete('/courses/{course_id}/students/{student_id}', LecturerController::class . ':removeStudentFromCourse');
        $group->get('/my-courses', LecturerController::class . ':getMyCourses');

    });
};
