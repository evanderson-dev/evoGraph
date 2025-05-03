<?php
require_once "db_connection.php";

header('Content-Type: application/json');

try {
    $query = "SELECT id, nome FROM anos_escolares ORDER BY ordem ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $anos = [];
    while ($row = $result->fetch_assoc()) {
        $anos[] = [
            'id' => $row['id'],
            'nome' => $row['nome']
        ];
    }

    echo json_encode($anos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar anos: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>