<?php
session_start();
require_once "db_connection.php";

header("Content-Type: application/json");

// Verifica o token CSRF
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(["mensagem" => "Erro: Token CSRF inválido."]);
    exit;
}

// Recebe os dados
$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados || !is_array($dados['dados'])) {
    http_response_code(400);
    echo json_encode(["mensagem" => "Dados inválidos."]);
    exit;
}

$formulario_id = isset($dados['formularioId']) ? trim($dados['formularioId']) : null;
$dados = $dados['dados'];

$importados = 0;

foreach ($dados as $linha) {
    $email = isset($linha['Email']) ? trim($linha['Email']) : null;
    
    // Valida o e-mail
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        continue;
    }

    // Busca aluno pelo email
    $email = $conn->real_escape_string($email);
    $query = "SELECT id FROM alunos WHERE email = '$email'";
    $result = $conn->query($query);
    
    $aluno_id = null;
    if ($result && $result->num_rows > 0) {
        $aluno = $result->fetch_assoc();
        $aluno_id = $aluno['id'];
    }

    // Prepara os dados JSON
    $json_resposta = $conn->real_escape_string(json_encode($linha, JSON_UNESCAPED_UNICODE));
    $formulario_id_escaped = $formulario_id ? "'".$conn->real_escape_string($formulario_id)."'" : "NULL";

    // Insere os dados
    $query = "INSERT INTO respostas_formulario (aluno_id, email, dados_json, formulario_id) 
              VALUES (" . ($aluno_id ? $aluno_id : "NULL") . ", '$email', '$json_resposta', $formulario_id_escaped)";
    
    if ($conn->query($query)) {
        $importados++;
    } else {
        http_response_code(500);
        echo json_encode(["mensagem" => "Erro ao importar: " . $conn->error]);
        exit;
    }
}

echo json_encode(["mensagem" => "$importados respostas importadas com sucesso."]);
$conn->close();
?>