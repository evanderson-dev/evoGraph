<?php
require_once "db_connection.php";

header("Content-Type: application/json");

// Função para gravar logs
function writeLog($message) {
    $logFile = 'import_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

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
    $query = "DELETE FROM respostas_formulario WHERE formulario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $formulario_id_escaped);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao excluir respostas: " . $stmt->error);
    }
    $stmt->close();

    // Confirmar transação
    $conn->commit();
    writeLog("Formulário $formulario_id excluído com sucesso.");
    echo json_encode(["mensagem" => "Formulário excluído com sucesso!", "status" => "success"]);

} catch (Exception $e) {
    // Reverter transação em caso de erro
    $conn->rollback();
    writeLog("Erro ao excluir formulário $formulario_id: " . $e->getMessage());
    echo json_encode(["mensagem" => "Erro ao excluir formulário: " . $e->getMessage(), "status" => "error"]);
}

$conn->close();
?>