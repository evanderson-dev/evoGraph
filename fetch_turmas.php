<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Diretor" && $_SESSION["cargo"] !== "Coordenador")) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

$sql = "SELECT id, nome, ano FROM turmas";
$result = $conn->query($sql);
$turmas = [];
while ($row = $result->fetch_assoc()) {
    $turmas[] = $row;
}

echo json_encode(['success' => true, 'turmas' => $turmas]);
$conn->close();
?>