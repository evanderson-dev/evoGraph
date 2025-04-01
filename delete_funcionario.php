<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor")) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["funcionario_id"])) {
    $funcionario_id = (int)$_POST["funcionario_id"];

    // Verificar se o funcionário está vinculado a turmas
    $sql = "SELECT COUNT(*) FROM turmas WHERE professor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $funcionario_id);
    $stmt->execute();
    $turmas_count = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    if ($turmas_count > 0) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir este funcionário pois ele está vinculado a uma ou mais turmas.']);
        exit;
    }

    $sql = "DELETE FROM funcionarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $funcionario_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Funcionário excluído com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir funcionário: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
$conn->close();
?>