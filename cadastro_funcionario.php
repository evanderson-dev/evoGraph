<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["cargo"] !== "Coordenador") {
    header("Location: index.html");
    exit;
}

require_once 'db_connection.php';

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
    } elseif (isset($_POST["edit_id"]) && !empty($_POST["edit_id"])) {
        // Edição
        $funcionario_id = $_POST["edit_id"];
        $sql = "UPDATE funcionarios SET nome = ?, sobrenome = ?, email = ?, data_nascimento = ?, rf = ?, cargo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
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
    } else {
        // Cadastro
        $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO funcionarios (nome, sobrenome, email, senha, data_nascimento, rf, cargo) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
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

// Carregar dados para edição, se aplicável
if (isset($_GET["edit_id"])) {
    $edit_mode = true;
    $funcionario_id = $_GET["edit_id"];
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
    <link href="./css/main.css" rel="stylesheet" />
    <title>evoGraph - <?php echo $edit_mode ? "Editar" : "Cadastrar"; ?> Funcionário</title>
</head>
<body>
    <div class="container">
        <h2><?php echo $edit_mode ? "Editar Funcionário" : "Cadastrar Novo Funcionário"; ?></h2>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
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
                    <!-- Adicione outros cargos aqui, se necessário -->
                </select>
            </div>
            <?php if ($edit_mode): ?>
                <input type="hidden" name="edit_id" value="<?php echo $funcionario_id; ?>">
            <?php endif; ?>
            <button type="submit" class="login-button"><?php echo $edit_mode ? "Salvar" : "Cadastrar"; ?></button>
            <a href="dashboard.php" class="cancel-button">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>