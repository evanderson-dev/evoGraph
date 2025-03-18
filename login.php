<?php
session_start();

$conn = new mysqli("localhost", "admEvoGraph", "evoGraph123", "evograph_db");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM funcionarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["senha"])) {
            $_SESSION["loggedin"] = true;
            $_SESSION["email"] = $email;
            $_SESSION["funcionario_id"] = $row["id"];
            $_SESSION["cargo"] = $row["cargo"];
            echo json_encode(["status" => "success", "message" => "Login bem-sucedido!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Senha inválida."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "E-mail não encontrado."]);
    }

    $stmt->close();
}

$conn->close();
?>