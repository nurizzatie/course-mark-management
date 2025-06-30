<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Dotenv\Dotenv;

//database
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$container = new \DI\Container();
AppFactory::setContainer($container);

$container->set('db', function () {
    $host = $_ENV['DB_HOST'];
    $db   = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];

    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    return new PDO($dsn, $user, $pass, $options);
});

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->add(function (Request $request, Handler $handler): Response {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:8081')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

// Sample route
$app->get('/api/hello', function ($request, $response) {
    $response->getBody()->write(json_encode(['message' => 'Hello from Slim']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/test-db', function ($request, $response, $args) {
    $db = $this->get('db');
    $stmt = $db->query("SELECT COUNT(*) as user_count FROM users");
    $data = $stmt->fetch();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();