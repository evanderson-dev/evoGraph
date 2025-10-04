<?php
// chamada.php (nova página para registro de chamada)
session_start();

// Verificar se o usuário está logado e é Professor
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["cargo"] !== 'Professor') {
    header('Location: index.php');
    exit;
}

// Definir variáveis da sessão
$cargo = $_SESSION["cargo"];
$funcionario_id = $_SESSION["funcionario_id"];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/sidebar.css" />
    <link rel="stylesheet" href="./assets/css/chamada.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Chamada</title>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <h1>evoGraph</h1>
        <div class="icons">
            <i class="fas fa-envelope"></i>
            <i class="fas fa-bell"></i>
            <i class="fas fa-cog"></i>
            <i class="fas fa-user"></i>
        </div>
    </header>
    <!-- Fim do Header -->

    <div class="container">

        <!-- SIDEBAR -->
        <div class="sidebar" id="sidebar">
            <a href="dashboard.php"><i class="fa-solid fa-house"></i>Home</a>
            <a href="relatorio-google.php"><i class="fa-solid fa-chart-bar"></i>Importar Relatório</a>
            <a href="relatorios_bncc.php"><i class="fa-solid fa-chart-bar"></i>Visualizar Relatório</a>
            <a href="my_profile.php"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>
            <a href="chamada.php" class="sidebar-active"><i class="fa-solid fa-clipboard-check"></i>Chamada</a>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
        </div>
        <!-- FIM SIDEBAR -->

        <div class="main-content" id="main-content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Registro de Chamada</span>
            </div>

            <section class="chamada-section">
                <div id="message-box"></div>
                
                <!-- Seleção de Turma e Data -->
                <div class="chamada-header">
                    <div class="form-group">
                        <label for="turmaSelect">Selecione a Turma:</label>
                        <select id="turmaSelect" required>
                            <option value="">Carregando turmas...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dataChamada">Data da Chamada:</label>
                        <input type="date" id="dataChamada" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <button type="button" id="loadAlunosBtn" class="btn-carregar" disabled>Carregar Alunos</button>
                </div>

                <!-- Lista de Alunos com Checkboxes -->
                <div id="alunosList" style="display: none;">
                    <h3>Marque a Presença:</h3>
                    <div class="alunos-container">
                        <!-- Alunos serão inseridos aqui via JS -->
                    </div>
                    <button type="button" id="salvarChamadaBtn" class="btn-salvar" disabled>Salvar Chamada</button>
                </div>
            </section>
        </div>
    </div>

    <footer>
        <p>© 2025 evoGraph. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./assets/js/sidebar.js"></script>
    <script src="./assets/js/chamada.js"></script>
</body>
</html>