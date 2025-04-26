<?php
require_once "db_connection.php";

// Ativar exibição de erros para depuração (remova isso em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// Função para gravar logs
function writeLog($message) {
    $logFile = 'import_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Função para enviar resposta JSON e encerrar o script
function sendResponse($status, $message, $additionalData = []) {
    $response = array_merge(["status" => $status, "mensagem" => $message], $additionalData);
    writeLog("Resposta enviada: " . json_encode($response, JSON_UNESCAPED_UNICODE));
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Recebe os dados
$rawInput = file_get_contents("php://input");
writeLog("Raw input recebido: " . $rawInput);

$dados = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendResponse("error", "Erro ao decodificar JSON: " . json_last_error_msg());
}

if (!$dados || !is_array($dados['dados'])) {
    sendResponse("error", "Dados inválidos ou nenhum dado recebido.");
}

writeLog("Dados decodificados: " . json_encode($dados, JSON_UNESCAPED_UNICODE));

$formulario_id = isset($dados['formularioId']) && !empty(trim($dados['formularioId'])) ? trim($dados['formularioId']) : 'Form_Default';
$funcionario_id = isset($dados['funcionarioId']) && !empty($dados['funcionarioId']) ? (int)$dados['funcionarioId'] : null;
$dados_alunos = $dados['dados'];
$perguntas = isset($dados['perguntas']) ? $dados['perguntas'] : [];
$respostasCorretas = isset($dados['respostasCorretas']) ? $dados['respostasCorretas'] : [];
$bncc_habilidade = isset($dados['bnccHabilidade']) && !empty(trim($dados['bnccHabilidade'])) ? trim($dados['bnccHabilidade']) : null;

if (!$funcionario_id) {
    sendResponse("error", "ID do funcionário não fornecido.");
}

$importados = 0;
$atualizados = 0;
$ignorados = 0;
$erros = [];

// Verifica se a tabela tem o campo formulario_id
$has_formulario_id = false;
$result = $conn->query("SHOW COLUMNS FROM respostas_formulario LIKE 'formulario_id'");
if ($result && $result->num_rows > 0) {
    $has_formulario_id = true;
} else {
    $erros[] = "Campo 'formulario_id' não encontrado na tabela respostas_formulario.";
    writeLog("Erro: Campo 'formulario_id' não encontrado na tabela respostas_formulario.");
}

// Verifica se a tabela tem o campo pontuacao
$has_pontuacao = false;
$result = $conn->query("SHOW COLUMNS FROM respostas_formulario LIKE 'pontuacao'");
if ($result && $result->num_rows > 0) {
    $has_pontuacao = true;
} else {
    writeLog("Aviso: Campo 'pontuacao' não encontrado na tabela respostas_formulario.");
}

// Salvar perguntas e respostas corretas na tabela perguntas_formulario
if (!empty($perguntas) && !empty($respostasCorretas) && count($perguntas) === count($respostasCorretas)) {
    for ($i = 0; $i < count($perguntas); $i++) {
        $pergunta_texto = $conn->real_escape_string($perguntas[$i]);
        $resposta_correta = $conn->real_escape_string($respostasCorretas[$i]);

        $query = "INSERT INTO perguntas_formulario (formulario_id, funcionario_id, pergunta_texto, resposta_correta, bncc_habilidade) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            $erros[] = "Erro ao preparar query para perguntas_formulario: " . $conn->error;
            writeLog("Erro ao preparar query para perguntas_formulario: " . $conn->error);
            continue;
        }
        $stmt->bind_param("sisss", $formulario_id, $funcionario_id, $pergunta_texto, $resposta_correta, $bncc_habilidade);
        
        if ($stmt->execute()) {
            writeLog("Pergunta '$pergunta_texto' salva com sucesso para formulario_id '$formulario_id' e funcionario_id '$funcionario_id'.");
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
            // Tenta converter do formato DD/MM/YYYY HH:MM:SS para YYYY-MM-DD HH:MM:SS
            $dateTime = DateTime::createFromFormat('d/m/Y H:i:s', $data_raw);
            if ($dateTime !== false) {
                $data_envio = $dateTime->format('Y-m-d H:i:s');
            } else {
                // Tenta outros formatos
                $dateTime = DateTime::createFromFormat('m/d/Y H:i:s', $data_raw);
                if ($dateTime !== false) {
                    $data_envio = $dateTime->format('Y-m-d H:i:s');
                }
            }
            break;
        }
    }

    if (!$data_envio) {
        $data_envio = date('Y-m-d H:i:s'); // Fallback para CURRENT_TIMESTAMP
        writeLog("Linha $index: 'Carimbo de data/hora' não encontrado ou inválido. Usando CURRENT_TIMESTAMP.");
    } else {
        writeLog("Linha $index: Data extraída: $data_envio");
    }

    // Tenta extrair a pontuação
    $pontuacao = null;
    if ($has_pontuacao && isset($linha['Pontuação']) && !empty($linha['Pontuação'])) {
        $pontuacao_raw = trim($linha['Pontuação']);
        // Trata o formato "X / Y" (exemplo: "4 / 10")
        if (preg_match('/^(\d+\.?\d*)\s*\/\s*(\d+\.?\d*)$/', $pontuacao_raw, $matches)) {
            $numerador = floatval($matches[1]);
            $denominador = floatval($matches[2]);
            if ($denominador > 0 && $numerador >= 0 && $numerador <= $denominador) {
                $pontuacao = $numerador; // Usa o numerador diretamente (exemplo: 4.00)
                writeLog("Linha $index: Pontuação extraída: $pontuacao (de $pontuacao_raw)");
            } else {
                $erros[] = "Linha $index: Pontuação '$pontuacao_raw' inválida (numerador ou denominador fora do intervalo).";
                writeLog("Linha $index: Pontuação inválida: $pontuacao_raw");
            }
        } else {
            // Tenta tratar como número puro (exemplo: "4" ou "4.5")
            if (is_numeric($pontuacao_raw)) {
                $pontuacao = floatval($pontuacao_raw);
                if ($pontuacao >= 0 && $pontuacao <= 10) { // Supondo pontuação entre 0 e 10
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
    
    if ($result === false) {
        $erros[] = "Erro ao buscar aluno com email '$email': " . $conn->error;
        writeLog("Erro ao buscar aluno com email '$email': " . $conn->error);
        continue;
    }

    $aluno_id = null;
    if ($result->num_rows > 0) {
        $aluno = $result->fetch_assoc();
        $aluno_id = $aluno['id'];
        writeLog("Linha $index: Aluno encontrado com ID $aluno_id para email '$email'.");
    } else {
        writeLog("Linha $index: Nenhum aluno encontrado para email '$email'.");
    }

    // Verifica se já existe uma resposta para o mesmo email, formulario_id e funcionario_id
    $formulario_id_escaped = $conn->real_escape_string($formulario_id);
    $query = "SELECT id, data_envio FROM respostas_formulario WHERE email = ? AND formulario_id = ? AND funcionario_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $erros[] = "Erro ao preparar query para verificar resposta existente: " . $conn->error;
        writeLog("Erro ao preparar query para verificar resposta existente: " . $conn->error);
        continue;
    }
    $stmt->bind_param("ssi", $email_escaped, $formulario_id_escaped, $funcionario_id);
    if (!$stmt->execute()) {
        $erros[] = "Erro ao executar query para verificar resposta existente: " . $stmt->error;
        writeLog("Erro ao executar query para verificar resposta existente: " . $stmt->error);
        $stmt->close();
        continue;
    }
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        $existing_data_envio = $existing['data_envio'];
        writeLog("Linha $index: Resposta existente encontrada para email '$email', formulario_id '$formulario_id' e funcionario_id '$funcionario_id' com data_envio '$existing_data_envio'.");

        // Compara data_envio
        if ($data_envio > $existing_data_envio) {
            // Nova resposta é mais recente, atualiza o registro
            $json_resposta = $conn->real_escape_string(json_encode($linha, JSON_UNESCAPED_UNICODE));
            $query = "UPDATE respostas_formulario SET 
                      aluno_id = " . ($aluno_id ? $aluno_id : "NULL") . ",
                      data_envio = ?,
                      dados_json = ?,
                      pontuacao = " . ($pontuacao !== null ? $pontuacao : "NULL") . "
                      WHERE id = ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                $erros[] = "Erro ao preparar query para atualização: " . $conn->error;
                writeLog("Erro ao preparar query para atualização: " . $conn->error);
                continue;
            }
            $stmt->bind_param("ssi", $data_envio, $json_resposta, $existing['id']);
            
            writeLog("Linha $index: Atualizando resposta existente com query: $query");
            
            if ($stmt->execute()) {
                $atualizados++;
                writeLog("Linha $index: Atualização bem-sucedida.");
            } else {
                $erros[] = "Erro ao atualizar linha $index com email '$email': " . $stmt->error;
                writeLog("Linha $index: Erro na atualização: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $ignorados++;
            writeLog("Linha $index: Resposta ignorada (data_envio '$data_envio' é mais antiga que '$existing_data_envio').");
            continue;
        }
    } else {
        // Nenhuma resposta existente, insere nova
        $json_resposta = $conn->real_escape_string(json_encode($linha, JSON_UNESCAPED_UNICODE));
        $columns = "aluno_id, email, data_envio, dados_json, funcionario_id" . ($has_pontuacao ? ", pontuacao" : "") . ($has_formulario_id ? ", formulario_id" : "");
        $values = ($aluno_id ? $aluno_id : "NULL") . ", ?, ?, ?, ?" . ($has_pontuacao ? ", " . ($pontuacao !== null ? $pontuacao : "NULL") : "") . ($has_formulario_id ? ", ?" : "");
        
        $query = "INSERT INTO respostas_formulario ($columns) VALUES ($values)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            $erros[] = "Erro ao preparar query para inserção: " . $conn->error;
            writeLog("Erro ao preparar query para inserção: " . $conn->error);
            continue;
        }
        
        if ($has_formulario_id && $has_pontuacao) {
            $stmt->bind_param("sssisid", $email_escaped, $data_envio, $json_resposta, $funcionario_id, $pontuacao, $formulario_id_escaped);
        } elseif ($has_formulario_id) {
            $stmt->bind_param("sssis", $email_escaped, $data_envio, $json_resposta, $funcionario_id, $formulario_id_escaped);
        } elseif ($has_pontuacao) {
            $stmt->bind_param("sssdi", $email_escaped, $data_envio, $json_resposta, $funcionario_id, $pontuacao);
        } else {
            $stmt->bind_param("sssi", $email_escaped, $data_envio, $json_resposta, $funcionario_id);
        }
        
        writeLog("Linha $index: Executando query: $query");
        
        if ($stmt->execute()) {
            $importados++;
            writeLog("Linha $index: Inserção bem-sucedida.");
        } else {
            $erros[] = "Erro ao inserir linha $index com email '$email': " . $stmt->error;
            writeLog("Linha $index: Erro na inserção: " . $stmt->error);
        }
        $stmt->close();
    }
}

$response = ["status" => "success", "mensagem" => "$importados respostas importadas, $atualizados atualizadas, $ignorados ignoradas."];
if (!empty($erros)) {
    $response["status"] = "error";
    $response["erros"] = $erros;
}

sendResponse($response["status"], $response["mensagem"], ["erros" => isset($response["erros"]) ? $response["erros"] : []]);

$conn->close();
?>