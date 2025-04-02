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
$sql = "SELECT nome, sobrenome, email, rf, data_nascimento, cargo, foto FROM funcionarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $funcionario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $error_message = "Erro ao carregar dados do usuário.";
}

// Processar atualização do perfil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $sobrenome = trim($_POST["sobrenome"]);
    $email = trim($_POST["email"]);
    $rf = trim($_POST["rf"]);
    $data_nascimento = trim($_POST["data_nascimento"]);
    $new_password = trim($_POST["new_password"]);
    $current_password = trim($_POST["current_password"]);

    // Verificar senha atual se uma nova senha foi informada
    if (!empty($new_password)) {
        $sql = "SELECT senha FROM funcionarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $funcionario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stored_password = $result->fetch_assoc()['senha'];
        $stmt->close();

        if (!password_verify($current_password, $stored_password)) {
            $error_message = "Senha atual incorreta.";
        }
    }

    // Processar upload da foto
    $foto_path = $user['foto'];
    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == 0) {
        $target_dir = "./img/employee_photos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $foto_name = $rf . "_" . time() . "." . pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $target_file = $target_dir . $foto_name;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $foto_path = $target_file;
        } else {
            $error_message = "Erro ao fazer upload da foto.";
        }
    }

    if (!isset($error_message)) {
        $sql = "UPDATE funcionarios SET nome = ?, sobrenome = ?, email = ?, rf = ?, data_nascimento = ?, foto = ?";
        $params = [$nome, $sobrenome, $email, $rf, $data_nascimento, $foto_path];
        $types = "ssssss";

        if (!empty($new_password)) {
            $sql .= ", senha = ?";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            $types .= "s";
        }
        $sql .= " WHERE id = ?";
        $params[] = $funcionario_id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $success_message = "Perfil atualizado com sucesso!";
            $user = array_merge($user, [
                'nome' => $nome,
                'sobrenome' => $sobrenome,
                'email' => $email,
                'rf' => $rf,
                'data_nascimento' => $data_nascimento,
                'foto' => $foto_path
            ]);
        } else {
            $error_message = "Erro ao atualizar perfil: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="./css/my-profile.css" />
    <link rel="stylesheet" href="./css/modal-add-funcionario.css" />
    <link rel="stylesheet" href="./css/modal-add-turma.css" />
    <link rel="stylesheet" href="./css/modal-add-aluno.css" />
    <link rel="stylesheet" href="./css/sidebar.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Meu Perfil</title>
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
            <img src="<?php echo $user['foto'] ?? 'https://avatars.githubusercontent.com/u/94180306?s=40&v=4'; ?>" alt="User" class="user-icon">
        </div>
    </header>
    <!-- FIM HEADER -->

    <section class="main">
        <!-- SIDEBAR -->
        <div class="sidebar" id="sidebar">
            <a href="dashboard.php"><i class="fa-solid fa-house"></i>Home</a>
            <a href="#"><i class="fa-solid fa-chart-bar"></i>Relatórios</a>
            <a href="my_profile.php" class="sidebar-active"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>
            <?php if ($cargo === "Coordenador" || $cargo === "Diretor"): ?>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <a href="#" onclick="openAddFuncionarioModal()"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                </div>
            </div>
            <?php endif; ?>
            <a href="funcionarios.php"><i class="fa-solid fa-users"></i>Funcionários</a>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
            <div class="separator"></div><br>
        </div>
        <!-- FIM SIDEBAR -->

        <!-- Seção de Conteúdo -->
        <div class="content" id="content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Meu Perfil</span>
            </div>

            <section class="meu-perfil">
                <?php if (isset($success_message)): ?>
                    <p class="success-message"><?php echo $success_message; ?></p>
                <?php elseif (isset($error_message)): ?>
                    <p class="error-message"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <div class="profile-form">
                    <form method="POST" action="my_profile.php" id="profile-form" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group foto-placeholder">
                                <label>Foto do Perfil</label>
                                <div class="foto-box">
                                    <img id="profile-foto-preview" src="<?php echo $user['foto'] ?? 'img/default-photo.jpg'; ?>" alt="Foto do Perfil">
                                </div>
                                <input type="file" id="foto" name="foto" accept="image/*" disabled>
                            </div>
                            <div class="form-group info-right">
                                <label for="nome">Nome:</label>
                                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" disabled required>
                                <label for="sobrenome">Sobrenome:</label>
                                <input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($user['sobrenome']); ?>" disabled required>
                                <label for="rf">RF Funcionário:</label>
                                <input type="text" id="rf" name="rf" value="<?php echo htmlspecialchars($user['rf'] ?? ''); ?>" disabled required>
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
                                <label for="data_nascimento">Data de Nascimento:</label>
                                <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($user['data_nascimento'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="form-group">
                                <label for="cargo">Cargo:</label>
                                <input type="text" id="cargo" name="cargo" value="<?php echo htmlspecialchars($user['cargo']); ?>" disabled readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="current_password">Senha Atual:</label>
                                <input type="password" id="current_password" name="current_password" disabled>
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
            </section>
        </div>
    </section>

    <!-- Modals -->
    <?php if ($cargo === "Coordenador" || $cargo === "Diretor"): ?>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/utils.js"></script>
    <script src="js/modal-add-funcionario.js"></script>
    <script src="js/modal-add-turma.js"></script>
    <script src="js/modal-add-aluno.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/ajax.js"></script>
    <script src="js/my-profile.js"></script>
</body>
</html>
<?php $conn->close(); ?>