<?php
// Iniciar sessão
session_start();

// Carregar autoload (vamos criar no próximo passo)
require_once __DIR__ . '/../src/autoload.php';

// Configurar conexão com o banco
require_once __DIR__ . '/../src/Config/Database.php';
$db = \EvoGraph\Config\Database::getConnection();

// Roteamento simples
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($request) {
    case '/':
    case '/dashboard':
        require_once __DIR__ . '/../src/Controllers/DashboardController.php';
        $controller = new \EvoGraph\Controllers\DashboardController($db);
        $controller->index();
        break;
    case '/login':
        require_once __DIR__ . '/../src/Controllers/AuthController.php';
        $controller = new \EvoGraph\Controllers\AuthController($db);
        $controller->login();
        break;
    case '/aluno/create':
        require_once __DIR__ . '/../src/Controllers/AlunoController.php';
        $controller = new \EvoGraph\Controllers\AlunoController($db);
        $controller->create();
        break;
    default:
        http_response_code(404);
        echo "404 - Página não encontrada";
        break;
}