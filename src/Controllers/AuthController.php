<?php
namespace EvoGraph\Controllers;

use EvoGraph\Models\User;

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            header('Content-Type: application/json');
            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'E-mail e senha são obrigatórios.']);
                return;
            }

            $user = $this->userModel->authenticate($email, $password);
            if ($user) {
                session_start();
                $_SESSION['loggedin'] = true;
                $_SESSION['funcionario_id'] = $user['id'];
                $_SESSION['nome'] = $user['nome'];
                $_SESSION['tipo'] = $user['tipo'];
                echo json_encode(['success' => true, 'redirect' => '/dashboard']);
            } else {
                echo json_encode(['success' => false, 'message' => 'E-mail ou senha inválidos.']);
            }
        } else {
            require_once __DIR__ . '/../Views/auth/login.php';
        }
    }
}