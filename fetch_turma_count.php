<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['turma_id'])) {
    $turma_id = $_POST['turma_id'];
    $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $turma_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $quantidade = $result['quantidade'];

    echo json_encode(['quantidade' => $quantidade]);

    $stmt->close();
} else {
    echo json_encode(['error' => 'Requisição inválida']);
}

$conn->close();
?>