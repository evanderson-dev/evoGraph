<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';
require_once 'utils.php';

$cargo = $_SESSION["cargo"];
$funcionario_id = $_SESSION["funcionario_id"];

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['matricula']) && isset($_POST['turma_id'])) {
    $matricula = $_POST['matricula'];
    $turma_id = $_POST['turma_id'];

    if ($cargo !== "Diretor" && $cargo !== "Administrador") {
        echo json_encode(['success' => false, 'message' => 'Ação não permitida para este cargo.']);
        exit;
    }

    // Excluir o aluno
    $sql = "DELETE FROM alunos WHERE matricula = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Erro na preparação da query: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("s", $matricula);
    $success = $stmt->execute();
    $stmt->close();

    if (!$success) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir aluno: ' . $conn->error]);
        exit;
    }

    // Buscar dados atualizados
    $total_alunos = getTotalAlunos($conn, $cargo);
    $quantidade_turma = getQuantidadeTurma($conn, $turma_id);
    $tabela_alunos = generateTabelaAlunos($conn, $turma_id, $cargo, $funcionario_id);

    if ($quantidade_turma === null || $tabela_alunos === null) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados atualizados da turma.']);
        exit;
    }

    // Retornar resposta completa
    $response = [
        'success' => true,
        'message' => 'Aluno excluído com sucesso!',
        'quantidade_turma' => $quantidade_turma,
        'tabela_alunos' => $tabela_alunos
    ];
    if ($cargo === "Diretor" || $cargo === "Administrador") {
        $response['total_alunos'] = $total_alunos;
    }
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida ou parâmetros ausentes.']);
}

$conn->close();
?>