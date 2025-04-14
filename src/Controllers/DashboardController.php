<?php
namespace EvoGraph\Controllers;

class DashboardController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        session_start();
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/../Views/dashboard/index.php';
    }
}