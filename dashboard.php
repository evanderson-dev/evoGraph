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

// Processar cadastro de nova turma (apenas coordenadores)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nova-turma"]) && $_SESSION["role"] === "coordenador") {
    $nome = $_POST["nome"];
    $ano = $_POST["ano"];
    $professor_id = $_POST["professor_id"];
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

        <?php if ($_SESSION["role"] === "professor"): ?>
            <!-- Dashboard do Professor -->
            <h2>Suas Turmas</h2>
            <ul class="turmas-list">
                <?php
                $professor_id = $_SESSION["professor_id"];
                $sql = "SELECT id, nome, ano FROM turmas WHERE professor_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $professor_id);
                $stmt->execute();
                $result = $stmt->get_result();

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

        <?php elseif ($_SESSION["role"] === "coordenador"): ?>
            <!-- Dashboard do Coordenador -->
            <h2>Professores e Turmas</h2>
            <?php
            $sql = "SELECT id, email FROM professores WHERE role = 'professor'";
            $prof_result = $conn->query($sql);

            if ($prof_result->num_rows > 0) {
                while ($prof = $prof_result->fetch_assoc()) {
                    echo "<div class='professor-section'>";
                    echo "<h3>" . htmlspecialchars($prof["email"]) . "</h3>";
                    $sql = "SELECT id, nome, ano FROM turmas WHERE professor_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $prof["id"]);
                    $stmt->execute();
                    $turmas_result = $stmt->get_result();

                    if ($turmas_result->num_rows > 0) {
                        echo "<ul class='turmas-list'>";
                        while ($turma = $turmas_result->fetch_assoc()) {
                            echo "<li><a href='turma.php?id=" . $turma["id"] . "'>" . htmlspecialchars($turma["nome"]) . " - Ano " . $turma["ano"] . "</a></li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>Nenhuma turma associada.</p>";
                    }
                    $stmt->close();
                    echo "</div>";
                }
            } else {
                echo "<p>Nenhum professor cadastrado.</p>";
            }
            ?>

            <!-- Formulário de Cadastro de Turma -->
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
                <div class="form-group">
                    <label for="professor_id">Professor Responsável</label>
                    <select id="professor_id" name="professor_id" required>
                        <?php
                        $prof_result = $conn->query("SELECT id, email FROM professores WHERE role = 'professor'");
                        while ($prof = $prof_result->fetch_assoc()) {
                            echo "<option value='" . $prof["id"] . "'>" . htmlspecialchars($prof["email"]) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="nova-turma" class="login-button">Cadastrar</button>
            </form>
        <?php endif; ?>

        <a href="logout.php">Sair</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>