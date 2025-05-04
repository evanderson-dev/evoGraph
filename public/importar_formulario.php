<?php
session_start(); // Iniciar a sessão
require_once "db_connection.php";

// Garantir que a saída seja JSON
header("Content-Type: application/json");

// Função para gravar logs
function writeLog($message) {
    $logFile = 'import_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Função para enviar resposta JSON e encerrar
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Função para garantir que uma string seja UTF-8
function ensureUtf8($string) {
    if (is_string($string)) {
        return mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string, 'UTF-8, ISO-8859-1', true));
    }
    return $string;
}

// Função para limpar e codificar array para JSON
function cleanAndEncodeJson($data) {
    // Limpar valores do array recursivamente
    $cleaned = array_map(function ($item) {
        if (is_array($item)) {
            return cleanAndEncodeJson($item);
        }
        return ensureUtf8($item);
    }, (array)$data);

    $json = json_encode($cleaned, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Erro ao codificar JSON: " . json_last_error_msg());
    }
    return $json;
}

try {
    // Recebe os dados
    $dados = json_decode(file_get_contents("php://input"), true);
    writeLog("Dados recebidos: " . json_encode($dados, JSON_UNESCAPED_UNICODE));

    // Verifica se os dados foram recebidos corretamente
    if (!$dados || !is_array($dados['dados'])) {
        writeLog("Erro: Dados inválidos ou nenhum dado recebido.");
        sendResponse(["mensagem" => "Dados inválidos ou nenhum dado recebido."], 400);
    }

    // Verifica se o usuário está logado e captura o funcionario_id
    $funcionario_id = isset($_SESSION['funcionario_id']) ? $_SESSION['funcionario_id'] : null;
    if (!$funcionario_id) {
        writeLog("Erro: Usuário não logado ou funcionario_id não encontrado.");
        sendResponse(["mensagem" => "Usuário não logado.", "status" => "error"], 403);
    }

    $formulario_id = isset($dados['formularioId']) && !empty(trim($dados['formularioId'])) ? trim($dados['formularioId']) : 'Form_Default';
    $dados_alunos = $dados['dados'];
    $perguntas = isset($dados['perguntas']) ? $dados['perguntas'] : [];
    $respostasCorretas = isset($dados['respostasCorretas']) ? $dados['respostasCorretas'] : [];
    $bncc_habilidade_id = isset($dados['bnccHabilidadeId']) && is_numeric($dados['bnccHabilidadeId']) ? (int)$dados['bnccHabilidadeId'] : null;

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
        writeLog("Aviso: Coluna 'formulario_id' não encontrada na tabela respostas_formulario.");
    }

    // Verifica se a tabela tem o campo pontuacao
    $has_pontuacao = false;
    $result = $conn->query("SHOW COLUMNS FROM respostas_formulario LIKE 'pontuacao'");
    if ($result && $result->num_rows > 0) {
        $has_pontuacao = true;
    } else {
        writeLog("Aviso: Coluna 'pontuacao' não encontrada na tabela respostas_formulario.");
    }

    // Verifica se a tabela tem o campo bncc_habilidade_id
    $has_bncc_habilidade_id = false;
    $result = $conn->query("SHOW COLUMNS FROM respostas_formulario LIKE 'bncc_habilidade_id'");
    if ($result && $result->num_rows > 0) {
        $has_bncc_habilidade_id = true;
    } else {
        writeLog("Aviso: Coluna 'bncc_habilidade_id' não encontrada na tabela respostas_formulario.");
    }

    // Salvar perguntas e respostas corretas na tabela perguntas_formulario
    if (!empty($perguntas) && !empty($respostasCorretas) && count($perguntas) === count($respostasCorretas)) {
        for ($i = 0; $i < count($perguntas); $i++) {
            $pergunta_texto = $conn->real_escape_string(ensureUtf8($perguntas[$i]));
            $resposta_correta = $conn->real_escape_string(ensureUtf8($respostasCorretas[$i]));

            $query = "INSERT INTO perguntas_formulario (formulario_id, pergunta_texto, resposta_correta, bncc_habilidade_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar a query para perguntas_formulario: " . $conn->error);
            }
            $stmt->bind_param("sssi", $formulario_id, $pergunta_texto, $resposta_correta, $bncc_habilidade_id);
            
            if ($stmt->execute()) {
                writeLog("Pergunta '$pergunta_texto' salva com sucesso para formulario_id '$formulario_id' com bncc_habilidade_id '$bncc_habilidade_id'.");
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
        $email_escaped = $conn->real_escape_string(ensureUtf8($email));
        $query = "SELECT id FROM alunos WHERE email = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Erro ao preparar a query para buscar aluno: " . $conn->error);
        }
        $stmt->bind_param("s", $email_escaped);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $aluno_id = null;
        if ($result && $result->num_rows > 0) {
            $aluno = $result->fetch_assoc();
            $aluno_id = $aluno['id'];
            writeLog("Linha $index: Aluno encontrado com ID $aluno_id para email '$email'.");
        } else {
            writeLog("Linha $index: Nenhum aluno encontrado para email '$email'.");
        }
        $stmt->close();

        // Verifica se já existe uma resposta para o mesmo email e formulario_id
        $query = "SELECT id, data_envio FROM respostas_formulario WHERE email = ? AND formulario_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Erro ao preparar a query para verificar resposta existente: " . $conn->error);
        }
        $stmt->bind_param("ss", $email_escaped, $formulario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            $existing_data_envio = $existing['data_envio'];
            writeLog("Linha $index: Resposta existente encontrada para email '$email' e formulario_id '$formulario_id' com data_envio '$existing_data_envio'.");

            if ($data_envio > $existing_data_envio) {
                // Atualiza o registro existente
                $json_resposta = cleanAndEncodeJson($linha);
                writeLog("Linha $index: JSON gerado para atualização: $json_resposta");
                
                $query = "UPDATE respostas_formulario SET 
                          aluno_id = ?,
                          data_envio = ?,
                          dados_json = ?,
                          pontuacao = ?,
                          funcionario_id = ?,
                          bncc_habilidade_id = ?
                          WHERE id = ?";
                
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Erro ao preparar a query para atualizar resposta: " . $conn->error);
                }
                $stmt->bind_param("isssiii", 
                    $aluno_id, 
                    $data_envio, 
                    $json_resposta, 
                    $pontuacao, 
                    $funcionario_id, 
                    $bncc_habilidade_id, 
                    $existing['id']
                );
                
                writeLog("Linha $index: Atualizando resposta existente com bncc_habilidade_id '$bncc_habilidade_id'.");
                
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
            // Insere nova resposta
            $json_resposta = cleanAndEncodeJson($linha);
            writeLog("Linha $index: JSON gerado para inserção: $json_resposta");
            
            $columns = "aluno_id, email, data_envio, dados_json" . ($has_pontuacao ? ", pontuacao" : "") . ($has_formulario_id ? ", formulario_id" : "") . ", funcionario_id" . ($has_bncc_habilidade_id ? ", bncc_habilidade_id" : "");
            $placeholders = "?, ?, ?, ?" . ($has_pontuacao ? ", ?" : "") . ($has_formulario_id ? ", ?" : "") . ", ?" . ($has_bncc_habilidade_id ? ", ?" : "");
            $query = "INSERT INTO respostas_formulario ($columns) VALUES ($placeholders)";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar a query para inserir resposta: " . $conn->error);
            }
            $types = "isss" . ($has_pontuacao ? "d" : "") . ($has_formulario_id ? "s" : "") . "i" . ($has_bncc_habilidade_id ? "i" : "");
            $params = [$aluno_id, $email_escaped, $data_envio, $json_resposta];
            if ($has_pontuacao) $params[] = $pontuacao;
            if ($has_formulario_id) $params[] = $formulario_id;
            $params[] = $funcionario_id;
            if ($has_bncc_habilidade_id) $params[] = $bncc_habilidade_id;
            
            $stmt->bind_param($types, ...$params);
            
            writeLog("Linha $index: Inserindo nova resposta com bncc_habilidade_id '$bncc_habilidade_id'.");
            
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

    $response = ["mensagem" => "$importados respostas importadas, $atualizados atualizadas, $ignorados ignoradas."];
    if (!empty($erros)) {
        $response["erros"] = $erros;
    }

    writeLog("Resposta final: " . json_encode($response));
    sendResponse($response);

} catch (Exception $e) {
    writeLog("Erro geral: " . $e->getMessage());
    sendResponse(["mensagem" => "Erro ao importar dados: " . $e->getMessage(), "erros" => []], 500);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>