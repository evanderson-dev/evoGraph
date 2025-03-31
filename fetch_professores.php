<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor")) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json');

$sql = "SELECT id, nome, sobrenome FROM funcionarios WHERE cargo = 'Professor'";
$result = $conn->query($sql);
$professores = [];
while ($row = $result->fetch_assoc()) {
    $professores[] = $row;
}

echo json_encode(['success' => true, 'professores' => $professores]);
$conn->close();
?>