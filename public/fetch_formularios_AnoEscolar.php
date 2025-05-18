<?php
session_start();
require_once "db_connection.php";

header("Content-Type: application/json");

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["funcionario_id"])) {
    http_response_code(403);
    echo json_encode(["error" => "Usuário não logado."]);
    exit;
}

$funcionario_id = $_SESSION["funcionario_id"];

// Consulta para obter os formulario_id associados ao funcionário logado
$query = "SELECT DISTINCT formulario_id FROM respostas_formulario WHERE funcionario_id = ? ORDER BY formulario_id";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $funcionario_id);
$stmt->execute();
$result = $stmt->get_result();

$formularios = [];
while ($row = $result->fetch_assoc()) {
    $formularios[] = $row["formulario_id"];
}

$stmt->close();
$conn->close();

echo json_encode($formularios);
?>