<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.html");
    exit;
}
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
        <p>Você está logado com sucesso.</p>
        <a href="logout.php">Sair</a>
    </div>
</body>
</html>