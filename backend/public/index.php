<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Dotenv\Dotenv;
use Slim\Views\PhpRenderer;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Create container
$container = new \DI\Container();
AppFactory::setContainer($container);

// Register view renderer (NOW container is ready ✅)
$container->set('view', function () {
    return new PhpRenderer(__DIR__ . '/../templates/');
});

// DB connection
$container->set('db', function () {
    $host = $_ENV['DB_HOST'];
    $db = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];

    $dsn = "mysql:host=127.0.0.1;port=3306;dbname=$db;charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    return new PDO($dsn, $user, $pass, $options);
});

// Create app
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// CORS
$app->add(function (Request $request, Handler $handler): Response {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// OPTIONS
$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

// Routes
$app->get('/', function ($request, $response) {
    $response->getBody()->write("Welcome to Course Mark Management Backend!");
    return $response;
});

$app->get('/api/hello', function ($request, $response) {
    $response->getBody()->write(json_encode(['message' => 'Hello from Slim']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/test-db', function ($request, $response) {
    $db = $this->get('db');
    $stmt = $db->query("SELECT * FROM users"); // tukar ikut table yang wujud
    $data = $stmt->fetchAll();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/dashboard', function ($request, $response, $args) {
    // Hardcode sementara (student ID 1)
    $studentId = 1;

    $db = $this->get('db');
    $stmt = $db->prepare("
        SELECT a.title AS component, sa.obtained_mark, a.max_mark
        FROM assessments a
        JOIN student_assessments sa ON a.id = sa.assessment_id
        WHERE sa.student_id = :studentId
    ");
    $stmt->execute(['studentId' => $studentId]);
    $marks = $stmt->fetchAll();

    $total = array_sum(array_column($marks, 'mark'));
    $totalPossible = array_sum(array_column($marks, 'total_mark'));

    return $this->get('view')->render($response, 'dashboard.php', [
        'marks' => $marks,
        'total' => $total,
        'totalPossible' => $totalPossible
    ]);


});

// POST /api/calculate-gpa
$app->get('/api/calculate-gpa', function ($request, $response) {
    $body = $request->getBody()->getContents();
    $data = json_decode($body, true);

    if (!isset($data['courses']) || !is_array($data['courses'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing or invalid course list']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $gradeMap = [
        'A+' => 4.0, 'A' => 4.0, 'A-' => 3.67,
        'B+' => 3.33, 'B' => 3.0, 'B-' => 2.67,
        'C+' => 2.33, 'C' => 2.0, 'C-' => 1.67,
        'D+' => 1.33, 'D' => 1.0, 'D-' => 0.67,
        'E' => 0.0
    ];

    $totalCredits = 0;
    $totalPoints = 0;

    foreach ($data['courses'] as $course) {
        $courseName = $course['name'] ?? 'Unnamed Course';
        $credit = $course['credit'] ?? 0;
        $grade = $course['grade'] ?? null;
        $point = $gradeMap[$grade] ?? null;

        if ($credit > 0 && isset($gradeMap[$grade])) {
            $totalCredits += $credit;
            $totalPoints += $gradeMap[$grade] * $credit;
        }
    }

    $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;

    $response->getBody()->write(json_encode(['Your GPA is: ' => $gpa]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


// GET /api/student/{id}/courses
$app->get('/api/student/{id}/courses', function ($request, $response, $args) {
    $db = $this->get('db');

    // No WHERE clause, no :studentId, no execute()
    $stmt = $db->query("
        SELECT course_code, course_name, credit_hours
        FROM courses
    ");

    $courses = $stmt->fetchAll();

    $response->getBody()->write(json_encode($courses));
    return $response->withHeader('Content-Type', 'application/json');
});





// Load routes from folder
(require __DIR__ . '/../routes/auth.php')($app);
(require __DIR__ . '/../routes/advisor.php')($app);


// Final run
$app->run();
