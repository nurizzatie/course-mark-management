<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Dotenv\Dotenv;
// Removed: use Slim\Views\PhpRenderer; // No longer needed for HTML rendering
use DI\Container;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Create container
$container = new Container();
AppFactory::setContainer($container);


// DB connection
$container->set('db', function () {
    $host = $_ENV['DB_HOST'];
    $db = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];

    $dsn = "mysql:host={$host};port=3306;dbname=$db;charset=utf8mb4";

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

// OPTIONS (Pre-flight requests for CORS)
$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

// --- Routes Defined Directly in index.php ---

// Homepage - Simple message, not a rendered view
$app->get('/', function ($request, $response) {
    $response->getBody()->write("Welcome to Course Mark Management Backend API!");
    return $response;
});

// Simple Hello API endpoint
$app->get('/api/hello', function ($request, $response) {
    $response->getBody()->write(json_encode(['message' => 'Hello from Slim API']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Test DB connection
$app->get('/api/test-db', function ($request, $response) {
    $db = $this->get('db');
    // REMEMBER: Change 'users' to a table that actually exists in your DB!
    try {
        $stmt = $db->query("SELECT * FROM users LIMIT 1"); // Added LIMIT 1 for efficiency
        $data = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log the error (for debugging) and return a generic error message
        error_log("DB Test Error: " . $e->getMessage());
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(json_encode(['error' => 'Could not connect to or query database.']));
    }

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// API endpoint for Dashboard data (replaces old /dashboard route that rendered HTML)
$app->get('/api/dashboard', function ($request, $response, $args) {
    $studentId = 1; // Hardcode sementara (student ID 1) - **Remember to replace with dynamic fetching!**

    $db = $this->get('db');
    try {
        $stmt = $db->prepare("
            SELECT a.title AS component, sa.obtained_mark, a.max_mark
            FROM assessments a
            JOIN student_assessments sa ON a.id = sa.assessment_id
            WHERE sa.student_id = :studentId
        ");
        $stmt->execute(['studentId' => $studentId]);
        $marks = $stmt->fetchAll();

        $totalObtained = array_sum(array_column($marks, 'obtained_mark'));
        $totalPossible = array_sum(array_column($marks, 'max_mark'));

        $result = [
            'studentId' => $studentId,
            'assessments' => $marks,
            'summary' => [
                'totalObtained' => $totalObtained,
                'totalPossible' => $totalPossible,
                'percentage' => $totalPossible > 0 ? round(($totalObtained / $totalPossible) * 100, 2) : 0
            ]
        ];

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        error_log("Dashboard API Error: " . $e->getMessage());
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(json_encode(['error' => 'Failed to fetch dashboard data.']));
    }
});

// POST /api/calculate-gpa (Remains POST for body data)
$app->post('/api/calculate-gpa', function ($request, $response) {
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
        $courseName = $course['name'] ?? 'Unnamed Course'; // Just for safety
        $credit = $course['credit'] ?? 0;
        $grade = $course['grade'] ?? null;

        if ($credit > 0 && isset($gradeMap[$grade])) {
            $totalCredits += $credit;
            $totalPoints += $gradeMap[$grade] * $credit;
        }
    }

    $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;

    $response->getBody()->write(json_encode(['gpa' => $gpa])); // Changed key for clarity
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


// GET /api/student/{id}/courses - Query modified to fetch student-specific courses
$app->get('/api/student/{id}/courses', function ($request, $response, $args) {
    $studentId = $args['id']; // Get the student ID from the URL
    $db = $this->get('db');

    // **IMPORTANT**: This query assumes a 'student_courses' table
    // that links students to courses (many-to-many relationship).
    // Adjust this JOIN and WHERE clause if your schema is different.
    try {
        $stmt = $db->prepare("
            SELECT c.id, c.course_code, c.course_name, c.credit_hours
            FROM courses c
            JOIN student_courses sc ON c.id = sc.course_id
            WHERE sc.student_id = :studentId
        ");
        $stmt->execute([':studentId' => $studentId]);
        $courses = $stmt->fetchAll();

        $response->getBody()->write(json_encode($courses));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        error_log("Student Courses API Error: " . $e->getMessage());
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(json_encode(['error' => 'Failed to fetch student courses.']));
    }
});


// --- Load Routes from External Files ---
// This is where your GradePredictorController route is loaded!
(require __DIR__ . '/../routes/auth.php')($app);
<<<<<<< HEAD
(require __DIR__ . '/../routes/advisor.php')($app);

=======
(require __DIR__ . '/../routes/grade_predictor.php')($app);
(require __DIR__ . '/../routes/assessments.php')($app);
require __DIR__ . '/../dependencies.php';
>>>>>>> 0432e4e982f9c3e1919bc40e31f669e9ea6df021



// Final run the application
$app->run();