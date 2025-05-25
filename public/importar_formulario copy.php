<?php
session_start(); // Iniciar a sessão
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

// Verifica se o usuário está logado e captura o funcionario_id
$funcionario_id = isset($_SESSION['funcionario_id']) ? $_SESSION['funcionario_id'] : null;
if (!$funcionario_id) {
    writeLog("Erro: Usuário não logado ou funcionario_id não encontrado.");
    http_response_code(403);
    echo json_encode(["mensagem" => "Usuário não logado.", "status" => "error"]);
    exit;
}

if (!$dados || !is_array($dados['dados'])) {
    writeLog("Erro: Dados inválidos ou nenhum dado recebido.");
    http_response_code(400);
    echo json_encode(["mensagem" => "Dados inválidos ou nenhum dado recebido."]);
    exit;
}

writeLog("Dados recebidos: " . json_encode($dados, JSON_UNESCAPED_UNICODE));

$formulario_id = isset($dados['formularioId']) && !empty(trim($dados['formularioId'])) ? trim($dados['formularioId']) : 'Form_Default';
$dados_alunos = $dados['dados'];
$perguntas = isset($dados['perguntas']) ? $dados['perguntas'] : [];
$respostasCorretas = isset($dados['respostasCorretas']) ? $dados['respostasCorretas'] : [];
$bncc_habilidade = isset($dados['bnccHabilidade']) && !empty(trim($dados['bnccHabilidade'])) ? trim($dados['bnccHabilidade']) : null;

$importados = 0;
$atualizados = 0;
$ignorados = 0;
$erros = [];

// Verifica se a tabela tem o campo formulario_id
$has_formulario_id = false;
$result = $conn->query("SHOW COLUMNS FROM respostas_formulario LIKE 'formulario_id'");
if ($result && $result->num_rows > 0) {
    $has_formulario_id = true;
}

// Verifica se a tabela tem o campo pontuacao
$has_pontuacao = false;
$result = $conn->query("SHOW COLUMNS FROM respostas_formulario LIKE 'pontuacao'");
if ($result && $result->num_rows > 0) {
    $has_pontuacao = true;
}

// Salvar perguntas e respostas corretas na tabela perguntas_formulario
if (!empty($perguntas) && !empty($respostasCorretas) && count($perguntas) === count($respostasCorretas)) {
    for ($i = 0; $i < count($perguntas); $i++) {
        $pergunta_texto = $conn->real_escape_string($perguntas[$i]);
        $resposta_correta = $conn->real_escape_string($respostasCorretas[$i]);

        $query = "INSERT INTO perguntas_formulario (formulario_id, pergunta_texto, resposta_correta, bncc_habilidade) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $formulario_id, $pergunta_texto, $resposta_correta, $bncc_habilidade);
        
        if ($stmt->execute()) {
            writeLog("Pergunta '$pergunta_texto' salva com sucesso para formulario_id '$formulario_id' com bncc_habilidade '$bncc_habilidade'.");
        } else {
            $erros[] = "Erro ao salvar pergunta '$pergunta_texto': " . $stmt->error;
            writeLog("Erro ao salvar pergunta '$pergunta_texto': " . $stmt->error);
        }
        $stmt->close();
    }
} else {
    $erros[] = "Nenhuma pergunta ou resposta correta fornecida para salvar.";
    writeLog("Nenhuma pergunta ou resposta correta fornecida para salvar.");
}

