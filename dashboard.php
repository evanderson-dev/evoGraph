<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.html");
    exit;
}

// Conectar ao MySQL
$conn = new mysqli("localhost", "root", "root123", "evograph_db");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Buscar turmas do professor logado
$professor_id = $_SESSION["professor_id"];
$sql = "SELECT nome, ano FROM turmas WHERE professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./css/main.css" rel="stylesheet" />
    <title>evoGraph Dashboard</title>
</head>
<body>
    <div class="container">
        <h1>Bem-vindo ao Dashboard do evoGraph, <?php echo htmlspecialchars($_SESSION["email"]); ?>!</h1>
        <h2>Suas Turmas</h2>
        <ul class="turmas-list">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($row["nome"]) . " - Ano " . $row["ano"] . "</li>";
                }
            } else {
                echo "<li>Nenhuma turma cadastrada.</li>";
            }
            $stmt->close();
            $conn->close();
            ?>
        </ul>
        <a href="logout.php">Sair</a>
    </div>
</body>
</html>