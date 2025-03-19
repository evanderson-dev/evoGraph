<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor")) {
    header("Location: index.html");
    exit;
}

require_once 'db_connection.php';

// Buscar nome e sobrenome do usuário logado
$sql = "SELECT nome, sobrenome FROM funcionarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["funcionario_id"]);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Processar cadastro ou edição
$edit_mode = false;
$funcionario_id = null;
$nome = $sobrenome = $email = $data_nascimento = $rf = $cargo = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $sobrenome = trim($_POST["sobrenome"]);
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);
    $data_nascimento = $_POST["data_nascimento"];
    $rf = trim($_POST["rf"]);
    $cargo = $_POST["cargo"];

    // Validações básicas
    if (empty($nome) || empty($sobrenome) || empty($email) || empty($data_nascimento) || empty($rf) || empty($cargo)) {
        $error_message = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "E-mail inválido.";
    } else {
        // Verificar se o RF já existe
        $check_rf_sql = "SELECT id FROM funcionarios WHERE rf = ? AND id != ?";
        $check_rf_stmt = $conn->prepare($check_rf_sql);
        if ($check_rf_stmt === false) {
            $error_message = "Erro na preparação da consulta de RF: " . $conn->error;
        } else {
            $check_id = isset($_POST["edit_id"]) ? (int)$_POST["edit_id"] : 0; // 0 para novo cadastro
            $check_rf_stmt->bind_param("si", $rf, $check_id);
            $check_rf_stmt->execute();
            $check_rf_result = $check_rf_stmt->get_result();

            // Verificar se o e-mail já existe
            $check_email_sql = "SELECT id FROM funcionarios WHERE email = ? AND id != ?";
            $check_email_stmt = $conn->prepare($check_email_sql);
            if ($check_email_stmt === false) {
                $error_message = "Erro na preparação da consulta de e-mail: " . $conn->error;
            } else {
                $check_email_stmt->bind_param("si", $email, $check_id);
                $check_email_stmt->execute();
                $check_email_result = $check_email_stmt->get_result();

                if ($check_rf_result->num_rows > 0) {
                    $error_message = "O Registro Funcional (RF) já está em uso por outro funcionário.";
                } elseif ($check_email_result->num_rows > 0) {
                    $error_message = "O e-mail já está em uso por outro funcionário.";
                } else {
                    if (isset($_POST["edit_id"]) && !empty($_POST["edit_id"])) {
                        // Edição
                        $funcionario_id = (int)$_POST["edit_id"];
                        $sql = "UPDATE funcionarios SET nome = ?, sobrenome = ?, email = ?, data_nascimento = ?, rf = ?, cargo = ? WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if ($stmt === false) {
                            $error_message = "Erro na preparação da atualização: " . $conn->error;
                        } else {
                            $stmt->bind_param("ssssssi", $nome, $sobrenome, $email, $data_nascimento, $rf, $cargo, $funcionario_id);
                            if ($stmt->execute()) {
                                if (!empty($senha)) {
                                    $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
                                    $sql_senha = "UPDATE funcionarios SET senha = ? WHERE id = ?";
                                    $stmt_senha = $conn->prepare($sql_senha);
                                    $stmt_senha->bind_param("si", $hashed_senha, $funcionario_id);
                                    $stmt_senha->execute();
                                    $stmt_senha->close();
                                }
                                header("Location: dashboard.php");
                                exit;
                            } else {
                                $error_message = "Erro ao atualizar funcionário: " . $conn->error;
                            }
                            $stmt->close();
                        }
                    } else {
                        // Cadastro
                        if (empty($senha)) {
                            $error_message = "A senha é obrigatória para novos funcionários.";
                        } else {
                            $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
                            $sql = "INSERT INTO funcionarios (nome, sobrenome, email, senha, data_nascimento, rf, cargo) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            if ($stmt === false) {
                                $error_message = "Erro na preparação do cadastro: " . $conn->error;
                            } else {
                                $stmt->bind_param("sssssss", $nome, $sobrenome, $email, $hashed_senha, $data_nascimento, $rf, $cargo);
                                if ($stmt->execute()) {
                                    header("Location: dashboard.php");
                                    exit;
                                } else {
                                    $error_message = "Erro ao cadastrar funcionário: " . $conn->error;
                                }
                                $stmt->close();
                            }
                        }
                    }
                }
                $check_email_stmt->close();
            }
            $check_rf_stmt->close();
        }
    }
}

// Carregar dados para edição, se aplicável
if (isset($_GET["edit_id"])) {
    $edit_mode = true;
    $funcionario_id = (int)$_GET["edit_id"];
    $sql = "SELECT nome, sobrenome, email, data_nascimento, rf, cargo FROM funcionarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $funcionario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nome = $row["nome"];
        $sobrenome = $row["sobrenome"];
        $email = $row["email"];
        $data_nascimento = $row["data_nascimento"];
        $rf = $row["rf"];
        $cargo = $row["cargo"];
    } else {
        header("Location: dashboard.php");
        exit;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./css/global.css" rel="stylesheet" />
    <link href="./css/cadastro.css" rel="stylesheet" />
    <title>evoGraph - <?php echo $edit_mode ? "Editar" : "Cadastrar"; ?> Funcionário</title>
</head>
<body>
    <div class="user-info">
        Logado como: <?php echo htmlspecialchars($usuario["nome"] . " " . $usuario["sobrenome"] . " (" . $_SESSION["cargo"] . ")"); ?>
    </div>
    <div class="container">
        <h2><?php echo $edit_mode ? "Editar Funcionário" : "Cadastrar Novo Funcionário"; ?></h2>
        <form method="POST" class="turma-form">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
            </div>
            <div class="form-group">
                <label for="sobrenome">Sobrenome</label>
                <input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($sobrenome); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha <?php echo $edit_mode ? "(deixe em branco para manter)" : ""; ?></label>
                <input type="password" id="senha" name="senha" <?php echo !$edit_mode ? "required" : ""; ?>>
            </div>
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($data_nascimento); ?>" required>
            </div>
            <div class="form-group">
                <label for="rf">Registro Funcional (RF)</label>
                <input type="text" id="rf" name="rf" value="<?php echo htmlspecialchars($rf); ?>" required>
            </div>
            <div class="form-group">
                <label for="cargo">Cargo</label>
                <select id="cargo" name="cargo" required>
                    <option value="Professor" <?php echo $cargo === "Professor" ? "selected" : ""; ?>>Professor</option>
                    <option value="Coordenador" <?php echo $cargo === "Coordenador" ? "selected" : ""; ?>>Coordenador</option>
                </select>
            </div>
            <?php if ($edit_mode): ?>
                <input type="hidden" name="edit_id" value="<?php echo $funcionario_id; ?>">
            <?php endif; ?>
            <button type="submit" class="login-button"><?php echo $edit_mode ? "Salvar" : "Cadastrar"; ?></button>
            <a href="dashboard.php" class="cancel-button">Cancelar</a>
        </form>
    </div>

    <?php if (!empty($error_message)): ?>
        <script>
            alert("<?php echo htmlspecialchars($error_message); ?>");
        </script>
    <?php endif; ?>
</body>
</html>
<?php $conn->close(); ?>