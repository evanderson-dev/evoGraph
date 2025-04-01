<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor")) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="./css/modal-delete-funcionario.css" />
    <link rel="stylesheet" href="./css/modal-edit-funcionario.css" />
    <link rel="stylesheet" href="./css/modal-add-funcionario.css" />
    <link rel="stylesheet" href="./css/modal-delete-turma.css" />
    <link rel="stylesheet" href="./css/modal-edit-turma.css" />
    <link rel="stylesheet" href="./css/modal-add-turma.css" />
    <link rel="stylesheet" href="./css/modal-add-aluno.css" />
    <link rel="stylesheet" href="./css/modal-details.css" />
    <link rel="stylesheet" href="./css/modal-delete.css" />
    <link rel="stylesheet" href="./css/modal-edit.css" />
    <link rel="stylesheet" href="./css/sidebar.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Gerenciar Funcionários</title>
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="info-header">
            <button class="menu-toggle" id="menu-toggle"><i class="fa-solid fa-bars"></i></button>
            <div class="logo">
                <h3>evoGraph</h3>
            </div>
        </div>
        <div class="info-header">
            <i class="fa-solid fa-envelope"></i>
            <i class="fa-solid fa-bell"></i>
            <i class="fa-solid fa-user"></i>
            <img src="https://avatars.githubusercontent.com/u/94180306?s=40&v=4" alt="User" class="user-icon">
        </div>
    </header>
    <!-- FIM HEADER -->

    <section class="main">
        <!-- SIDEBAR -->
        <div class="sidebar" id="sidebar">
            <a href="dashboard.php"><i class="fa-solid fa-house"></i>Home</a>
            <a href="#"><i class="fa-solid fa-chart-bar"></i>Relatórios</a>
            <a href="meu_perfil.php"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <a href="#" onclick="openAddFuncionarioModal()"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                </div>
            </div>
            <a href="funcionarios.php" class="sidebar-active"><i class="fa-solid fa-users"></i>Funcionários</a>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
            <div class="separator"></div><br>
        </div>
        <!-- FIM SIDEBAR -->

        <div class="content" id="content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a> / Gerenciar Funcionários</span>
                <!--<h2>Gerenciar Funcionários</h2>-->
                <div class="separator"></div>
                <p>                    
                    <div class="filtros">
                        <input type="text" id="search-funcionario" placeholder="Pesquisar por Nome ou RF" class="search-bar">
                        <select id="filtro-cargo">
                            <option value="">Todos os Cargos</option>
                            <option value="Professor">Professor</option>
                            <option value="Coordenador">Coordenador</option>
                            <option value="Diretor">Diretor</option>
                        </select>
                        <button id="btn-pesquisar" class="btn">Pesquisar</button>
                    </div>
                </p>
            </div>

            <!-- Tabela de Funcionários -->
            <div class="tabela-funcionarios">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Sobrenome</th>
                            <th>E-mail</th>
                            <th>RF</th>
                            <th>Cargo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-funcionarios">
                        <!-- Dados serão preenchidos via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Modal de Edição de Funcionário -->
    <div id="modal-edit-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>

    <!-- Modal de Exclusão de Funcionário -->
    <div id="modal-delete-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/utils.js"></script>
    <script src="js/modal-add-funcionario.js"></script>
    <script src="js/modal-delete-turma.js"></script>
    <script src="js/modal-edit-turma.js"></script>
    <script src="js/modal-add-turma.js"></script>
    <script src="js/modal-add-aluno.js"></script>
    <script src="js/modal-details.js"></script>
    <script src="js/modal-delete.js"></script>
    <script src="js/funcionarios.js"></script>
    <script src="js/modal-edit.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/ajax.js"></script>
</body>
</html>
<?php $conn->close(); ?>