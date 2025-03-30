<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Diretor" && $_SESSION["cargo"] !== "Coordenador")) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
require_once 'utils.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = $_POST['matricula'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $sobrenome = $_POST['sobrenome'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $nome_pai = $_POST['nome_pai'] ?? null;
    $nome_mae = $_POST['nome_mae'] ?? null;
    $turma_id = $_POST['turma_id'] ?? '';
    $data_matricula = date('Y-m-d'); // Data atual como padrão

    if (empty($matricula) || empty($nome) || empty($sobrenome) || empty($data_nascimento) || empty($turma_id)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos.']);
        exit;
    }

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
    $sql = "INSERT INTO alunos (matricula, nome, sobrenome, data_nascimento, data_matricula, nome_pai, nome_mae, turma_id, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssis", $matricula, $nome, $sobrenome, $data_nascimento, $data_matricula, $nome_pai, $nome_mae, $turma_id, $foto_path);
    
    if ($stmt->execute()) {
        $total_alunos = getTotalAlunos($conn, $_SESSION["cargo"]);
        $quantidade_turma = getQuantidadeTurma($conn, $turma_id);
        $tabela_alunos = generateTabelaAlunos($conn, $turma_id, $_SESSION["cargo"], $_SESSION["funcionario_id"]);
        $response = [
            'success' => true,
            'message' => 'Aluno cadastrado com sucesso!',
            'quantidade_turma' => $quantidade_turma,
            'tabela_alunos' => $tabela_alunos
        ];
        if ($_SESSION["cargo"] === "Diretor") {
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Aluno</title>
    <link rel="stylesheet" href="css/cadastro.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Cadastrar Novo Aluno</h1>
    <form id="cadastro-aluno-form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="matricula">Matrícula:</label>
            <input type="text" id="matricula" name="matricula" required>
        </div>
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        <div class="form-group">
            <label for="sobrenome">Sobrenome:</label>
            <input type="text" id="sobrenome" name="sobrenome" required>
        </div>
        <div class="form-group">
            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required>
        </div>
        <div class="form-group">
            <label for="foto">Foto (opcional):</label>
            <input type="file" id="foto" name="foto" accept="image/*">
        </div>
        <div class="form-group">
            <label for="nome_pai">Nome do Pai (opcional):</label>
            <input type="text" id="nome_pai" name="nome_pai">
        </div>
        <div class="form-group">
            <label for="nome_mae">Nome da Mãe (opcional):</label>
            <input type="text" id="nome_mae" name="nome_mae">
        </div>
        <div class="form-group">
            <label for="turma_id">Turma:</label>
            <select id="turma_id" name="turma_id" required>
                <option value="">Selecione uma turma</option>
            </select>
        </div>
        <button type="submit">Cadastrar</button>
    </form>
    <div id="message"></div>

    <script>
        $(document).ready(function() {
            // Carregar turmas no select
            $.get('fetch_turmas.php', function(response) {
                if (response.success) {
                    let select = $('#turma_id');
                    response.turmas.forEach(turma => {
                        select.append(`<option value="${turma.id}">${turma.nome} (${turma.ano})</option>`);
                    });
                }
            });

            // Enviar formulário via AJAX
            $('#cadastro-aluno-form').on('submit', function(e) {
                e.preventDefault();
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
                            // Atualizar dashboard (se aberto)
                            if (window.opener) {
                                window.opener.$('#tabela-alunos').html(response.tabela_alunos);
                                if (response.total_alunos) {
                                    window.opener.$('#total-alunos').text(response.total_alunos);
                                }
                                window.opener.$(`.box-turmas-single[data-turma-id="${formData.get('turma_id')}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                            }
                            setTimeout(() => window.close(), 2000); // Fechar janela após 2s
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