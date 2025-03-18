<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.html");
    exit;
}

$conn = new mysqli("localhost", "admEvoGraph", "evoGraph123", "evograph_db");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$professor_id = $_SESSION["professor_id"];
$sql = "SELECT id, nome, ano FROM turmas WHERE professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();

// Cadastro de nova turma (apenas para coordenadores)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nova-turma"]) && $_SESSION["role"] === "coordenador") {
    $nome = $_POST["nome"];
    $ano = $_POST["ano"];
    $insertSql = "INSERT INTO turmas (nome, ano, professor_id) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("sii", $nome, $ano, $professor_id);
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
    <title>evoGraph Dashboard</title>
</head>
<body>
    <div class="container">
        <h1>Bem-vindo ao Dashboard do evoGraph, <?php echo htmlspecialchars($_SESSION["email"]); ?>!</h1>
        <h2>Suas Turmas</h2>
        <ul class="turmas-list">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<li><a href='turma.php?id=" . $row["id"] . "'>" . htmlspecialchars($row["nome"]) . " - Ano " . $row["ano"] . "</a></li>";
                }
            } else {
                echo "<li>Nenhuma turma cadastrada.</li>";
            }
            $stmt->close();
            ?>
        </ul>

        <?php if ($_SESSION["role"] === "coordenador"): ?>
            <!-- Formulário visível apenas para coordenadores -->
            <h3>Cadastrar Nova Turma</h3>
            <form method="POST" class="turma-form">
                <div class="form-group">
                    <label for="nome">Nome da Turma</label>
                    <input type="text" id="nome" name="nome" placeholder="Ex.: 5º Ano A" required>
                </div>
                <div class="form-group">
                    <label for="ano">Ano</label>
                    <input type="number" id="ano" name="ano" placeholder="Ex.: 5" required>
                </div>
                <button type="submit" name="nova-turma" class="login-button">Cadastrar</button>
            </form>
        <?php endif; ?>

        <a href="logout.php">Sair</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>