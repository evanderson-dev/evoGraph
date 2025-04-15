<?php
namespace EvoGraph\Controllers;

class DashboardController {
    private $db;

    public function __construct($db) {
        error_log("Inicializando DashboardController");
        $this->db = $db;
    }

    public function index() {
        error_log("Carregando dashboard");
        session_start();
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            error_log("Usuário não autenticado, redirecionando para login");
            header('Location: /login');
            exit;
        }
        try {
            error_log("Renderizando view do dashboard");
            require_once __DIR__ . '/../Views/dashboard/index.php';
        } catch (Exception $e) {
            error_log("Erro ao carregar dashboard: " . $e->getMessage());
            http_response_code(500);
            exit("Erro interno no servidor");
        }
    }
}