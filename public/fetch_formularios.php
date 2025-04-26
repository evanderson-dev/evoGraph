<?php
session_start();

// Verificar se o usuário está logado e tem um cargo permitido
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["funcionario_id"])) {
    echo json_encode([]);
    exit;
}

require_once "db_connection.php";

$funcionario_id = (int)$_SESSION["funcionario_id"];

// Buscar os formulários associados ao funcionário logado
$query = "SELECT DISTINCT formulario_id FROM respostas_formulario WHERE funcionario_id = ? ORDER BY formulario_id";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode([]);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $funcionario_id);
$stmt->execute();
$result = $stmt->get_result();

$formularios = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formularios[] = $row['formulario_id'];
    }
}

$stmt->close();
$conn->close();

// Retornar a lista de formulários como JSON
header('Content-Type: application/json');
echo json_encode($formularios);
?>