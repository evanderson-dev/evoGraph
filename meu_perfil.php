<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.html");
    exit;
}

require_once 'db_connection.php';

$funcionario_id = $_SESSION["funcionario_id"];
$cargo = $_SESSION["cargo"];

// Buscar dados atuais do usuário
$sql = "SELECT nome, sobrenome, email, rf, data_nascimento, cargo FROM funcionarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $funcionario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Atualizar perfil se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $sobrenome = trim($_POST["sobrenome"]);
    $email = trim($_POST["email"]);
    $rf = trim($_POST["rf"]);
    $data_nascimento = trim($_POST["data_nascimento"]);
    $new_password = trim($_POST["new_password"]);

    $sql = "UPDATE funcionarios SET nome = ?, sobrenome = ?, email = ?, rf = ?, data_nascimento = ?" . (!empty($new_password) ? ", senha = ?" : "") . " WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bind_param("ssssssi", $nome, $sobrenome, $email, $rf, $data_nascimento, $hashed_password, $funcionario_id);
    } else {
        $stmt->bind_param("sssssi", $nome, $sobrenome, $email, $rf, $data_nascimento, $funcionario_id);
    }

    if ($stmt->execute()) {
        $success_message = "Perfil atualizado com sucesso!";
        $user['nome'] = $nome;
        $user['sobrenome'] = $sobrenome;
        $user['email'] = $email;
        $user['rf'] = $rf;
        $user['data_nascimento'] = $data_nascimento;
    } else {
        $error_message = "Erro ao atualizar perfil: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Meu Perfil</title>
</head>
<body>
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

    <section class="main">
        <div class="sidebar" id="sidebar">
            <a href="dashboard.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="#"><i class="fa-solid fa-chart-bar"></i> Relatórios</a>
            <a class="sidebar-active" href="meu_perfil.php"><i class="fa-solid fa-user-gear"></i> Meu Perfil</a>
            <?php if ($cargo === "Coordenador" || $cargo === "Diretor"): ?>
                <a href="cadastro_turma.php"><i class="fa-solid fa-plus"></i> Cadastrar Turma</a>
                <a href="cadastro_funcionario.php"><i class="fa-solid fa-user-plus"></i> Cadastrar Funcionário</a>
                <a href="cadastro_aluno.php"><i class="fa-solid fa-graduation-cap"></i> Cadastrar Aluno</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i> Sair</a>
            <div class="separator"></div><br>
        </div>

        <div class="content" id="content">
            <div class="titulo-secao">
                <h2>Meu Perfil</h2><br>
                <div class="separator"></div><br>
                <p><i class="fa-solid fa-house"></i> / Meu Perfil</p>
            </div>

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

            // Verificar estado salvo da sidebar
            if (localStorage.getItem('sidebarActive') === 'true') {
                $('#sidebar').addClass('active');
                $('#content').addClass('shifted');
            }

            // Menu hambúrguer
            $('#menu-toggle').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('shifted');
                // Salvar estado no localStorage
                localStorage.setItem('sidebarActive', $('#sidebar').hasClass('active'));
            });

            // Botão Editar
            $('#edit-btn').on('click', function() {
                $('#profile-form input:not(#cargo)').prop('disabled', false);
                $('#save-btn, #cancel-btn').prop('disabled', false);
                $('#edit-btn').prop('disabled', true);
            });

            // Botão Cancelar
            $('#cancel-btn').on('click', function() {
                // Restaurar valores originais
                $('#nome').val(originalValues.nome);
                $('#sobrenome').val(originalValues.sobrenome);
                $('#email').val(originalValues.email);
                $('#rf').val(originalValues.rf);
                $('#data_nascimento').val(originalValues.data_nascimento);
                $('#new_password').val(originalValues.new_password);

                // Bloquear campos e botões
                $('#profile-form input:not(#cargo)').prop('disabled', true);
                $('#save-btn, #cancel-btn').prop('disabled', true);
                $('#edit-btn').prop('disabled', false);
            });
        });
    </script>
</body>
</html>