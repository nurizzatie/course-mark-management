<?php
use App\Controllers\StudentController;
use Psr\Container\ContainerInterface;
use App\Controllers\RemarkController;

// === CONTROLLERS ===
$container->set(RemarkController::class, function ($c) {
    return new RemarkController($c);
});

$container->set(StudentController::class, function (ContainerInterface $c) {
    return new StudentController($c);
});

// === DATABASE CONNECTION ===
$container->set(PDO::class, function () {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $db   = $_ENV['DB_NAME'] ?? 'course_mark_db';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
});

// Optional alias to allow $this->get('db') style
$container->set('db', \DI\get(PDO::class));
