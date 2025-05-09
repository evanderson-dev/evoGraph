<?php
session_start();

// Definir os cargos permitidos para acessar a página
$allowed_cargos = ['Professor', 'Coordenador', 'Diretor', 'Administrador'];

// Verificar se o usuário está logado e tem um cargo permitido
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["cargo"]) || !in_array($_SESSION["cargo"], $allowed_cargos)) {
    header('Location: index.php');
    exit;
}

// Definir a variável $cargo para uso no HTML
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
    <link rel="stylesheet" href="./assets/css/relatorio-google.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-add-funcionario.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-add-turma.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-add-aluno.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Relatório Google</title>
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

        <div class="main-content" id="main-content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Formulário do Google Forms</span>
            </div>

            <section class="relatorio-section">
                <div id="message-box"></div>
                <div class="profile-form">
                    <form id="profile-form" enctype="multipart/form-data">
                        <input type="hidden" name="save_profile" value="1">

                        <div class="form-group">
                            <div>
                                <label for="googleSheetLink">Link da planilha do Google:</label>
                                <input type="text" id="googleSheetLink" placeholder="https://docs.google.com/spreadsheets/d/..." required>
                            </div>
                            <div>
                                <label for="bnccAno">Ano Escolar:</label>
                                <select id="bnccAno" required>
                                    <option value="">Selecione o ano</option>
                                </select>
                            </div>
                            <div>
                                <label for="bnccDisciplina">Disciplina:</label>
                                <select id="bnccDisciplina" disabled>
                                    <option value="">Selecione a disciplina</option>
                                </select>
                            </div>
                            <div>
                                <label for="bnccHabilidade">Habilidade BNCC (Opcional):</label>
                                <select id="bnccHabilidade" disabled>
                                    <option value="">Selecione a habilidade</option>
                                </select>
                            </div>
                            <div>
                                <label for="formularioId">Identificador do formulário:</label>
                                <input type="text" id="formularioId" placeholder="Identificador do formulário" required>
                            </div>
                            <div>
                                <label> </label>
                                <button type="button" class="btn-carregar" onclick="carregarPlanilha()">Carregar</button>
                            </div>
                            <div>
                                <label> </label>
                                <button type="button" class="btn-importar" onclick="importarParaBanco()">Importar para o banco</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div>
                                <label for="formularioIdDelete">Excluir formulário:</label>
                                <select id="formularioIdDelete">
                                    <option value="">Selecione um formulário</option>
                                    <?php
                                    require_once "db_connection.php";
                                    $query = "SELECT DISTINCT formulario_id FROM respostas_formulario WHERE funcionario_id = ? ORDER BY formulario_id";
                                    $stmt = $conn->prepare($query);
                                    $stmt->bind_param("i", $funcionario_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                        $form_id = htmlspecialchars($row['formulario_id']);
                                        echo "<option value=\"$form_id\">$form_id</option>";
                                    }
                                    $stmt->close();
                                    $conn->close();
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label> </label>
                                <button type="button" class="btn-excluir" onclick="excluirFormulario()">Excluir</button>
                            </div>
                        </div>

                        <div class="table-container">
                            <h4>Dados Carregados da Planilha</h4>
                            <div style="overflow-x: auto;">
                                <table id="tabela-dados">
                                    <thead></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </form>
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
    <?php endif; ?>

    <footer>
        <p>© 2025 evoGraph. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    <script src="./assets/js/utils.js"></script>
    <script src="./assets/js/modal-add-funcionario.js"></script>
    <script src="./assets/js/modal-add-turma.js"></script>
    <script src="./assets/js/modal-add-aluno.js"></script>
    <script src="./assets/js/sidebar.js"></script>
    <script src="./assets/js/ajax.js"></script>
    <script>
        // Passar o funcionarioId do PHP para o JavaScript
        const funcionarioId = <?php echo json_encode($_SESSION['funcionario_id'] ?? null); ?>;
    </script>
    <script src="./assets/js/relatorio-google.js"></script>
</body>
</html>