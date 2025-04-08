<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json');

$sql = "SELECT MAX(matricula) as max_matricula FROM alunos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$max_matricula = $row['max_matricula'];
if ($max_matricula === null) {
    $next_matricula = '0000000001'; // Primeiro número se o banco estiver vazio
} else {
    $next_number = (int)$max_matricula + 1;
    $next_matricula = str_pad($next_number, 10, '0', STR_PAD_LEFT); // Formata com 10 dígitos
}

echo json_encode(['success' => true, 'matricula' => $next_matricula]);

$stmt->close();
$conn->close();
?>