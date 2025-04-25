<?php
session_start();
require_once "db_connection.php";

header("Content-Type: application/json");

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["cargo"]) || !isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Usuário não autenticado."]);
    exit;
}

$funcionario_id = (int)$_SESSION["id"];
$cargo = $_SESSION["cargo"];

$query = "SELECT DISTINCT formulario_id FROM respostas_formulario WHERE formulario_id IS NOT NULL";
if ($cargo === 'Professor') {
    $query .= " AND funcionario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $funcionario_id);
} else {
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();

$formularios = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formularios[] = $row['formulario_id'];
    }
}

echo json_encode($formularios);

$stmt->close();
$conn->close();
?>