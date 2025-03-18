<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["cargo"] !== "Coordenador") {
    header("Location: index.html");
    exit;
}

$conn = new mysqli("localhost", "admEvoGraph", "evoGraph123", "evograph_db");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Processar cadastro de nova turma
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nova-turma"])) {
    $nome = $_POST["nome"];
    $ano = $_POST["ano"];
    $funcionario_id = $_POST["funcionario_id"];
    $insertSql = "INSERT INTO turmas (nome, ano, professor_id) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("sii", $nome, $ano, $funcionario_id);
    $insertStmt->execute();
    $insertStmt->close();
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./css/main.css" rel="stylesheet" />
    <title>evoGraph - Cadastrar Turma</title>
</head>
<body>
    <div class="container">
        <h2>Cadastrar Nova Turma</h2>
        <form method="POST" class="turma-form">
            <div class="form-group">
                <label for="nome">Nome da Turma</label>
                <input type="text" id="nome" name="nome" placeholder="Ex.: 5º Ano A" required>
            </div>
            <div class="form-group">
                <label for="ano">Ano</label>
                <input type="number" id="ano" name="ano" placeholder="Ex.: 5" required>
            </div>
            <div class="form-group">
                <label for="funcionario_id">Professor Responsável</label>
                <select id="funcionario_id" name="funcionario_id" required>
                    <?php
                    $func_result = $conn->query("SELECT id, nome, sobrenome FROM funcionarios WHERE cargo = 'Professor'");
                    while ($func = $func_result->fetch_assoc()) {
                        echo "<option value='" . $func["id"] . "'>" . htmlspecialchars($func["nome"] . " " . $func["sobrenome"]) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="nova-turma" class="login-button">Cadastrar</button>
            <a href="dashboard.php" class="cancel-button">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>