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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['turma_id'])) {
    $turma_id = $_POST['turma_id'];

    $total_alunos = getTotalAlunos($conn, $cargo);
    $quantidade_turma = getQuantidadeTurma($conn, $turma_id);
    $tabela_alunos = generateTabelaAlunos($conn, $turma_id, $cargo, $funcionario_id);

    if ($quantidade_turma === null || $tabela_alunos === null) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados da turma.']);
        exit;
    }

    $response = [
        'success' => true,
        'quantidade_turma' => $quantidade_turma,
        'tabela_alunos' => $tabela_alunos
    ];
    if ($cargo === "Diretor") {
        $response['total_alunos'] = $total_alunos;
    }
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Turma não especificada ou requisição inválida.']);
}

$conn->close();
?>