<?php
session_start();
require_once "db_connection.php";

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ação inválida'];

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $response['message'] = 'Usuário não autenticado';
    error_log('fetch_anos_disciplinas_habilidades: Usuário não autenticado');
    echo json_encode($response);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'disciplinas') {
    $ano_id = intval($_GET['ano_id'] ?? 0);
    if ($ano_id <= 0) {
        $response['message'] = 'Ano escolar inválido';
        error_log('fetch_anos_disciplinas_habilidades: Ano escolar inválido');
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
    error_log('Disciplinas retornadas para ano_id ' . $ano_id . ': ' . json_encode($disciplinas));
    echo json_encode(['status' => 'success', 'data' => $disciplinas]);
} elseif ($action === 'habilidades') {
    $ano_id = intval($_GET['ano_id'] ?? 0);
    $disciplina_id = intval($_GET['disciplina_id'] ?? 0);
    if ($ano_id <= 0 || $disciplina_id <= 0) {
        $response['message'] = 'Ano escolar ou disciplina inválida';
        error_log('fetch_anos_disciplinas_habilidades: Ano ou disciplina inválida');
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
    error_log('Habilidades retornadas para ano_id ' . $ano_id . ', disciplina_id ' . $disciplina_id . ': ' . json_encode($habilidades));
    echo json_encode(['status' => 'success', 'data' => $habilidades]);
} else {
    $response['message'] = 'Ação não especificada';
    error_log('fetch_anos_disciplinas_habilidades: Ação não especificada');
    echo json_encode($response);
}

$conn->close();
?>