<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor" && $_SESSION["cargo"] !== "Administrador")) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json');

$search = trim($_POST['search'] ?? '');
$cargo = trim($_POST['cargo'] ?? '');
$id = isset($_POST['id']) ? (int)$_POST['id'] : null;

$sql = "SELECT id, nome, sobrenome, email, rf, cargo, data_nascimento FROM funcionarios WHERE 1=1";
$params = [];
$types = "";

if ($id !== null) {
    $sql .= " AND id = ?";
    $params[] = $id;
    $types .= "i";
} else {
    if (!empty($search)) {
        $sql .= " AND (nome LIKE ? OR rf LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }
    if (!empty($cargo)) {
        $sql .= " AND cargo = ?";
        $params[] = $cargo;
        $types .= "s";
    }
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$funcionarios = [];
while ($row = $result->fetch_assoc()) {
    $funcionarios[] = $row;
}

echo json_encode(['success' => true, 'funcionarios' => $funcionarios]);
$stmt->close();
$conn->close();
?>