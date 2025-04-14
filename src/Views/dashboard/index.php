<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EvoGraph</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <link rel="stylesheet" href="/assets/css/modal-add-aluno.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <div class="container">
        <aside class="sidebar">
            <!-- Conteúdo da sidebar -->
        </aside>
        <main>
            <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h1>
            <!-- Conteúdo do dashboard -->
        </main>
    </div>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/dashboard.js"></script>
    <script src="/assets/js/modal-add-aluno.js"></script>
</body>
</html>