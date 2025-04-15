<?php
namespace EvoGraph\Models;

class User {
    private $conn;

    public function __construct($db) {
        error_log("Inicializando User model");
        $this->conn = $db;
    }

    public function authenticate($email, $password) {
        error_log("Autenticando usuário com email: $email");
        try {
            $stmt = $this->conn->prepare("SELECT id, nome, email, senha, cargo FROM funcionarios WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
            }
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['senha'])) {
                    error_log("Autenticação bem-sucedida para $email");
                    return $user;
                }
            }
            error_log("Autenticação falhou para $email");
            return false;
        } catch (Exception $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            throw $e;
        }
    }
}