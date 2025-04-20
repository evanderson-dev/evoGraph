<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="./assets/css/dashboard.css" />
    <link rel="stylesheet" href="./assets/css/sidebar.css" />
    <link rel="stylesheet" href="./assets/css/relatorio-google.css" />
    <link rel="stylesheet" href="./assets/css/modal-add-funcionario.css" />
    <link rel="stylesheet" href="./assets/css/modal-add-turma.css" />
    <link rel="stylesheet" href="./assets/css/modal-add-aluno.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Meu Perfil</title>
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
            <a href="#"><i class="fa-solid fa-chart-bar"></i>Relatórios</a>
            <a href="my_profile.php"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>

            <?php if ($cargo === "Coordenador" || $cargo === "Diretor" || $cargo === "Administrador"): ?>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor" || $cargo === "Administrador"): ?>
                    <a href="#" onclick="openAddFuncionarioModal()"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <?php endif; ?>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                </div>
            </div>
            <?php endif; ?>
            
            <a href="funcionarios.php"><i class="fa-solid fa-users"></i>Funcionários</a>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
        </div>
        <!-- FIM SIDEBAR -->

        <div class="main-content" id="main-content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Formulário do Google Forms</span>
            </div>

            <section class="meu-perfil">
                <div id="message-box"></div>
                <div class="profile-form">
                    <form id="profile-form" enctype="multipart/form-data">
                        <input type="hidden" name="save_profile" value="1">
                        
                        <?php
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
                                require_once 'conexao.php'; // seu arquivo de conexão com o banco

                                $arquivo = $_FILES['csv_file']['tmp_name'];
                                $csv = array_map('str_getcsv', file($arquivo));
                                
                                $header = array_map('trim', $csv[0]);
                                unset($csv[0]);

                                foreach ($csv as $linha) {
                                    $linha = array_map('trim', $linha);
                                    $dados = array_combine($header, $linha);

                                    // Pega o e-mail
                                    $email = $dados["Endereço de e-mail"];

                                    // Busca aluno pelo e-mail
                                    $stmt = $pdo->prepare("SELECT id FROM alunos WHERE email = ?");
                                    $stmt->execute([$email]);
                                    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

                                    if ($aluno) {
                                        $aluno_id = $aluno['id'];
                                        $data = date('Y-m-d');
                                        $conteudo = json_encode($dados, JSON_UNESCAPED_UNICODE);

                                        $insert = $pdo->prepare("INSERT INTO respostas_formulario (aluno_id, data, conteudo) VALUES (?, ?, ?)");
                                        $insert->execute([$aluno_id, $data, $conteudo]);
                                    }
                                }

                                echo "<script>alert('Importação concluída com sucesso!');</script>";
                            }
                            ?>
                            <input type="file" name="csv_file" accept=".csv" required>
                            <button type="submit">Importar Respostas</button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <?php if ($cargo === "Coordenador" || $cargo === "Diretor" || $cargo === "Administrador"): ?>
    <div id="modal-cadastrar-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <footer>
        <p>&copy; 2025 evoGraph. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    <script src="./assets/js/utils.js"></script>
    <script src="./assets/js/modal-add-funcionario.js"></script>
    <script src="./assets/js/modal-add-turma.js"></script>
    <script src="./assets/js/modal-add-aluno.js"></script>
    
    <script src="./assets/js/my-profile.js"></script>
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