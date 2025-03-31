<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';

$funcionario_id = $_SESSION["funcionario_id"];
$cargo = $_SESSION["cargo"];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="./css/modal-add-turma.css" />
    <link rel="stylesheet" href="./css/modal-details.css" />
    <link rel="stylesheet" href="./css/modal-delete.css" />
    <link rel="stylesheet" href="./css/modal-edit.css" />
    <link rel="stylesheet" href="./css/modal-add.css" />
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
            <i class="fa-solid fa-user"></i>
            <img src="https://avatars.githubusercontent.com/u/94180306?s=40&v=4" alt="User" class="user-icon">
        </div>
    </header>
    <!-- FIM HEADER -->

    <section class="main">
        <!-- SIDEBAR -->
        <div class="sidebar" id="sidebar">
            <a class="sidebar-active" href="#"><i class="fa-solid fa-house"></i>Home</a>
            <a href="#"><i class="fa-solid fa-chart-bar"></i>Relatórios</a>
            <a href="meu_perfil.php"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>

            <?php if ($cargo === "Coordenador" || $cargo === "Diretor"): ?>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <a href="cadastro_funcionario.php"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                </div>
            </div>
            <?php endif; ?>

            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
            <div class="separator"></div><br>
        </div>
        <!-- FIM SIDEBAR -->

        <div class="content" id="content">
            <div class="titulo-secao">
                <h2>Dashboard <?php echo htmlspecialchars($cargo); ?></h2><br>
                <div class="separator"></div><br>
                <p><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ <?php echo htmlspecialchars($cargo === "Professor" ? "Minhas Turmas" : "Gerenciamento"); ?></p>
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
                        echo "<h3>{$turma['nome']} ({$turma['ano']})</h3>";
                        echo "<p>Professor: " . ($turma['professor_nome'] ? htmlspecialchars($turma['professor_nome'] . " " . $turma['sobrenome']) : "Sem professor") . "</p>";
                        echo "<p>{$quantidade} alunos</p>";
                        echo "<p><i class='fa-solid fa-trash'></i></p>";
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

    <!-- Modal de Detalhes do Aluno (compartilhado entre cargos) -->
    <div id="modal-detalhes-aluno" class="modal">
        <div class="modal-content">
            <h2>Detalhes do Aluno</h2>
            <div class="cadastro-form detalhes-form">
                <div class="form-row">
                    <div class="form-group foto-placeholder">
                        <label>Foto do Aluno</label>
                        <div class="foto-box">
                            <img id="detalhes-foto" src="img/default-photo.jpg" alt="Foto do Aluno">
                        </div>
                    </div>
                    <div class="form-group info-right">
                        <label for="detalhes-nome">Nome:</label>
                        <input type="text" id="detalhes-nome" readonly>
                        <label for="detalhes-matricula">Matrícula:</label>
                        <input type="text" id="detalhes-matricula" readonly>
                        <label for="detalhes-turma">Turma:</label>
                        <input type="text" id="detalhes-turma" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="detalhes-nascimento">Data de Nascimento:</label>
                        <input type="text" id="detalhes-nascimento" readonly>
                    </div>
                    <div class="form-group">
                        <label for="detalhes-data-matricula">Data de Matrícula:</label>
                        <input type="text" id="detalhes-data-matricula" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="detalhes-pai">Nome do Pai:</label>
                        <input type="text" id="detalhes-pai" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="detalhes-mae">Nome da Mãe:</label>
                        <input type="text" id="detalhes-mae" readonly>
                    </div>
                </div>
                <div class="form-buttons">
                    <button class="btn close-modal-btn">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão (exclusivo do Diretor) -->
    <?php if ($cargo === "Diretor"): ?>
    <div id="modal-confirm-delete" class="modal">
        <div class="modal-content">
            <h2>Confirmar Exclusão</h2>
            <p>Tem certeza que deseja excluir o aluno com matrícula <span id="delete-matricula"></span>?</p>
            <div class="modal-buttons">
                <button id="confirm-delete-btn" class="btn">Sim</button>
                <button id="cancel-delete-btn" class="btn">Não</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal de Edição de Aluno (exclusivo para Diretor e Coordenador) -->
    <?php if ($cargo === "Diretor" || $cargo === "Coordenador"): ?>
    <div id="modal-editar-aluno" class="modal">
        <div class="modal-content">
            <h2>Editar Aluno</h2>
            <div class="cadastro-form">
                <form method="POST" id="editar-aluno-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-nome">Nome:</label>
                            <input type="text" id="edit-nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-sobrenome">Sobrenome:</label>
                            <input type="text" id="edit-sobrenome" name="sobrenome" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-data_nascimento">Data de Nascimento:</label>
                            <input type="date" id="edit-data_nascimento" name="data_nascimento" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-matricula">Matrícula:</label>
                            <input type="text" id="edit-matricula" name="matricula" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="edit-nome_pai">Nome do Pai (opcional):</label>
                            <input type="text" id="edit-nome_pai" name="nome_pai">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="edit-nome_mae">Nome da Mãe (opcional):</label>
                            <input type="text" id="edit-nome_mae" name="nome_mae">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="edit-turma_id">Turma:</label>
                            <select id="edit-turma_id" name="turma_id" required>
                                <option value="">Selecione uma turma</option>
                                <?php
                                $sql = "SELECT id, nome FROM turmas";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>" . htmlspecialchars($row['nome']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" id="edit-data_matricula_hidden" name="data_matricula_hidden">

                    <div class="form-buttons">
                        <button type="submit" class="btn">Salvar</button>
                        <button type="button" class="btn close-modal-btn">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!--  Modal de Cadastro de Aluno (exclusivo para Coordenador e Diretor) -->
    <?php if ($cargo === "Coordenador" || $cargo === "Diretor"): ?>
    <div id="modal-cadastrar-aluno" class="modal" style="display: none;">
        <div class="modal-content">
            <h2 class="modal-title">Cadastrar Aluno</h2>
            <form id="cadastro-aluno-form" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="add-nome">Nome:</label>
                        <input type="text" id="add-nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="add-sobrenome">Sobrenome:</label>
                        <input type="text" id="add-sobrenome" name="sobrenome" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add-data_nascimento">Data de Nascimento:</label>
                        <input type="date" id="add-data_nascimento" name="data_nascimento" required>
                    </div>
                    <div class="form-group">
                        <label for="add-matricula">Matrícula:</label>
                        <input type="text" id="add-matricula" name="matricula" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add-foto">Foto (opcional):</label>
                        <input type="file" id="add-foto" name="foto" accept="image/*">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="add-nome_pai">Nome do Pai (opcional):</label>
                        <input type="text" id="add-nome_pai" name="nome_pai">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="add-nome_mae">Nome da Mãe (opcional):</label>
                        <input type="text" id="add-nome_mae" name="nome_mae">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="add-turma_id">Turma:</label>
                        <select id="add-turma_id" name="turma_id" required>
                            <option value="">Selecione uma turma</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" id="add-data_matricula_hidden" name="data_matricula_hidden">
                <div class="modal-buttons">
                    <button type="submit" class="btn">Cadastrar</button>
                    <button type="button" class="btn close-modal-btn">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    <!-- Fim do Modal de Cadastro de Aluno -->

    <!-- Modal de Cadastro de Turma (exclusivo para Coordenador e Diretor) -->
    <?php if ($cargo === "Coordenador" || $cargo === "Diretor"): ?>
    <div id="modal-cadastrar-turma" class="modal" style="display: none;">
        <div class="modal-content">
            <h2 class="modal-title">Cadastrar Turma</h2>
            <form id="cadastro-turma-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="add-turma-nome">Nome da Turma:</label>
                        <input type="text" id="add-turma-nome" name="nome" placeholder="Ex.: 5º Ano A" required>
                    </div>
                    <div class="form-group">
                        <label for="add-turma-ano">Ano:</label>
                        <input type="number" id="add-turma-ano" name="ano" placeholder="Ex.: 5" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="add-professor-id">Professor Responsável:</label>
                        <select id="add-professor-id" name="professor_id" required>
                            <option value="">Selecione um professor</option>
                        </select>
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn">Cadastrar</button>
                    <button type="button" class="btn close-modal-btn">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/utils.js"></script>
    <script src="js/modal-add-turma.js"></script>
    <script src="js/modal-details.js"></script>
    <script src="js/modal-delete.js"></script>
    <script src="js/modal-edit.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/modal-add.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/ajax.js"></script>
</body>
</html>
<?php $conn->close(); ?>