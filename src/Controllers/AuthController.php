<?php
namespace EvoGraph\Controllers;

use EvoGraph\Models\User;

class AuthController {
    private $userModel;

    public function __construct($db) {
        error_log("Inicializando AuthController");
        try {
            $this->userModel = new User($db);
            error_log("User model instanciado com sucesso");
        } catch (Exception $e) {
            error_log("Erro ao instanciar User: " . $e->getMessage());
            throw $e;
        }
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

            try {
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
            } catch (Exception $e) {
                error_log("Erro durante autenticação: " . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro interno no servidor.',
                    'status' => 'error'
                ]);
            }
        } else {
            error_log("Carregando página de login");
            require_once __DIR__ . '/../Views/auth/login.php';
        }
    }
}