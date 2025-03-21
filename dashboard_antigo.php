<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./css/global.css" rel="stylesheet" />
    <link href="./css/dashboard.css" rel="stylesheet" />
    <link href="./css/header.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />    <title>evoGraph Dashboard</title>
</head>

<body>
    <!-- Início Header -->
    <header>
        <div class="info-header">
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
    <!-- Fim Header -->

    <!-- Início Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor"): ?>
                <li><a href="cadastro_turma.php">Cadastrar Nova Turma</a></li>
                <li><a href="cadastro_funcionario.php">Cadastrar Funcionário</a></li>
                <li><a href="cadastro_aluno.php">Cadastrar Aluno</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Sair</a></li>
        </ul>
    </div>
    <!-- Fim Sidebar -->

    <!-- Início Main Content -->
    <div class="main-content" id="main-content">
        <button class="menu-toggle" id="menu-toggle"><i class="fa-solid fa-bars"></i></button>
        <div class="content-wrapper">
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
                    <h2>Meus Alunos</h2>
                    <table class="professores-turmas-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Matrícula</th>
                                <th>Turma</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT a.nome, a.sobrenome, a.matricula, t.nome AS turma_nome, t.ano 
                                    FROM alunos a 
                                    JOIN turmas t ON a.turma_id = t.id 
                                    WHERE t.professor_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $funcionario_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["turma_nome"] . " - Ano " . $row["ano"]) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>Nenhum aluno cadastrado.</td></tr>";
                            }
                            $stmt->close();
                            ?>
                        </tbody>
                    </table>

                <?php elseif ($_SESSION["cargo"] === "Coordenador"): ?>
                    <!-- Dashboard do Coordenador -->
                    <h2>Professores e Turmas</h2>
                    <table class="professores-turmas-table">
                        <thead>
                            <tr>
                                <th>Professor</th>
                                <th>RF</th>
                                <th>Turma 1</th>
                                <th>Turma 2</th>
                                <th>Turma 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id, nome, sobrenome, rf FROM funcionarios WHERE cargo = 'Professor'";
                            $func_result = $conn->query($sql);

                            if ($func_result->num_rows > 0) {
                                while ($func = $func_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td><a href='cadastro_funcionario.php?edit_id=" . $func["id"] . "'>" . htmlspecialchars($func["nome"] . " " . $func["sobrenome"]) . "</a></td>";
                                    echo "<td>" . htmlspecialchars($func["rf"]) . "</td>";
                                    
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
                                echo "<tr><td colspan='5'>Nenhum professor cadastrado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <h2>Alunos</h2>
                    <table class="professores-turmas-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Matrícula</th>
                                <th>Turma</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT a.id, a.nome, a.sobrenome, a.matricula, t.nome AS turma_nome, t.ano 
                                    FROM alunos a 
                                    JOIN turmas t ON a.turma_id = t.id";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td><a href='cadastro_aluno.php?edit_id=" . $row["id"] . "'>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</a></td>";
                                    echo "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["turma_nome"] . " - Ano " . $row["ano"]) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>Nenhum aluno cadastrado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                <?php elseif ($_SESSION["cargo"] === "Diretor"): ?>
                    <!-- Dashboard do Diretor -->
                    <h2>Funcionários e Turmas</h2>
                    <table class="professores-turmas-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>RF</th>
                                <th>Cargo</th>
                                <th>Turma 1</th>
                                <th>Turma 2</th>
                                <th>Turma 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id, nome, sobrenome, rf, cargo FROM funcionarios";
                            $func_result = $conn->query($sql);

                            if ($func_result->num_rows > 0) {
                                while ($func = $func_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td><a href='cadastro_funcionario.php?edit_id=" . $func["id"] . "'>" . htmlspecialchars($func["nome"] . " " . $func["sobrenome"]) . "</a></td>";
                                    echo "<td>" . htmlspecialchars($func["rf"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($func["cargo"]) . "</td>";

                                    if ($func["cargo"] === "Professor") {
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
                                    } else {
                                        echo "<td>-</td><td>-</td><td>-</td>";
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>Nenhum funcionário cadastrado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <h2>Alunos</h2>
                    <table class="professores-turmas-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Matrícula</th>
                                <th>Turma</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT a.id, a.nome, a.sobrenome, a.matricula, t.nome AS turma_nome, t.ano 
                                    FROM alunos a 
                                    JOIN turmas t ON a.turma_id = t.id";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td><a href='cadastro_aluno.php?edit_id=" . $row["id"] . "'>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</a></td>";
                                    echo "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["turma_nome"] . " - Ano " . $row["ano"]) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>Nenhum aluno cadastrado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Fim Main Content -->

    <script src="./js/script.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/js/all.min.js"></script>
    
</body>
</html>
<?php $conn->close(); ?>