<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json'); // Garantir cabeçalho JSON

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'fetch_aluno' && isset($_POST['matricula'])) {
    $matricula = $_POST['matricula'];
    $context = isset($_POST['context']) ? $_POST['context'] : 'details';

    $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.data_matricula, a.nome_pai, a.nome_mae, a.turma_id, t.nome AS turma_nome, a.foto, a.email
            FROM alunos a 
            LEFT JOIN turmas t ON a.turma_id = t.id 
            WHERE a.matricula = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Erro na preparação da query: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($context === 'edit') {
            $row['data_nascimento'] = $row['data_nascimento'] ?: 'N/A';
            $row['data_matricula'] = $row['data_matricula'] ?: 'N/A';
        } else {
            $row['data_nascimento'] = $row['data_nascimento'] ? date("d/m/Y", strtotime($row["data_nascimento"])) : 'N/A';
            $row['data_matricula'] = $row['data_matricula'] ? date("d/m/Y", strtotime($row["data_matricula"])) : 'N/A';
        }
        echo json_encode(['success' => true, 'aluno' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aluno não encontrado.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida ou parâmetros ausentes.']);
}

$conn->close();
?>