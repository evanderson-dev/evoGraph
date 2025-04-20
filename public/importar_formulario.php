<?php
require_once "db_connection.php";

header("Content-Type: application/json");

// Recebe os dados
$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados || !is_array($dados['dados'])) {
    http_response_code(400);
    echo json_encode(["mensagem" => "Dados inválidos ou nenhum dado recebido."]);
    exit;
}

$formulario_id = isset($dados['formularioId']) ? trim($dados['formularioId']) : null;
$dados = $dados['dados'];

$importados = 0;
$erros = [];

// Verifica se a tabela tem o campo formulario_id
$has_formulario_id = false;
$result = $conn->query("SHOW COLUMNS FROM respostas_formulario LIKE 'formulario_id'");
if ($result && $result->num_rows > 0) {
    $has_formulario_id = true;
}

foreach ($dados as $linha) {
    // Tenta diferentes variações do campo Email
    $email = null;
    foreach (['Email', 'E-mail', 'email', 'EMAIL', 'E-Mail', 'Endereço de e-mail'] as $key) {
        if (isset($linha[$key]) && !empty($linha[$key])) {
            $email = trim($linha[$key]);
            break;
        }
    }

    if (!$email) {
        $erros[] = "Linha ignorada: Nenhum campo 'Email' encontrado ou vazio.";
        continue;
    }

    // Busca aluno pelo email
    $email_escaped = $conn->real_escape_string($email);
    $query = "SELECT id FROM alunos WHERE email = '$email_escaped'";
    $result = $conn->query($query);
    
    $aluno_id = null;
    if ($result && $result->num_rows > 0) {
        $aluno = $result->fetch_assoc();
        $aluno_id = $aluno['id'];
    }

    // Prepara os dados JSON
    $json_resposta = $conn->real_escape_string(json_encode($linha, JSON_UNESCAPED_UNICODE));

    // Monta a query com ou sem formulario_id
    if ($has_formulario_id && $formulario_id) {
        $formulario_id_escaped = $conn->real_escape_string($formulario_id);
        $query = "INSERT INTO respostas_formulario (aluno_id, email, dados_json, formulario_id) 
                  VALUES (" . ($aluno_id ? $aluno_id : "NULL") . ", '$email_escaped', '$json_resposta', '$formulario_id_escaped')";
    } else {
        $query = "INSERT INTO respostas_formulario (aluno_id, email, dados_json) 
                  VALUES (" . ($aluno_id ? $aluno_id : "NULL") . ", '$email_escaped', '$json_resposta')";
    }
    
    if ($conn->query($query)) {
        $importados++;
    } else {
        $erros[] = "Erro ao inserir linha com email '$email': " . $conn->error;
    }
}

$response = ["mensagem" => "$importados respostas importadas com sucesso."];
if (!empty($erros)) {
    $response["erros"] = $erros;
}

echo json_encode($response);
$conn->close();
?>