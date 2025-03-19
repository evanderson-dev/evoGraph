<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.html");
    exit;
}

require_once 'db_connection.php';

// Buscar nome e sobrenome do usuário logado
$sql = "SELECT nome, sobrenome FROM funcionarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["funcionario_id"]);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./css/global.css" rel="stylesheet" />
</head>
<body>
    <div class="user-info">
        Logado como: <?php echo htmlspecialchars($usuario["nome"] . " " . $usuario["sobrenome"] . " (" . $_SESSION["cargo"] . ")"); ?>
    </div>