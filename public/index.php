<?php
require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/Config/Database.php';

session_start();

$db = \EvoGraph\Config\Database::getConnection();
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($request) {
    case '/':
    case '/login':
        $controller = new \EvoGraph\Controllers\AuthController($db);
        $controller->login();
        break;
    case '/dashboard':
        $controller = new \EvoGraph\Controllers\DashboardController($db);
        $controller->index();
        break;
    default:
        http_response_code(404);
        echo "404 - Página não encontrada";
        break;
}