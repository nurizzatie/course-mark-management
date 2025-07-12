<?php
namespace App\Controllers;

use Slim\App;
use App\Controllers\LecturerController;

return function (App $app) {
    $app->group('/api/lecturer', function ($group) {
        $group->get('/profile', [\App\Controllers\LecturerController::class, 'getProfile']);
        $group->get('/courses/{course_id}/students', LecturerController::class . ':getCourseStudents');
        $group->post('/courses/{course_id}/students', LecturerController::class . ':addStudentToCourse');
        $group->delete('/courses/{course_id}/students/{student_id}', LecturerController::class . ':removeStudentFromCourse');
        $group->get('/my-courses', LecturerController::class . ':getMyCourses');
        $group->get('/courses/{course_id}', LecturerController::class . ':getCourse');
        $group->post('/courses', LecturerController::class . ':createCourse');
        $group->put('/courses/{id}', LecturerController::class . ':updateCourse');
        $group->delete('/courses/{id}', LecturerController::class . ':deleteCourse');
        $group->get('/courses/{course_id}/assessments', LecturerController::class . ':getAssessments');
        $group->post('/courses/{course_id}/assessments', LecturerController::class . ':addAssessment');
        $group->put('/courses/{course_id}/assessments/{id}', LecturerController::class . ':updateAssessment');
        $group->get('/courses/{course_id}/marks', LecturerController::class . ':getCourseMarks');
        $group->post('/courses/{course_id}/marks', LecturerController::class . ':saveCourseMarks');
        $group->post('/courses/{course_id}/marks/upload', LecturerController::class . ':uploadMarksCsv');
        $group->get('/courses/{course_id}/analytics', LecturerController::class . ':getCourseAnalytics');
        $group->get('/remark-requests', LecturerController::class . ':getRemarkRequests');
        $group->put('/remark-requests/{id}', LecturerController::class . ':updateRemarkRequest');
        $group->put('/profile', LecturerController::class . ':updateProfile');
    });

};
