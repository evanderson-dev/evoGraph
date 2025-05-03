<?php
require_once "db_connection.php";

header('Content-Type: application/json');

// Ler o corpo da requisição JSON
$input = json_decode(file_get_contents('php://input'), true);

try {
    // Obter ano_id e disciplina_id do corpo JSON
    $ano_id = isset($input['ano_id']) ? intval($input['ano_id']) : 0;
    $disciplina_id = isset($input['disciplina_id']) ? intval($input['disciplina_id']) : 0;
    if ($ano_id <= 0 || $disciplina_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do ano ou disciplina inválido']);
        exit;
    }

    $query = "SELECT id, codigo, descricao 
              FROM habilidades_bncc 
              WHERE ano_id = ? AND disciplina_id = ? 
              ORDER BY codigo ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $ano_id, $disciplina_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $habilidades = [];
    while ($row = $result->fetch_assoc()) {
        $habilidades[] = [
            'id' => $row['id'],
            'codigo' => $row['codigo'],
            'descricao' => $row['descricao']
        ];
    }

    echo json_encode($habilidades);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar habilidades: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>