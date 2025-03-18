<?php
$conn = new mysqli("localhost", "admEvoGraph", "evoGraph123", "evograph_db");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["reset-email"];
    $newPassword = $_POST["new-password"];

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $sql = "SELECT * FROM funcionarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $updateSql = "UPDATE funcionarios SET senha = ? WHERE email = ?";
        $updateStmt = $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ss", $hashedPassword, $email);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Senha alterada com sucesso!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erro ao alterar a senha."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "E-mail não encontrado."]);
    }

    $stmt->close();
}

$conn->close();
?>