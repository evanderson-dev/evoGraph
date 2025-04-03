<?php
    session_start();

    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("Location: index.php");
        exit;
    }

    require_once 'db_connection.php';

    $funcionario_id = $_SESSION["funcionario_id"];
    $cargo = $_SESSION["cargo"];

    // Buscar dados do funcionário
    $sql = "SELECT nome, foto FROM funcionarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $funcionario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        die("Erro ao carregar dados do usuário.");
    }

    // Definir foto padrão e verificar existência
    $default_photo = './img/employee_photos/default_photo.jpg';
    $photo_path = $user['foto'] ? $user['foto'] : $default_photo;
    $user['foto'] = file_exists($photo_path) ? $photo_path : $default_photo;

    // Definir caminho da foto quadrada para o header
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
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="./css/modal-add-turma.css" />
    <link rel="stylesheet" href="./css/modal-add-funcionario.css" />
    <link rel="stylesheet" href="./css/modal-add-aluno.css" />
    <link rel="stylesheet" href="./css/modal-edit-turma.css" />
    <link rel="stylesheet" href="./css/modal-edit-aluno.css" />
    <link rel="stylesheet" href="./css/modal-delete-turma.css" />
    <link rel="stylesheet" href="./css/modal-delete-aluno.css" />
    <link rel="stylesheet" href="./css/modal-details-aluno.css" />
    <link rel="stylesheet" href="./css/sidebar.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph Dashboard - <?php echo htmlspecialchars($cargo); ?></title>
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
            <img src="<?php echo $header_photo; ?>" alt="User" class="user-icon" id="header-photo">
        </div>
    </header>
    <!-- FIM HEADER -->

    <section class="main">
        <!-- SIDEBAR -->
        <div class="sidebar" id="sidebar">
            <a class="sidebar-active" href="#"><i class="fa-solid fa-house"></i>Home</a>
            <a href="#"><i class="fa-solid fa-chart-bar"></i>Relatórios</a>
            <a href="my_profile.php"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>

            <?php if ($cargo === "Coordenador" || $cargo === "Diretor"): ?>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor"): ?>
                    <a href="#" onclick="openAddFuncionarioModal()"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <?php endif; ?>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                </div>
            </div>
            <?php endif; ?>
            
            <a href="funcionarios.php"><i class="fa-solid fa-users"></i>Funcionários</a>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
            <div class="separator"></div><br>
        </div>
        <!-- FIM SIDEBAR -->

        <div class="content" id="content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Gerenciamento de Turmas e Alunos</span>
            </div>

            <?php if ($cargo === "Professor"): ?>
            <!-- Dashboard do Professor -->
            <div class="box-turmas">
                <?php
                $sql = "SELECT id, nome, ano FROM turmas WHERE professor_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $funcionario_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $turmas = [];
                while ($row = $result->fetch_assoc()) {
                    $turmas[] = $row;
                }
                $stmt->close();

                foreach ($turmas as $turma) {
                    $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $turma['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $quantidade = $result->fetch_assoc()['quantidade'];
                    $stmt->close();

                    echo "<div class='box-turmas-single' data-turma-id='{$turma['id']}'>";
                    echo "<h3>{$turma['nome']}</h3>";
                    echo "<p>{$quantidade} alunos</p>";
                    echo "</div>";
                }
                if (empty($turmas)) {
                    echo "<p>Nenhuma turma cadastrada.</p>";
                }
                ?>
            </div>

            <div class="tabela-turma-selecionada">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Data de Nascimento</th>
                            <th>Matrícula</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-alunos">
                        <!-- Dados dos alunos serão inseridos aqui -->
                    </tbody>
                </table>
            </div>

            <?php elseif ($cargo === "Coordenador"): ?>
            <!-- Dashboard do Coordenador -->
            <div class="box-turmas">
                <?php
                    $sql = "SELECT t.id, t.nome, t.ano, f.nome AS professor_nome, f.sobrenome 
                            FROM turmas t 
                            LEFT JOIN funcionarios f ON t.professor_id = f.id";
                    $result = $conn->query($sql);
                    $turmas = [];
                    while ($row = $result->fetch_assoc()) {
                        $turmas[] = $row;
                    }

                    foreach ($turmas as $turma) {
                        $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $turma['id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $quantidade = $result->fetch_assoc()['quantidade'];
                        $stmt->close();

                        echo "<div class='box-turmas-single' data-turma-id='{$turma['id']}'>";
                        echo "<h3>{$turma['nome']}</h3>";
                        echo "<p>Professor: " . ($turma['professor_nome'] ? htmlspecialchars($turma['professor_nome'] . " " . $turma['sobrenome']) : "Sem professor") . "</p>";
                        echo "<p>{$quantidade} alunos</p>";
                        echo "</div>";
                    }
                    if (empty($turmas)) {
                        echo "<p>Nenhuma turma cadastrada.</p>";
                    }
                ?>
            </div>
            <!-- Fim do Dashboard do Coordenador -->

            <div class="tabela-turma-selecionada">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Data de Nascimento</th>
                            <th>Matrícula</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-alunos">
                        <!-- Dados dos alunos serão inseridos aqui -->
                    </tbody>
                </table>
            </div>

            <?php elseif ($cargo === "Diretor"): ?>
            <!-- Dashboard do Diretor -->
            <!-- Visão Geral -->
            <div class="overview">
                <?php
                $sql = "SELECT COUNT(*) as total_turmas FROM turmas";
                $total_turmas = $conn->query($sql)->fetch_assoc()['total_turmas'];

                $sql = "SELECT COUNT(*) as total_alunos FROM alunos";
                $total_alunos = $conn->query($sql)->fetch_assoc()['total_alunos'];

                $sql = "SELECT COUNT(*) as total_professores FROM funcionarios WHERE cargo = 'Professor'";
                $total_professores = $conn->query($sql)->fetch_assoc()['total_professores'];

                $sql = "SELECT COUNT(*) as total_funcionarios FROM funcionarios";
                $total_funcionarios = $conn->query($sql)->fetch_assoc()['total_funcionarios'];
                ?>
                <div class="overview-box">
                    <h3><?php echo $total_turmas; ?></h3>
                    <p>Total de Turmas</p>
                </div>
                <div class="overview-box">
                    <h3 id="total-alunos"><?php echo $total_alunos; ?></h3>
                    <p>Total de Alunos</p>
                </div>
                <div class="overview-box">
                    <h3><?php echo $total_professores; ?></h3>
                    <p>Total de Professores</p>
                </div>
                <div class="overview-box">
                    <h3><?php echo $total_funcionarios; ?></h3>
                    <p>Total de Funcionários</p>
                </div>
            </div>

            <!-- Lista de Turmas -->
            <h3 class="section-title"><i class="fa-solid fa-users"></i> Turmas</h3>
            <div class="box-turmas">
                <?php
                    $sql = "SELECT t.id, t.nome, t.ano, f.nome AS professor_nome, f.sobrenome 
                        FROM turmas t 
                        LEFT JOIN funcionarios f ON t.professor_id = f.id";
                    $result = $conn->query($sql);
                    $turmas = [];
                    while ($row = $result->fetch_assoc()) {
                        $turmas[] = $row;
                    }

                    foreach ($turmas as $turma) {
                        $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $turma['id']);
                        $stmt->execute();
                        $quantidade = $stmt->get_result()->fetch_assoc()['quantidade'];
                        $stmt->close();

                        echo "<div class='box-turmas-single' data-turma-id='{$turma['id']}'>";
                        echo "<h3>{$turma['nome']}</h3>";
                        echo "<p>Professor: " . ($turma['professor_nome'] ? htmlspecialchars($turma['professor_nome'] . " " . $turma['sobrenome']) : "Sem professor") . "</p>";
                        echo "<p>{$quantidade} alunos</p>";
                        if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor") {
                            echo "<button class='action-btn edit-btn' title='Editar Turma' onclick='showEditTurmaModal({$turma['id']})'>";
                            echo "<i class='fa-solid fa-pen-to-square'></i>";
                            echo "</button>";
                        }
                        if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor") {
                            echo "<button class='action-btn delete-btn' title='Excluir Turma' onclick='showDeleteTurmaModal({$turma['id']})'>";
                            echo "<i class='fa-solid fa-trash'></i>";
                            echo "</button>";
                        }
                        echo "</div>";
                    }
                    if (empty($turmas)) {
                        echo "<p>Nenhuma turma cadastrada.</p>";
                    }
                ?>
            </div>

            <!-- Tabela de Alunos -->
            <div class="tabela-turma-selecionada">
                <h3>Alunos da Turma Selecionada</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Data de Nascimento</th>
                            <th>Matrícula</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-alunos">
                        <!-- Dados dos alunos serão inseridos aqui -->
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div><!-- FIM CONTENT -->
    </section><!-- FIM MAIN -->

    <!-- Modal de Confirmação de Exclusão (exclusivo do Diretor) -->
    <?php if ($cargo === "Diretor"): ?>
    <div id="modal-delete-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>
    
    <!-- Modal de Cadastro de Turma -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor"): ?>
    <div id="modal-cadastrar-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Modal de Exclusão de Turma -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor"): ?>
    <div id="modal-delete-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Modal de Edição de Turma -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor"): ?>
    <div id="modal-edit-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>
    
    <!-- Modal de Adição de Funcionário -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor"): ?>
    <div id="modal-add-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Modal de Cadastro de Aluno -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor"): ?>
    <div id="modal-add-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Modal de Edição de Aluno -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor"): ?>
    <div id="modal-edit-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Modal de Detalhes do Aluno (compartilhado entre cargos) -->
    <div id="modal-details-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/utils.js"></script>

    <script src="js/modal-add-funcionario.js"></script>
    <script src="js/modal-add-turma.js"></script>
    <script src="js/modal-add-aluno.js"></script>

    <script src="js/modal-edit-turma.js"></script>
    <script src="js/modal-edit-aluno.js"></script>

    <script src="js/modal-delete-turma.js"></script>
    <script src="js/modal-delete-aluno.js"></script>

    <script src="js/modal-details-aluno.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/ajax.js"></script>
</body>
</html>
<?php $conn->close(); ?>