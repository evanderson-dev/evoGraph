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
    <div class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <?php if ($_SESSION["cargo"] === "Coordenador"): ?>
                <li><a href="cadastro_turma.php">Cadastrar Nova Turma</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Sair</a></li>
        </ul>
    </div>
    <div class="main-content">
        <button class="menu-toggle" id="menu-toggle">☰</button>
        <div class="container">
            <?php if ($_SESSION["cargo"] === "Professor"): ?>
                <!-- Dashboard do Professor -->
                <h2>Minhas Turmas</h2>
                <ul class="turmas-list">
                    <?php
                    $funcionario_id = $_SESSION["funcionario_id"];
                    $sql = "SELECT id, nome, ano FROM turmas WHERE professor_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $funcionario_id);
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

            <?php elseif ($_SESSION["cargo"] === "Coordenador"): ?>
                <!-- Dashboard do Coordenador -->
                <h2>Funcionários e Turmas</h2>
                <table class="professores-turmas-table">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Turma 1</th>
                            <th>Turma 2</th>
                            <th>Turma 3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT id, nome, sobrenome FROM funcionarios WHERE cargo = 'Professor'";
                        $func_result = $conn->query($sql);

                        if ($func_result->num_rows > 0) {
                            while ($func = $func_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($func["nome"] . " " . $func["sobrenome"]) . "</td>";
                                
                                $sql = "SELECT id, nome, ano FROM turmas WHERE professor_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $func["id"]);
                                $stmt->execute();
                                $turmas_result = $stmt->get_result();
                                $turmas = [];
                                while ($turma = $turmas_result->fetch_assoc()) {
                                    $turmas[] = "<a href='turma.php?id=" . $turma["id"] . "'>" . htmlspecialchars($turma["nome"]) . " - Ano " . $turma["ano"] . "</a>";
                                }
                                $stmt->close();

                                for ($i = 0; $i < 3; $i++) {
                                    echo "<td>";
                                    if (isset($turmas[$i])) {
                                        echo $turmas[$i];
                                    } else {
                                        echo "-";
                                    }
                                    echo "</td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>Nenhum professor cadastrado.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <script src="./js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>