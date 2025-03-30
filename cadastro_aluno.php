<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';
require_once 'utils.php';

$funcionario_id = $_SESSION["funcionario_id"];
$cargo = $_SESSION["cargo"];

if ($cargo !== "Coordenador" && $cargo !== "Diretor") {
    echo json_encode(['success' => false, 'message' => 'Ação não permitida para este cargo.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"] ?? '');
    $sobrenome = trim($_POST["sobrenome"] ?? '');
    $data_nascimento = trim($_POST["data_nascimento"] ?? '');
    $matricula = trim($_POST["matricula"] ?? '');
    $data_matricula = trim($_POST["data_matricula_hidden"] ?? date('Y-m-d H:i:s'));
    $nome_pai = trim($_POST["nome_pai"] ?? null);
    $nome_mae = trim($_POST["nome_mae"] ?? null);
    $turma_id = trim($_POST["turma_id"] ?? '');

    if (empty($nome) || empty($sobrenome) || empty($data_nascimento) || empty($matricula) || empty($turma_id)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios (Nome, Sobrenome, Data de Nascimento, Matrícula e Turma).']);
        exit;
    }

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

echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
$conn->close();
?>