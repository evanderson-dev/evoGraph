<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
require_once 'utils.php';

$funcionario_id = $_SESSION["funcionario_id"];
$cargo = $_SESSION["cargo"];

// Restringir acesso a Coordenador e Diretor
if ($cargo !== "Coordenador" && $cargo !== "Diretor") {
    header("Location: dashboard.php");
    exit;
}

// Processar o formulário via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"] ?? '');
    $sobrenome = trim($_POST["sobrenome"] ?? '');
    $data_nascimento = trim($_POST["data_nascimento"] ?? '');
    $matricula = trim($_POST["matricula"] ?? '');
    $data_matricula = trim($_POST["data_matricula_hidden"] ?? date('Y-m-d H:i:s'));
    $nome_pai = trim($_POST["nome_pai"] ?? null);
    $nome_mae = trim($_POST["nome_mae"] ?? null);
    $turma_id = trim($_POST["turma_id"] ?? '');

    // Validar campos obrigatórios
    if (empty($nome) || empty($sobrenome) || empty($data_nascimento) || empty($matricula) || empty($turma_id)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios (Nome, Sobrenome, Data de Nascimento, Matrícula e Turma).']);
        exit;
    }

    // Verificar se a matrícula já existe
    $sql = "SELECT id FROM alunos WHERE matricula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => "A matrícula '$matricula' já está cadastrada."]);
        exit;
    }
    $stmt->close();

    // Upload da foto
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'img/fotos_alunos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $foto_path = $upload_dir . $matricula . '.' . $file_ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path);
    }

    // Inserir aluno
    $fields = "nome, sobrenome, data_nascimento, matricula, data_matricula, turma_id";
    $values = "?, ?, ?, ?, ?, ?";
    $types = "ssssss";
    $params = [&$nome, &$sobrenome, &$data_nascimento, &$matricula, &$data_matricula, &$turma_id];

    if (!empty($nome_pai)) {
        $fields .= ", nome_pai";
        $values .= ", ?";
        $types .= "s";
        $params[] = &$nome_pai;
    }
    if (!empty($nome_mae)) {
        $fields .= ", nome_mae";
        $values .= ", ?";
        $types .= "s";
        $params[] = &$nome_mae;
    }
    if ($foto_path) {
        $fields .= ", foto";
        $values .= ", ?";
        $types .= "s";
        $params[] = &$foto_path;
    }

    $sql = "INSERT INTO alunos ($fields) VALUES ($values)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $total_alunos = getTotalAlunos($conn, $cargo);
        $quantidade_turma = getQuantidadeTurma($conn, $turma_id);
        $tabela_alunos = generateTabelaAlunos($conn, $turma_id, $cargo, $funcionario_id);
        $response = [
            'success' => true,
            'message' => 'Aluno cadastrado com sucesso!',
            'quantidade_turma' => $quantidade_turma,
            'tabela_alunos' => $tabela_alunos
        ];
        if ($cargo === "Diretor") {
            $response['total_alunos'] = $total_alunos;
        }
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar aluno: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Buscar turmas para o select
$sql = "SELECT id, nome FROM turmas";
$result = $conn->query($sql);
$turmas = [];
while ($row = $result->fetch_assoc()) {
    $turmas[] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Cadastrar Aluno</title>
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
    </header>

    <section class="main">
        <div class="sidebar" id="sidebar">
            <a href="dashboard.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="#"><i class="fa-solid fa-chart-bar"></i> Relatórios</a>
            <a href="meu_perfil.php"><i class="fa-solid fa-user-gear"></i> Meu Perfil</a>
            <?php if ($cargo === "Coordenador" || $cargo === "Diretor"): ?>
                <a href="cadastro_turma.php"><i class="fa-solid fa-plus"></i> Cadastrar Turma</a>
                <a href="cadastro_funcionario.php"><i class="fa-solid fa-user-plus"></i> Cadastrar Funcionário</a>
                <a class="sidebar-active" href="cadastro_aluno.php"><i class="fa-solid fa-graduation-cap"></i> Cadastrar Aluno</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i> Sair</a>
            <div class="separator"></div><br>
        </div>

        <div class="content" id="content">
            <div class="titulo-secao">
                <h2>Cadastrar Aluno</h2><br>
                <div class="separator"></div><br>
                <p><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a> / Cadastrar Aluno</p>
            </div>

            <div id="message"></div>

            <div class="cadastro-form">
                <form method="POST" action="cadastro_aluno.php" id="cadastro-aluno-form" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome:</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="sobrenome">Sobrenome:</label>
                            <input type="text" id="sobrenome" name="sobrenome" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento:</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" required>
                        </div>
                        <div class="form-group">
                            <label for="matricula">Matrícula:</label>
                            <input type="text" id="matricula" name="matricula" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="foto">Foto (opcional):</label>
                            <input type="file" id="foto" name="foto" accept="image/*">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="nome_pai">Nome do Pai (opcional):</label>
                            <input type="text" id="nome_pai" name="nome_pai">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="nome_mae">Nome da Mãe (opcional):</label>
                            <input type="text" id="nome_mae" name="nome_mae">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="turma_id">Turma:</label>
                            <select id="turma_id" name="turma_id" required>
                                <option value="">Selecione uma turma</option>
                                <?php foreach ($turmas as $turma): ?>
                                    <option value="<?php echo $turma['id']; ?>">
                                        <?php echo htmlspecialchars($turma['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" id="data_matricula_hidden" name="data_matricula_hidden">

                    <div class="form-buttons">
                        <button type="submit" class="btn">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle da sidebar
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

            // Capturar data e hora ao enviar o formulário
            $('#cadastro-aluno-form').on('submit', function(e) {
                e.preventDefault();
                var now = new Date();
                var year = now.getFullYear();
                var month = String(now.getMonth() + 1).padStart(2, '0');
                var day = String(now.getDate()).padStart(2, '0');
                var hours = String(now.getHours()).padStart(2, '0');
                var minutes = String(now.getMinutes()).padStart(2, '0');
                var seconds = String(now.getSeconds()).padStart(2, '0');
                var dataMatricula = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                $('#data_matricula_hidden').val(dataMatricula);

                // Enviar via AJAX
                let formData = new FormData(this);
                $.ajax({
                    url: 'cadastro_aluno.php',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        $('#message').text(response.message).css('color', response.success ? 'green' : 'red');
                        if (response.success) {
                            if (window.opener) {
                                window.opener.$('#tabela-alunos').html(response.tabela_alunos);
                                if (response.total_alunos) {
                                    window.opener.$('#total-alunos').text(response.total_alunos);
                                }
                                window.opener.$(`.box-turmas-single[data-turma-id="${formData.get('turma_id')}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                            }
                            setTimeout(() => window.close(), 2000);
                        }
                    },
                    error: function(xhr) {
                        $('#message').text('Erro ao comunicar com o servidor: ' + xhr.statusText).css('color', 'red');
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>