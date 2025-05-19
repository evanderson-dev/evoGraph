<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connection.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !in_array($_SESSION['cargo'], ['Coordenador', 'Diretor', 'Administrador'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit;
}

$tipo = $_POST['tipo'] ?? '';

if (!$tipo) {
    echo json_encode(['success' => false, 'message' => 'Tipo de dado não especificado.']);
    exit;
}

try {
    if ($tipo === 'ano_escolar') {
        $nome = trim($_POST['nome_ano'] ?? '');
        $ordem = filter_var($_POST['ordem'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        if (empty($nome)) {
            echo json_encode(['success' => false, 'message' => 'O nome do ano escolar é obrigatório.']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO anos_escolares (nome, ordem) VALUES (?, ?)");
        $stmt->bind_param("si", $nome, $ordem);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Ano escolar cadastrado com sucesso.']);
    } elseif ($tipo === 'disciplina') {
        $nome = trim($_POST['nome_disciplina'] ?? '');
        if (empty($nome)) {
            echo json_encode(['success' => false, 'message' => 'O nome da disciplina é obrigatório.']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO disciplinas (nome) VALUES (?)");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Disciplina cadastrada com sucesso.']);
    } elseif ($tipo === 'habilidade_bncc') {
        $codigo = trim($_POST['codigo_habilidade'] ?? '');
        $descricao = trim($_POST['descricao_habilidade'] ?? '');
        $ano_escolar_id = filter_var($_POST['ano_escolar_id'] ?? '', FILTER_VALIDATE_INT);
        $disciplina_id = filter_var($_POST['disciplina_id'] ?? '', FILTER_VALIDATE_INT);
        if (empty($codigo) || empty($descricao) || !$ano_escolar_id || !$disciplina_id) {
            echo json_encode(['success' => false, 'message' => 'Todos os campos da habilidade BNCC são obrigatórios.']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO habilidades_bncc (codigo, descricao, ano_escolar_id, disciplina_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $codigo, $descricao, $ano_escolar_id, $disciplina_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Habilidade BNCC cadastrada com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo de dado inválido.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage()]);
}

$conn->close();
?>