<?php
// Conectar ao MySQL (ajuste a senha do root)
$conn = new mysqli("localhost", "admEvoGraph", "evoGraph1234", "evograph_db");

// Verificar conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Pegar dados do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM professores WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($password === $row["senha"]) {
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