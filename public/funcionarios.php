<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}
require_once 'db_connection.php';
$funcionario_id = $_SESSION["funcionario_id"];
$cargo = $_SESSION["cargo"];

$sql = "SELECT nome, foto FROM funcionarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $funcionario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$default_photo = './assets/img/employee_photos/default_photo.jpg';
$photo_path = $user['foto'] ? $user['foto'] : $default_photo;
$user['foto'] = file_exists($photo_path) ? $photo_path : $default_photo;
$ext = pathinfo($user['foto'], PATHINFO_EXTENSION);
$square_photo_path = str_replace(".$ext", "_square.$ext", $user['foto']);
$header_photo = file_exists($square_photo_path) ? $square_photo_path : $default_photo;
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
    <link rel="stylesheet" href="./assets/css/modals/modal-add-turma.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-add-funcionario.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-add-aluno.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-delete-funcionario.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-edit-funcionario.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Gerenciar Funcionários</title>
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
            <a href="dashboard.php" class="sidebar-active"><i class="fa-solid fa-house"></i>Home</a>
            <a href="relatorio-google.php"><i class="fa-solid fa-chart-bar"></i>Importar Relatório</a>
            <a href="relatorios_bncc.php"><i class="fa-solid fa-chart-bar"></i>Visualizar Relatório</a>
            <a href="my_profile.php"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>

            <?php if (in_array($cargo, ['Coordenador', 'Diretor', 'Administrador'])): ?>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <a href="#" onclick="openAddFuncionarioModal()"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                </div>
            </div>
            <a href="funcionarios.php"><i class="fa-solid fa-users"></i>Funcionários</a>
            <?php endif; ?>
            
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
        </div>
        <!-- FIM SIDEBAR -->

        <!-- Seção de Conteúdo -->
        <div class="main-content" id="main-content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Gerenciamento de Funcionários</span>
                <div class="separator"></div>
                <p>                    
                    <div class="filtros">
                        <input type="text" id="search-funcionario" placeholder="Pesquisar por Nome ou RF" class="search-bar">
                        <select id="filtro-cargo">
                            <option value="">Todos os Cargos</option>
                            <option value="Professor">Professor</option>
                            <option value="Coordenador">Coordenador</option>
                            <option value="Diretor">Diretor</option>
                            <option value="Administrador">Administrador</option>
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
                            <th>Nome Completo</th>
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
    </div>

    <!-- Modal de Edição de Funcionário -->
    <div id="modal-edit-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>

    <!-- Modal de Exclusão de Funcionário -->
    <div id="modal-delete-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>

    <!-- Modal de Cadastro de Turma (exclusivo para Coordenador e Diretor) -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor" || $_SESSION["cargo"] === "Administrador"): ?>
    <div id="modal-cadastrar-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Modal de Adição de Funcionário (exclusivo para Coordenador e Diretor) -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor" || $_SESSION["cargo"] === "Administrador"): ?>
    <div id="modal-add-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Modal de Cadastro de Aluno -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor" || $_SESSION["cargo"] === "Administrador"): ?>
    <div id="modal-add-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>
    
    <!-- Scripts -->
    <footer>
        <p>&copy; 2025 evoGraph. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./assets/js/utils.js"></script>
    <script src="./assets/js/modal-add-funcionario.js"></script>
    <script src="./assets/js/modal-add-turma.js"></script>
    <script src="./assets/js/modal-add-aluno.js"></script>
    
    <script src="./assets/js/funcionarios.js"></script>
    <script src="./assets/js/dashboard.js"></script>
    <script src="./assets/js/ajax.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
    
            // Atualiza o estado no localStorage
            const isActive = sidebar.classList.contains('active');
            localStorage.setItem('sidebarActive', isActive);
        }
    
        $(document).ready(function() {
            // Inicializa o estado da sidebar com base no localStorage
            if (localStorage.getItem('sidebarActive') === 'true') {
                $('#sidebar').addClass('active');
                $('#main-content').addClass('shifted');
            }
    
            $('#menu-toggle').on('click', function() {
                toggleSidebar();
            });
    
            // Toggle do submenu
            $('.sidebar-toggle').on('click', function(e) {
                e.preventDefault();
                const $submenu = $(this).next('.submenu');
                const $toggleIcon = $(this).find('.submenu-toggle');
    
                $submenu.slideToggle(200); // Animação suave
                $toggleIcon.toggleClass('open'); // Gira a seta
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>