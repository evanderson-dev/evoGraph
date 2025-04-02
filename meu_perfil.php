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

        <!-- Seção de Conteúdo -->
        <div class="content" id="content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Gerenciamento de Funcionários</span>
            </div>

            <section class="meu-perfil">
                <div class="content" id="content">
                    <?php if (isset($success_message)): ?>
                        <p style="color: green;"><?php echo $success_message; ?></p>
                    <?php elseif (isset($error_message)): ?>
                        <p style="color: red;"><?php echo $error_message; ?></p>
                    <?php endif; ?>

                    <div class="profile-form">
                        <form method="POST" action="meu_perfil.php" id="profile-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nome">Nome:</label>
                                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" disabled required>
                                </div>
                                <div class="form-group">
                                    <label for="sobrenome">Sobrenome:</label>
                                    <input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($user['sobrenome']); ?>" disabled required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="email">E-mail:</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="rf">RF Funcionário:</label>
                                    <input type="text" id="rf" name="rf" value="<?php echo htmlspecialchars($user['rf'] ?? ''); ?>" disabled required>
                                </div>
                                <div class="form-group">
                                    <label for="data_nascimento">Data de Nascimento:</label>
                                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($user['data_nascimento'] ?? ''); ?>" disabled required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="cargo">Cargo:</label>
                                    <input type="text" id="cargo" name="cargo" value="<?php echo htmlspecialchars($user['cargo']); ?>" disabled readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="new_password">Nova Senha:</label>
                                    <input type="password" id="new_password" name="new_password" disabled>
                                </div>
                            </div>

                            <div class="form-buttons">
                                <button type="button" id="edit-btn" class="btn">Editar</button>
                                <button type="submit" id="save-btn" class="btn" disabled>Salvar</button>
                                <button type="button" id="cancel-btn" class="btn" disabled>Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <!-- Modal de Cadastro de Turma (exclusivo para Coordenador e Diretor) -->
    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor"): ?>
    <div id="modal-cadastrar-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Modal de Adição de Funcionário (exclusivo para Coordenador e Diretor) -->
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
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/utils.js"></script>
    <script src="js/modal-add-funcionario.js"></script>
    <script src="js/modal-add-turma.js"></script>
    <script src="js/modal-add-aluno.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/ajax.js"></script>
    <script>
        $(document).ready(function() {
            // Valores originais do formulário
            const originalValues = {
                nome: $('#nome').val(),
                sobrenome: $('#sobrenome').val(),
                email: $('#email').val(),
                rf: $('#rf').val(),
                data_nascimento: $('#data_nascimento').val(),
                new_password: $('#new_password').val()
            };

            // Botão Editar
            $('#edit-btn').on('click', function() {
                $('#profile-form input:not(#cargo)').prop('disabled', false);
                $('#save-btn, #cancel-btn').prop('disabled', false);
                $('#edit-btn').prop('disabled', true);
            });

            // Botão Cancelar
            $('#cancel-btn').on('click', function() {
                $('#nome').val(originalValues.nome);
                $('#sobrenome').val(originalValues.sobrenome);
                $('#email').val(originalValues.email);
                $('#rf').val(originalValues.rf);
                $('#data_nascimento').val(originalValues.data_nascimento);
                $('#new_password').val(originalValues.new_password);

                $('#profile-form input:not(#cargo)').prop('disabled', true);
                $('#save-btn, #cancel-btn').prop('disabled', true);
                $('#edit-btn').prop('disabled', false);
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>