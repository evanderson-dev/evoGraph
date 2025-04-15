<?php
namespace EvoGraph\Models;

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function authenticate($email, $password) {
        error_log("Autenticando usuário com email: $email");
        $stmt = $this->conn->prepare("SELECT id, nome, email, senha, cargo FROM funcionarios WHERE email = ?");
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
    }
}