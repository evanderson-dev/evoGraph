<?php
namespace EvoGraph\Models;

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function authenticate($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, nome, email, senha, tipo FROM funcionarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['senha'])) {
                return $user;
            }
        }
        return false;
    }
}