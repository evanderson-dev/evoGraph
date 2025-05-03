<?php
require_once "db_connection.php";

header('Content-Type: application/json');

// Ler o corpo da requisição JSON
$input = json_decode(file_get_contents('php://input'), true);

try {
    // Obter ano_id do corpo JSON
    $ano_id = isset($input['ano_id']) ? intval($input['ano_id']) : 0;
    if ($ano_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do ano inválido']);
        exit;
    }

    $query = "SELECT DISTINCT d.id, d.nome 
              FROM disciplinas d
              INNER JOIN habilidades_bncc h ON d.id = h.disciplina_id
              WHERE h.ano_id = ?
              ORDER BY d.nome ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ano_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $disciplinas = [];
    while ($row = $result->fetch_assoc()) {
        $disciplinas[] = [
            'id' => $row['id'],
            'nome' => $row['nome']
        ];
    }

    echo json_encode($disciplinas);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar disciplinas: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>