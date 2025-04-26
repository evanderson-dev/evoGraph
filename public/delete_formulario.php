<?php
session_start();
require_once "db_connection.php";

header("Content-Type: application/json");

// Função para gravar logs
function writeLog($message) {
    $logFile = 'import_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["funcionario_id"])) {
    writeLog("Erro: Usuário não logado ou funcionario_id não encontrado.");
    http_response_code(403);
    echo json_encode(["mensagem" => "Usuário não logado.", "status" => "error"]);
    exit;
}

$funcionario_id = $_SESSION["funcionario_id"];

// Recebe os dados
$dados = json_decode(file_get_contents("php://input"), true);

if (!isset($dados['formularioId']) || empty(trim($dados['formularioId']))) {
    writeLog("Erro: Identificador do formulário não fornecido.");
    http_response_code(400);
    echo json_encode(["mensagem" => "Identificador do formulário não fornecido.", "status" => "error"]);
    exit;
}

$formulario_id = trim($dados['formularioId']);
$formulario_id_escaped = $conn->real_escape_string($formulario_id);

// Verifica se o formulário pertence ao funcionário logado
$query = "SELECT id FROM respostas_formulario WHERE formulario_id = ? AND funcionario_id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $formulario_id_escaped, $funcionario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    writeLog("Erro: Formulário $formulario_id não pertence ao funcionário $funcionario_id.");
    http_response_code(403);
    echo json_encode(["mensagem" => "Você não tem permissão para excluir este formulário.", "status" => "error"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

writeLog("Tentando excluir formulário com ID: $formulario_id");

// Iniciar transação para garantir consistência
$conn->begin_transaction();

try {
    // Excluir da tabela perguntas_formulario
    $query = "DELETE FROM perguntas_formulario WHERE formulario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $formulario_id_escaped);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao excluir perguntas: " . $stmt->error);
    }
    $stmt->close();

    // Excluir da tabela respostas_formulario
    $query = "DELETE FROM respostas_formulario WHERE formulario_id = ? AND funcionario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $formulario_id_escaped, $funcionario_id);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao excluir respostas: " . $stmt->error);
    }
    $stmt->close();

    // Confirmar transação
    $conn->commit();
    writeLog("Formulário $formulario_id excluído com sucesso pelo funcionário $funcionario_id.");
    echo json_encode(["mensagem" => "Formulário excluído com sucesso!", "status" => "success"]);

} catch (Exception $e) {
    // Reverter transação em caso de erro
    $conn->rollback();
    writeLog("Erro ao excluir formulário $formulario_id: " . $e->getMessage());
    echo json_encode(["mensagem" => "Erro ao excluir formulário: " . $e->getMessage(), "status" => "error"]);
}

$conn->close();
?>