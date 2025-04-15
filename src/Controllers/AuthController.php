<?php
namespace EvoGraph\Controllers;

use EvoGraph\Models\User;

class AuthController {
    private $userModel;

    public function __construct($db) {
        error_log("Inicializando AuthController");
        $this->userModel = new User($db);
    }

    public function login() {
        error_log("Método login chamado, Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            error_log("Tentativa de login com email: $email");
            header('Content-Type: application/json');
            if (empty($email) || empty($password)) {
                error_log("Campos vazios detectados");
                echo json_encode([
                    'success' => false,
                    'message' => 'E-mail e senha são obrigatórios.',
                    'status' => 'error'
                ]);
                return;
            }

            $user = $this->userModel->authenticate($email, $password);
            if ($user) {
                error_log("Login bem-sucedido para $email");
                session_start();
                $_SESSION['loggedin'] = true;
                $_SESSION['funcionario_id'] = $user['id'];
                $_SESSION['nome'] = $user['nome'];
                $_SESSION['cargo'] = $user['cargo'];
                echo json_encode([
                    'success' => true,
                    'message' => 'Login bem-sucedido! Redirecionando...',
                    'status' => 'success',
                    'redirect' => '/dashboard'
                ]);
            } else {
                error_log("Falha no login para $email");
                echo json_encode([
                    'success' => false,
                    'message' => 'E-mail ou senha inválidos.',
                    'status' => 'error'
                ]);
            }
        } else {
            error_log("Carregando página de login");
            require_once __DIR__ . '/../Views/auth/login.php';
        }
    }
}