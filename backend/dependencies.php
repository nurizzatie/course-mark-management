<?php
use App\Controllers\StudentController;
use Psr\Container\ContainerInterface;
use App\Controllers\RemarkController;

$container->set(RemarkController::class, function ($c) {
    return new RemarkController($c);
});


/** @var \DI\Container $container */
$container->set(StudentController::class, function (ContainerInterface $c) {
    return new StudentController($c);
});


$container->set(PDO::class, function () {
    $host = '127.0.0.1';
    $db   = 'course_mark_db';
    $user = 'root'; // betulkan sini
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
});
