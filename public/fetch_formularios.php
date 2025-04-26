<?php
require_once "db_connection.php";

header("Content-Type: application/json");

$query = "SELECT DISTINCT formulario_id FROM respostas_formulario WHERE formulario_id IS NOT NULL ORDER BY formulario_id";
$result = $conn->query($query);

$formularios = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formularios[] = $row['formulario_id'];
    }
}

echo json_encode($formularios);
$conn->close();
?>