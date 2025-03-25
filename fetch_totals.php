<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';

$sql = "SELECT COUNT(*) as total_alunos FROM alunos";
$result = $conn->query($sql);
$total_alunos = $result->fetch_assoc()['total_alunos'];

echo json_encode(['total_alunos' => $total_alunos]);

$conn->close();
?>