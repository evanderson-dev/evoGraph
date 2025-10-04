<?php
// gerenciar_chamadas.php (nova página para consulta de chamadas por cargos superiores)
session_start();

// Verificar se o usuário está logado e tem cargo permitido (acima de Professor)
$allowed_cargos = ['Coordenador', 'Diretor', 'Administrador'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION["cargo"], $allowed_cargos)) {
    header('Location: index.php');
    exit;
}

// Definir variáveis da sessão
$cargo = $_SESSION["cargo"];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="./assets/css/dashboard.css" />
    <link rel="stylesheet" href="./assets/css/sidebar.css" />
    <link rel="stylesheet" href="./assets/css/gerenciar_chamadas.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Gerenciar Chamadas</title>
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
            <a href="gerenciar_chamadas.php" class="sidebar-active"><i class="fa-solid fa-clipboard-list"></i>Gerenciar Chamadas</a>

            <?php if (in_array($cargo, ['Coordenador', 'Diretor', 'Administrador'])): ?>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <a href="#" onclick="openAddFuncionarioModal(); return false;"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                    <a href="#" onclick="openAddBnccModal(); return false;"><i class="fa-solid fa-book"></i>BNCC/Dados Escolares</a>
                </div>
            </div>
            <a href="funcionarios.php"><i class="fa-solid fa-users"></i>Funcionários</a>
            <?php endif; ?>

            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
        </div>
        <!-- FIM SIDEBAR -->

        <div class="main-content" id="main-content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Gerenciar Chamadas</span>
            </div>

            <section class="gerenciar-chamadas-section">
                <div id="message-box"></div>
                
                <!-- Filtros: Turma e Data -->
                <div class="filtros-chamadas">
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
                    <button type="button" id="carregarChamadasBtn" class="btn-carregar" disabled>Carregar Chamadas</button>
                </div>

                <!-- Tabela de Presenças -->
                <div id="tabelaChamadas" style="display: none;">
                    <h3>Presenças da Turma Selecionada</h3>
                    <div class="tabela-scroll">
                        <table id="tabela-presencas">
                            <thead>
                                <tr>
                                    <th>Nome do Aluno</th>
                                    <th>Matrícula</th>
                                    <th>Presente</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dados serão inseridos via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php if (in_array($cargo, ['Coordenador', 'Diretor', 'Administrador'])): ?>
    <div id="modal-cadastrar-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-bncc" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <footer>
        <p>© 2025 evoGraph. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./assets/js/sidebar.js"></script>
    <script src="./assets/js/utils.js"></script>
    <script src="./assets/js/modal-add-funcionario.js"></script>
    <script src="./assets/js/modal-add-turma.js"></script>
    <script src="./assets/js/modal-add-aluno.js"></script>
    <script src="./assets/js/modal-add-bncc.js"></script>
    
    <!-- Script inline para injetar o cargo (não precisa de ID) -->
    <script>
        window.currentCargo = '<?php echo json_encode($cargo); ?>';
    </script>
    
    <script src="./assets/js/gerenciar_chamadas.js"></script>
</body>
</html>