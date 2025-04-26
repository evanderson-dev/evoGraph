<?php
session_start();

header("Content-Type: application/json");

// Verificar se o usuário está logado e tem um cargo permitido
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["funcionario_id"])) {
    echo json_encode(["status" => "error", "mensagem" => "Acesso negado."]);
    exit;
}

require_once "db_connection.php";

$funcionario_id = (int)$_SESSION["funcionario_id"];

// Receber o formularioId do corpo da requisição
$rawInput = file_get_contents("php://input");
$dados = json_decode($rawInput, true);

if (!$dados || !isset($dados['formularioId']) || empty($dados['formularioId'])) {
    echo json_encode(["status" => "error", "mensagem" => "Identificador do formulário não fornecido."]);
    $conn->close();
    exit;
}

$formulario_id = $conn->real_escape_string($dados['formularioId']);

// Verificar se o formulário pertence ao funcionário logado (usando perguntas_formulario)
$query = "SELECT COUNT(*) as total FROM perguntas_formulario WHERE formulario_id = ? AND funcionario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $formulario_id, $funcionario_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] == 0) {
    echo json_encode(["status" => "error", "mensagem" => "Você não tem permissão para excluir este formulário."]);
    $result->close();
    $stmt->close();
    $conn->close();
    exit;
}

$result->close();
$stmt->close();

// Excluir perguntas relacionadas ao formulário
$query = "DELETE FROM perguntas_formulario WHERE formulario_id = ? AND funcionario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $formulario_id, $funcionario_id);
if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "mensagem" => "Erro ao excluir perguntas do formulário: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Excluir respostas relacionadas ao formulário
$query = "DELETE FROM respostas_formulario WHERE formulario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $formulario_id);
if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "mensagem" => "Erro ao excluir respostas do formulário: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

$conn->close();

echo json_encode(["status" => "success", "mensagem" => "Formulário excluído com sucesso."]);
?>