<?php
require_once "db_connection.php"; // ajuste para o seu arquivo de conexão com o banco

header("Content-Type: application/json");

$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados || !is_array($dados)) {
    echo json_encode(["mensagem" => "Dados inválidos."]);
    exit;
}

$importados = 0;

foreach ($dados as $linha) {
    $email = isset($linha['Email']) ? trim($linha['Email']) : null;
    if (!$email) continue;

    // Busca aluno pelo email
    $stmt = $pdo->prepare("SELECT id FROM alunos WHERE email = ?");
    $stmt->execute([$email]);
    $aluno = $stmt->fetch();

    $aluno_id = $aluno ? $aluno['id'] : null;
    $json_resposta = json_encode($linha, JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("INSERT INTO respostas_formulario (aluno_id, email, dados_json) VALUES (?, ?, ?)");
    $stmt->execute([$aluno_id, $email, $json_resposta]);
    $importados++;
}

echo json_encode(["mensagem" => "$importados respostas importadas com sucesso."]);
