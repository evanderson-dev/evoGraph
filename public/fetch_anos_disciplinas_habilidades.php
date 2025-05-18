<?php
session_start();
require_once "db_connection.php";

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ação inválida'];

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $response['message'] = 'Usuário não autenticado';
    echo json_encode($response);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'anos') {
    // Buscar anos escolares
    $query = "SELECT id, nome FROM anos_escolares ORDER BY ordem";
    $result = $conn->query($query);
    $anos = [];
    while ($row = $result->fetch_assoc()) {
        $anos[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $anos]);
} elseif ($action === 'disciplinas') {
    // Buscar disciplinas disponíveis para um ano específico
    $ano_id = intval($_GET['ano_id'] ?? 0);
    if ($ano_id <= 0) {
        $response['message'] = 'Ano escolar inválido';
        echo json_encode($response);
        exit;
    }
    $query = "SELECT DISTINCT d.id, d.nome 
              FROM disciplinas d
              INNER JOIN habilidades_bncc h ON d.id = h.disciplina_id
              WHERE h.ano_id = ?
              ORDER BY d.nome";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ano_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $disciplinas = [];
    while ($row = $result->fetch_assoc()) {
        $disciplinas[] = $row;
    }
    $stmt->close();
    echo json_encode(['status' => 'success', 'data' => $disciplinas]);
} elseif ($action === 'habilidades') {
    // Buscar habilidades BNCC para um ano e disciplina específicos
    $ano_id = intval($_GET['ano_id'] ?? 0);
    $disciplina_id = intval($_GET['disciplina_id'] ?? 0);
    if ($ano_id <= 0 || $disciplina_id <= 0) {
        $response['message'] = 'Ano escolar ou disciplina inválida';
        echo json_encode($response);
        exit;
    }
    $query = "SELECT codigo, descricao 
              FROM habilidades_bncc 
              WHERE ano_id = ? AND disciplina_id = ?
              ORDER BY codigo";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $ano_id, $disciplina_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $habilidades = [];
    while ($row = $result->fetch_assoc()) {
        $habilidades[] = $row;
    }
    $stmt->close();
    echo json_encode(['status' => 'success', 'data' => $habilidades]);
} else {
    $response['message'] = 'Ação não especificada';
    echo json_encode($response);
}

$conn->close();
?>