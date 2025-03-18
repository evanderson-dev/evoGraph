<?php
// Conectar ao MySQL (ajuste a senha do root)
$conn = new mysqli("localhost", "admEvoGraph", "evoGraph123", "evograph_db");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["reset-email"];
    $newPassword = $_POST["new-password"];

    // Gerar hash da nova senha
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Verificar se o e-mail existe
    $sql = "SELECT * FROM professores WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Atualizar a senha
        $updateSql = "UPDATE professores SET senha = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ss", $hashedPassword, $email);
        if ($updateStmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Senha alterada com sucesso!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erro ao alterar a senha."]);
        }
        $updateStmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "E-mail não encontrado."]);
    }

    $stmt->close();
}

$conn->close();
?>