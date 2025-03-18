<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.html");
    exit;
}

// Conectar ao MySQL
$conn = new mysqli("localhost", "admEvoGraph", "evoGraph123", "evograph_db");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Pegar o ID da turma da URL
if (!isset($_GET["id"])) {
    header("Location: dashboard.php");
    exit;
}
$turma_id = $_GET["id"];

// Buscar detalhes da turma
$sql = "SELECT nome, ano FROM turmas WHERE id = ? AND professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $turma_id, $_SESSION["professor_id"]);
$stmt->execute();
$turma_result = $stmt->get_result();
$turma = $turma_result->fetch_assoc();
$stmt->close();

if (!$turma) {
    header("Location: dashboard.php");
    exit;
}

// Buscar alunos da turma
$sql = "SELECT nome FROM alunos WHERE turma_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $turma_id);
$stmt->execute();
$alunos_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./css/main.css" rel="stylesheet" />
    <title>evoGraph - <?php echo htmlspecialchars($turma["nome"]); ?></title>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($turma["nome"]) . " - Ano " . $turma["ano"]; ?></h1>
        <h2>Alunos</h2>
        <ul class="alunos-list">
            <?php
            if ($alunos_result->num_rows > 0) {
                while ($row = $alunos_result->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($row["nome"]) . "</li>";
                }
            } else {
                echo "<li>Nenhum aluno cadastrado.</li>";
            }
            $stmt->close();
            $conn->close();
            ?>
        </ul>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </div>
</body>
</html>