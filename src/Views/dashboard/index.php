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
    <?php
    try {
        include __DIR__ . '/../layouts/header.php';
    } catch (Exception $e) {
        error_log("Erro ao incluir header.php: " . $e->getMessage());
        echo "Erro ao carregar o cabeçalho.";
    }
    ?>
    <div class="container">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li><a href="/alunos">Alunos</a></li>
                    <li><a href="/turmas">Turmas</a></li>
                    <li><a href="/funcionarios">Funcionários</a></li>
                    <li><a href="/logout">Sair</a></li>
                </ul>
            </nav>
        </aside>
        <main>
            <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h1>
            <p>Cargo: <?php echo htmlspecialchars($_SESSION['cargo']); ?></p>
            <!-- Adicione o conteúdo do dashboard aqui -->
        </main>
    </div>
    <?php
    try {
        include __DIR__ . '/../layouts/footer.php';
    } catch (Exception $e) {
        error_log("Erro ao incluir footer.php: " . $e->getMessage());
        echo "Erro ao carregar o rodapé.";
    }
    ?>
    <script src="/assets/js/script.js"></script>
    <script src="/assets/js/dashboard.js"></script>
    <script src="/assets/js/modal-add-aluno.js"></script>
</body>
</html>