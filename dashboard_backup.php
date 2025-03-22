<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';

// Buscar turmas do professor logado
$funcionario_id = $_SESSION["funcionario_id"];
$sql = "SELECT id, nome, ano FROM turmas WHERE professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $funcionario_id);
$stmt->execute();
$result = $stmt->get_result();
$turmas = [];
while ($row = $result->fetch_assoc()) {
    $turmas[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph Dashboard - Professor</title>
</head>
<body>
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
    </header><!-- FIM HEADER -->

    <section class="main">
        <div class="sidebar">
            <a class="sidebar-active" href="#"><i class="fa-solid fa-house"></i> Home</a>
            <a href="#"><i class="fa-solid fa-chart-bar"></i> Relatórios</a>
            <a href="#"><i class="fa-solid fa-cog"></i> Configurações</a>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i> Sair</a>
            <div class="separator"></div><br>
        </div><!-- FIM SIDEBAR -->

        <div class="content">
            <div class="titulo-secao">
                <h2>Dashboard Professor</h2><br>
                <div class="separator"></div><br>
                <p><i class="fa-solid fa-house"></i> / Minhas Turmas</p>
            </div>

            <div class="box-turmas">
                <?php
                foreach ($turmas as $turma) {
                    // Contar quantidade de alunos na turma
                    $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $turma['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $quantidade = $result->fetch_assoc()['quantidade'];
                    $stmt->close();

                    echo "<div class='box-turmas-single' data-turma-id='{$turma['id']}'>";
                    echo "<h3>{$turma['nome']}</h3>";
                    echo "<p>{$quantidade} alunos</p>";
                    echo "</div>";
                }
                if (empty($turmas)) {
                    echo "<p>Nenhuma turma cadastrada.</p>";
                }
                ?>
            </div>

            <div class="tabela-turma-selecionada">
                <!-- Tabela será preenchida dinamicamente via JavaScript -->
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Matrícula</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-alunos">
                        <!-- Dados dos alunos serão inseridos aqui -->
                    </tbody>
                </table>
            </div>
        </div><!-- FIM CONTENT -->
    </section><!-- FIM MAIN -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.box-turmas-single').click(function() {
                var turmaId = $(this).data('turma-id');

                // Requisição AJAX para buscar alunos da turma
                $.ajax({
                    url: 'fetch_alunos.php',
                    method: 'POST',
                    data: { turma_id: turmaId },
                    success: function(response) {
                        $('#tabela-alunos').html(response);
                    },
                    error: function() {
                        $('#tabela-alunos').html('<tr><td colspan="2">Erro ao carregar alunos.</td></tr>');
                    }
                });
            });

            // Simular clique na primeira turma ao carregar a página (opcional)
            if ($('.box-turmas-single').length > 0) {
                $('.box-turmas-single').first().click();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>