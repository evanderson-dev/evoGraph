<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connection.php';

try {
    $anos_escolares = [];
    $disciplinas = [];
    $habilidades_bncc = [];

    // Fetch anos escolares
    $result = $conn->query("SELECT id, nome FROM anos_escolares ORDER BY nome");
    while ($row = $result->fetch_assoc()) {
        $anos_escolares[] = $row;
    }

    // Fetch disciplinas
    $result = $conn->query("SELECT id, nome FROM disciplinas ORDER BY nome");
    while ($row = $result->fetch_assoc()) {
        $disciplinas[] = $row;
    }

    // Fetch habilidades BNCC
    $result = $conn->query("SELECT id, codigo, descricao FROM habilidades_bncc ORDER BY codigo");
    while ($row = $result->fetch_assoc()) {
        $habilidades_bncc[] = $row;
    }

    echo json_encode([
        'success' => true,
        'anos_escolares' => $anos_escolares,
        'disciplinas' => $disciplinas,
        'habilidades_bncc' => $habilidades_bncc
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}

$conn->close();
?>