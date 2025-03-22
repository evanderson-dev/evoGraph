<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor")) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';

// Processar cadastro ou edição
$edit_mode = false;
$aluno_id = null;
$nome = $sobrenome = $data_nascimento = $matricula = $nome_pai = $nome_mae = $turma_id = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $sobrenome = trim($_POST["sobrenome"]);
    $data_nascimento = $_POST["data_nascimento"];
    $matricula = trim($_POST["matricula"]);
    $nome_pai = trim($_POST["nome_pai"]);
    $nome_mae = trim($_POST["nome_mae"]);
    $turma_id = $_POST["turma_id"];

    // Validações básicas
    if (empty($nome) || empty($sobrenome) || empty($data_nascimento) || empty($matricula) || empty($turma_id)) {
        $error_message = "Todos os campos obrigatórios devem ser preenchidos.";
    } else {
        // Verificar se a matrícula já existe
        $check_matricula_sql = "SELECT id FROM alunos WHERE matricula = ? AND id != ?";
        $check_stmt = $conn->prepare($check_matricula_sql);
        $check_id = isset($_POST["edit_id"]) ? $_POST["edit_id"] : 0;
        $check_stmt->bind_param("si", $matricula, $check_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message = "A matrícula já está em uso por outro aluno.";
        } else {
            if (isset($_POST["edit_id"]) && !empty($_POST["edit_id"])) {
                // Edição
                $aluno_id = $_POST["edit_id"];
                $sql = "UPDATE alunos SET nome = ?, sobrenome = ?, data_nascimento = ?, matricula = ?, nome_pai = ?, nome_mae = ?, turma_id = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssii", $nome, $sobrenome, $data_nascimento, $matricula, $nome_pai, $nome_mae, $turma_id, $aluno_id);
                if ($stmt->execute()) {
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error_message = "Erro ao atualizar aluno: " . $conn->error;
                }
                $stmt->close();
            } else {
                // Cadastro
                $sql = "INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, nome_pai, nome_mae, turma_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $nome, $sobrenome, $data_nascimento, $matricula, $nome_pai, $nome_mae, $turma_id);
                if ($stmt->execute()) {
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error_message = "Erro ao cadastrar aluno: " . $conn->error;
                }
                $stmt->close();
            }
        }
        $check_stmt->close();
    }
}

// Carregar dados para edição, se aplicável
if (isset($_GET["edit_id"])) {
    $edit_mode = true;
    $aluno_id = $_GET["edit_id"];
    $sql = "SELECT nome, sobrenome, data_nascimento, matricula, nome_pai, nome_mae, turma_id FROM alunos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $aluno_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nome = $row["nome"];
        $sobrenome = $row["sobrenome"];
        $data_nascimento = $row["data_nascimento"];
        $matricula = $row["matricula"];
        $nome_pai = $row["nome_pai"];
        $nome_mae = $row["nome_mae"];
        $turma_id = $row["turma_id"];
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
    <title>evoGraph - <?php echo $edit_mode ? "Editar" : "Cadastrar"; ?> Aluno</title>
</head>
<body>
    <div class="container">
        <h2><?php echo $edit_mode ? "Editar Aluno" : "Cadastrar Novo Aluno"; ?></h2>
        <form method="POST" class="turma-form">
            <div class="form-group">
                <label for="nome">Nome *</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
            </div>
            <div class="form-group">
                <label for="sobrenome">Sobrenome *</label>
                <input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($sobrenome); ?>" required>
            </div>
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento *</label>
                <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($data_nascimento); ?>" required>
            </div>
            <div class="form-group">
                <label for="matricula">Matrícula *</label>
                <input type="text" id="matricula" name="matricula" value="<?php echo htmlspecialchars($matricula); ?>" required>
            </div>
            <div class="form-group">
                <label for="nome_pai">Nome do Pai</label>
                <input type="text" id="nome_pai" name="nome_pai" value="<?php echo htmlspecialchars($nome_pai); ?>">
            </div>
            <div class="form-group">
                <label for="nome_mae">Nome da Mãe</label>
                <input type="text" id="nome_mae" name="nome_mae" value="<?php echo htmlspecialchars($nome_mae); ?>">
            </div>
            <div class="form-group">
                <label for="turma_id">Turma *</label>
                <select id="turma_id" name="turma_id" required>
                    <option value="">Selecione uma turma</option>
                    <?php
                    $turmas_result = $conn->query("SELECT id, nome, ano FROM turmas");
                    while ($turma = $turmas_result->fetch_assoc()) {
                        $selected = $turma_id == $turma["id"] ? "selected" : "";
                        echo "<option value='" . $turma["id"] . "' $selected>" . htmlspecialchars($turma["nome"]) . " - Ano " . $turma["ano"] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <?php if ($edit_mode): ?>
                <input type="hidden" name="edit_id" value="<?php echo $aluno_id; ?>">
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