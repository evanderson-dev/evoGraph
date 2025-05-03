<?php
require_once "db_connection.php";

header('Content-Type: application/json');

try {
    $ano_id = isset($_POST['ano_id']) ? intval($_POST['ano_id']) : 0;
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