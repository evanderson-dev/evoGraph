<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';

$funcionario_id = $_SESSION["funcionario_id"];
$cargo = $_SESSION["cargo"];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph Dashboard - <?php echo htmlspecialchars($cargo); ?></title>
</head>
<body>
    <header>
        <div class="info-header">
            <button class="menu-toggle" id="menu-toggle"><i class="fa-solid fa-bars"></i></button>
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
        <div class="sidebar" id="sidebar">
            <a class="sidebar-active" href="#"><i class="fa-solid fa-house"></i> Home</a>
            <a href="#"><i class="fa-solid fa-chart-bar"></i> Relatórios</a>
            <a href="meu_perfil.php"><i class="fa-solid fa-user-gear"></i> Meu Perfil</a> <!-- Alterado aqui -->
            <?php if ($cargo === "Coordenador" || $cargo === "Diretor"): ?>
                <a href="cadastro_turma.php"><i class="fa-solid fa-plus"></i> Cadastrar Turma</a>
                <a href="cadastro_funcionario.php"><i class="fa-solid fa-user-plus"></i> Cadastrar Funcionário</a>
                <a href="cadastro_aluno.php"><i class="fa-solid fa-graduation-cap"></i> Cadastrar Aluno</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i> Sair</a>
            <div class="separator"></div><br>
        </div><!-- FIM SIDEBAR -->

        <div class="content" id="content">
            <div class="titulo-secao">
                <h2>Dashboard <?php echo htmlspecialchars($cargo); ?></h2><br>
                <div class="separator"></div><br>
                <p><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ <?php echo htmlspecialchars($cargo === "Professor" ? "Minhas Turmas" : "Gerenciamento"); ?></p>
            </div>

            <?php if ($cargo === "Professor"): ?>
                <!-- Dashboard do Professor -->
                <div class="box-turmas">
                    <?php
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

                    foreach ($turmas as $turma) {
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
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Data de Nascimento</th>
                                <th>Matrícula</th>
                                <th>Data de Matrícula</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-alunos">
                            <!-- Dados dos alunos serão inseridos aqui -->
                        </tbody>
                    </table>
                </div>

            <?php elseif ($cargo === "Coordenador"): ?>
                <!-- Dashboard do Coordenador -->
                <div class="box-turmas">
                    <?php
                    $sql = "SELECT t.id, t.nome, t.ano, f.nome AS professor_nome, f.sobrenome 
                            FROM turmas t 
                            LEFT JOIN funcionarios f ON t.professor_id = f.id";
                    $result = $conn->query($sql);
                    $turmas = [];
                    while ($row = $result->fetch_assoc()) {
                        $turmas[] = $row;
                    }

                    foreach ($turmas as $turma) {
                        $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $turma['id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $quantidade = $result->fetch_assoc()['quantidade'];
                        $stmt->close();

                        echo "<div class='box-turmas-single' data-turma-id='{$turma['id']}'>";
                        echo "<h3>{$turma['nome']}</h3>";
                        echo "<p>Professor: " . ($turma['professor_nome'] ? htmlspecialchars($turma['professor_nome'] . " " . $turma['sobrenome']) : "Sem professor") . "</p>";
                        echo "<p>{$quantidade} alunos</p>";
                        echo "</div>";
                    }
                    if (empty($turmas)) {
                        echo "<p>Nenhuma turma cadastrada.</p>";
                    }
                    ?>
                </div>

                <div class="tabela-turma-selecionada">
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

                <?php elseif ($cargo === "Diretor"): ?>
                    <!-- Dashboard do Diretor -->
                    <!-- Visão Geral -->
                    <div class="overview">
                        <?php
                        $sql = "SELECT COUNT(*) as total_turmas FROM turmas";
                        $total_turmas = $conn->query($sql)->fetch_assoc()['total_turmas'];

                        $sql = "SELECT COUNT(*) as total_alunos FROM alunos";
                        $total_alunos = $conn->query($sql)->fetch_assoc()['total_alunos'];

                        $sql = "SELECT COUNT(*) as total_professores FROM funcionarios WHERE cargo = 'Professor'";
                        $total_professores = $conn->query($sql)->fetch_assoc()['total_professores'];

                        $sql = "SELECT COUNT(*) as total_funcionarios FROM funcionarios";
                        $total_funcionarios = $conn->query($sql)->fetch_assoc()['total_funcionarios'];
                        ?>
                        <div class="overview-box">
                            <h3><?php echo $total_turmas; ?></h3>
                            <p>Total de Turmas</p>
                        </div>
                        <div class="overview-box">
                            <h3 id="total-alunos"><?php echo $total_alunos; ?></h3>
                            <p>Total de Alunos</p>
                        </div>
                        <div class="overview-box">
                            <h3><?php echo $total_professores; ?></h3>
                            <p>Total de Professores</p>
                        </div>
                        <div class="overview-box">
                            <h3><?php echo $total_funcionarios; ?></h3>
                            <p>Total de Funcionários</p>
                        </div>
                    </div>

                    <!-- Lista de Turmas -->
                    <h3 class="section-title"><i class="fa-solid fa-users"></i> Turmas</h3>
                    <div class="box-turmas">
                        <?php
                        $sql = "SELECT t.id, t.nome, t.ano, f.nome AS professor_nome, f.sobrenome 
                                FROM turmas t 
                                LEFT JOIN funcionarios f ON t.professor_id = f.id";
                        $result = $conn->query($sql);
                        $turmas = [];
                        while ($row = $result->fetch_assoc()) {
                            $turmas[] = $row;
                        }

                        foreach ($turmas as $turma) {
                            $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $turma['id']);
                            $stmt->execute();
                            $quantidade = $stmt->get_result()->fetch_assoc()['quantidade'];
                            $stmt->close();

                            echo "<div class='box-turmas-single' data-turma-id='{$turma['id']}'>";
                            echo "<h3>{$turma['nome']} ({$turma['ano']})</h3>";
                            echo "<p>Professor: " . ($turma['professor_nome'] ? htmlspecialchars($turma['professor_nome'] . " " . $turma['sobrenome']) : "Sem professor") . "</p>";
                            echo "<p>{$quantidade} alunos</p>";
                            echo "</div>";
                        }
                        if (empty($turmas)) {
                            echo "<p>Nenhuma turma cadastrada.</p>";
                        }
                        ?>
                    </div>

                    <!-- Tabela de Alunos -->
                    <div class="tabela-turma-selecionada">
                        <h3>Alunos da Turma Selecionada</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Data de Nascimento</th>
                                    <th>Matrícula</th>
                                    <th>Data de Matrícula</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-alunos">
                                <!-- Dados dos alunos serão inseridos aqui -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Modal de Detalhes do Aluno -->
                    <div id="modal-detalhes-aluno" class="modal">
                        <div class="modal-content">
                            <h2>Detalhes do Aluno</h2>
                            <div class="cadastro-form detalhes-form">
                                <div class="form-row">
                                    <div class="form-group foto-placeholder">
                                        <label>Foto do Aluno</label>
                                        <div class="foto-box">Foto não disponível</div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="detalhes-nome">Nome:</label>
                                        <input type="text" id="detalhes-nome" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="detalhes-nascimento">Data de Nascimento:</label>
                                        <input type="text" id="detalhes-nascimento" readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="detalhes-matricula">Matrícula:</label>
                                        <input type="text" id="detalhes-matricula" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="detalhes-data-matricula">Data de Matrícula:</label>
                                        <input type="text" id="detalhes-data-matricula" readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <label for="detalhes-pai">Nome do Pai:</label>
                                        <input type="text" id="detalhes-pai" readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <label for="detalhes-mae">Nome da Mãe:</label>
                                        <input type="text" id="detalhes-mae" readonly>
                                    </div>
                                </div>
                                <div class="form-buttons">
                                    <button class="btn close-modal-btn">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Confirmação de Exclusão -->
                    <div id="modal-confirm-delete" class="modal">
                        <div class="modal-content">
                            <h2>Confirmar Exclusão</h2>
                            <p>Tem certeza que deseja excluir o aluno com matrícula <span id="delete-matricula"></span>?</p>
                            <div class="modal-buttons">
                                <button id="confirm-delete-btn" class="btn">Sim</button>
                                <button id="cancel-delete-btn" class="btn">Não</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
        </div><!-- FIM CONTENT -->
    </section><!-- FIM MAIN -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            if (localStorage.getItem('sidebarActive') === 'true') {
                $('#sidebar').addClass('active');
                $('#content').addClass('shifted');
            }

            $('#menu-toggle').on('click', function() {
                $('#sidebar').addClass('transition-enabled');
                $('#content').addClass('transition-enabled');
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('shifted');
                localStorage.setItem('sidebarActive', $('#sidebar').hasClass('active'));
                setTimeout(function() {
                    $('#sidebar').removeClass('transition-enabled');
                    $('#content').removeClass('transition-enabled');
                }, 300);
            });

            // Clique nas turmas
            $('.box-turmas-single').click(function() {
                var turmaId = $(this).data('turma-id');
                $.ajax({
                    url: 'fetch_alunos.php',
                    method: 'POST',
                    data: { turma_id: turmaId },
                    success: function(response) {
                        $('#tabela-alunos').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#tabela-alunos').html('<tr><td colspan="5">Erro ao carregar alunos: ' + xhr.statusText + '</td></tr>');
                    }
                });
            });

            if ($('.box-turmas-single').length > 0) {
                $('.box-turmas-single').first().click();
            }

            // Clique na linha do aluno para abrir modal de detalhes
            $(document).on('click', '.aluno-row', function(e) {
                if (!$(e.target).hasClass('action-btn') && !$(e.target).parent().hasClass('action-btn')) {
                    var matricula = $(this).data('matricula');
                    var nome = $(this).data('nome');
                    var nascimento = $(this).data('nascimento');
                    var dataMatricula = $(this).data('matricula-data');
                    var pai = $(this).data('pai');
                    var mae = $(this).data('mae');

                    $('#detalhes-nome').val(nome);
                    $('#detalhes-nascimento').val(nascimento);
                    $('#detalhes-matricula').val(matricula);
                    $('#detalhes-data-matricula').val(dataMatricula);
                    $('#detalhes-pai').val(pai);
                    $('#detalhes-mae').val(mae);

                    $('#modal-detalhes-aluno').css('display', 'block');
                }
            });

            // Fechar modais
            $('.close-btn, #cancel-delete-btn, .close-modal-btn').click(function() {
                $('.modal').css('display', 'none');
            });

            // Função para abrir modal de exclusão
            window.showDeleteModal = function(matricula) {
                $('#delete-matricula').text(matricula);
                $('#modal-confirm-delete').css('display', 'block');
                $('#confirm-delete-btn').off('click').on('click', function() {
                    $.ajax({
                        url: 'delete_aluno.php',
                        method: 'POST',
                        data: { matricula: matricula },
                        dataType: 'json',
                        success: function(response) {
                            var modalContent = $('#modal-confirm-delete .modal-content');
                            if (response.success) {
                                modalContent.html(`
                                    <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Exclusão Concluída</h2>
                                    <p class="modal-message">${response.message}</p>
                                    <div class="modal-buttons">
                                        <button class="btn close-modal-btn">Fechar</button>
                                    </div>
                                `);
                                // Recarregar a tabela
                                $('.box-turmas-single').first().click();
                                // Atualizar o total de alunos
                                $.ajax({
                                    url: 'fetch_totals.php',
                                    method: 'GET',
                                    dataType: 'json',
                                    success: function(data) {
                                        $('#total-alunos').text(data.total_alunos);
                                    },
                                    error: function() {
                                        console.log('Erro ao atualizar o total de alunos');
                                    }
                                });
                            } else {
                                modalContent.html(`
                                    <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                                    <p class="modal-message">${response.message}</p>
                                    <div class="modal-buttons">
                                        <button class="btn close-modal-btn">Fechar</button>
                                    </div>
                                `);
                            }
                            $('.close-modal-btn').click(function() {
                                $('#modal-confirm-delete').css('display', 'none');
                            });
                        },
                        error: function() {
                            $('#modal-confirm-delete .modal-content').html(`
                                <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                                <p class="modal-message">Erro ao comunicar com o servidor.</p>
                                <div class="modal-buttons">
                                    <button class="btn close-modal-btn">Fechar</button>
                                </div>
                            `);
                            $('.close-modal-btn').click(function() {
                                $('#modal-confirm-delete').css('display', 'none');
                            });
                        }
                    });
                });
            };

            // Função placeholder para Editar
            window.editAluno = function(matricula) {
                alert('Editar aluno com matrícula: ' + matricula);
            };
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>