// Processar respostas dos alunos
foreach ($dados_alunos as $index => $linha) {
    // Tenta diferentes variações do campo Email
    $email = null;
    foreach (['Email', 'E-mail', 'email', 'EMAIL', 'E-Mail', 'Endereço de e-mail', 'Endereço de Email'] as $key) {
        if (isset($linha[$key]) && !empty($linha[$key])) {
            $email = trim($linha[$key]);
            break;
        }
    }

    if (!$email) {
        $erros[] = "Linha $index: Nenhum campo 'Email' encontrado ou vazio.";
        writeLog("Linha $index: Nenhum campo 'Email' encontrado ou vazio.");
        continue;
    }

    // Tenta extrair a data de "Carimbo de data/hora"
    $data_envio = null;
    foreach (['Carimbo de data/hora', 'Timestamp', 'Data', 'Date'] as $key) {
        if (isset($linha[$key]) && !empty($linha[$key])) {
            $data_raw = trim($linha[$key]);
            $dateTime = DateTime::createFromFormat('d/m/Y H:i:s', $data_raw);
            if ($dateTime !== false) {
                $data_envio = $dateTime->format('Y-m-d H:i:s');
            } else {
                $dateTime = DateTime::createFromFormat('m/d/Y H:i:s', $data_raw);
                if ($dateTime !== false) {
                    $data_envio = $dateTime->format('Y-m-d H:i:s');
                }
            }
            break;
        }
    }

    if (!$data_envio) {
        $data_envio = date('Y-m-d H:i:s');
        writeLog("Linha $index: 'Carimbo de data/hora' não encontrado ou inválido. Usando CURRENT_TIMESTAMP.");
    } else {
        writeLog("Linha $index: Data extraída: $data_envio");
    }

    // Tenta extrair a pontuação
    $pontuacao = null;
    if ($has_pontuacao && isset($linha['Pontuação']) && !empty($linha['Pontuação'])) {
        $pontuacao_raw = trim($linha['Pontuação']);
        if (preg_match('/^(\d+\.?\d*)\s*\/\s*(\d+\.?\d*)$/', $pontuacao_raw, $matches)) {
            $numerador = floatval($matches[1]);
            $denominador = floatval($matches[2]);
            if ($denominador > 0 && $numerador >= 0 && $numerador <= $denominador) {
                $pontuacao = $numerador;
                writeLog("Linha $index: Pontuação extraída: $pontuacao (de $pontuacao_raw)");
            } else {
                $erros[] = "Linha $index: Pontuação '$pontuacao_raw' inválida (numerador ou denominador fora do intervalo).";
                writeLog("Linha $index: Pontuação inválida: $pontuacao_raw");
            }
        } else {
            if (is_numeric($pontuacao_raw)) {
                $pontuacao = floatval($pontuacao_raw);
                if ($pontuacao >= 0 && $pontuacao <= 10) {
                    writeLog("Linha $index: Pontuação numérica extraída: $pontuacao");
                } else {
                    $pontuacao = null;
                    $erros[] = "Linha $index: Pontuação '$pontuacao_raw' fora do intervalo permitido (0-10).";
                    writeLog("Linha $index: Pontuação fora do intervalo: $pontuacao_raw");
                }
            } else {
                $erros[] = "Linha $index: Formato de pontuação '$pontuacao_raw' não reconhecido.";
                writeLog("Linha $index: Formato de pontuação não reconhecido: $pontuacao_raw");
            }
        }
    } else {
        writeLog("Linha $index: 'Pontuação' não encontrada ou vazia.");
    }

    // Busca aluno pelo email
    $email_escaped = $conn->real_escape_string($email);
    $query = "SELECT id FROM alunos WHERE email = '$email_escaped'";
    $result = $conn->query($query);
    
    $aluno_id = null;
    if ($result && $result->num_rows > 0) {
        $aluno = $result->fetch_assoc();
        $aluno_id = $aluno['id'];
        writeLog("Linha $index: Aluno encontrado com ID $aluno_id para email '$email'.");
    } else {
        writeLog("Linha $index: Nenhum aluno encontrado para email '$email'.");
    }

    // Verifica se já existe uma resposta para o mesmo email e formulario_id
    $formulario_id_escaped = $conn->real_escape_string($formulario_id);
    $query = "SELECT id, data_envio FROM respostas_formulario WHERE email = '$email_escaped' AND formulario_id = '$formulario_id_escaped'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        $existing_data_envio = $existing['data_envio'];
        writeLog("Linha $index: Resposta existente encontrada para email '$email' e formulario_id '$formulario_id' com data_envio '$existing_data_envio'.");

        if ($data_envio > $existing_data_envio) {
            // Atualiza o registro existente
            $json_resposta = $conn->real_escape_string(json_encode($linha, JSON_UNESCAPED_UNICODE));
            $query = "UPDATE respostas_formulario SET 
                      aluno_id = " . ($aluno_id ? $aluno_id : "NULL") . ",
                      data_envio = '$data_envio',
                      dados_json = '$json_resposta',
                      pontuacao = " . ($pontuacao !== null ? $pontuacao : "NULL") . ",
                      funcionario_id = " . ($funcionario_id ? $funcionario_id : "NULL") . "
                      WHERE id = " . $existing['id'];
            
            writeLog("Linha $index: Atualizando resposta existente com query: $query");
            
            if ($conn->query($query)) {
                $atualizados++;
                writeLog("Linha $index: Atualização bem-sucedida.");
            } else {
                $erros[] = "Erro ao atualizar linha $index com email '$email': " . $conn->error;
                writeLog("Linha $index: Erro na atualização: " . $conn->error);
            }
        } else {
            $ignorados++;
            writeLog("Linha $index: Resposta ignorada (data_envio '$data_envio' é mais antiga que '$existing_data_envio').");
            continue;
        }
    } else {
        // Insere nova resposta
        $json_resposta = $conn->real_escape_string(json_encode($linha, JSON_UNESCAPED_UNICODE));
        $columns = "aluno_id, email, data_envio, dados_json" . ($has_pontuacao ? ", pontuacao" : "") . ($has_formulario_id ? ", formulario_id" : "") . ", funcionario_id";
        $values = ($aluno_id ? $aluno_id : "NULL") . ", '$email_escaped', '$data_envio', '$json_resposta'" . ($has_pontuacao ? ", " . ($pontuacao !== null ? $pontuacao : "NULL") : "") . ($has_formulario_id ? ", '$formulario_id_escaped'" : "") . ", " . ($funcionario_id ? $funcionario_id : "NULL");
        
        $query = "INSERT INTO respostas_formulario ($columns) VALUES ($values)";
        
        writeLog("Linha $index: Executando query: $query");
        
        if ($conn->query($query)) {
            $importados++;
            writeLog("Linha $index: Inserção bem-sucedida.");
        } else {
            $erros[] = "Erro ao inserir linha $index com email '$email': " . $conn->error;
            writeLog("Linha $index: Erro na inserção: " . $conn->error);
        }
    }
}

$response = ["mensagem" => "$importados respostas importadas, $atualizados atualizadas, $ignorados ignoradas."];
if (!empty($erros)) {
    $response["erros"] = $erros;
}

writeLog("Resposta final: " . json_encode($response));
echo json_encode($response);
$conn->close();
?>