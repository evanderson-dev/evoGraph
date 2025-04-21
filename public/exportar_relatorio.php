<?php
require_once "db_connection.php";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=relatorio_bncc.csv');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); // BOM para UTF-8

// Escreve cabeçalhos
fputcsv($output, ['Relatório BNCC', '', '']);
fputcsv($output, []);

// Filtro por formulario_id
$formulario_id = isset($_GET['formulario_id']) ? $conn->real_escape_string($_GET['formulario_id']) : '';

// Média por Série
fputcsv($output, ['Média de Pontuação por Série', '', '']);
fputcsv($output, ['Série', 'Média de Pontuação', 'Total de Alunos']);
$query = "SELECT JSON_EXTRACT(dados_json, '$.\"Série:\"') AS serie,
                 AVG(pontuacao) AS media_pontuacao,
                 COUNT(*) AS total_alunos
          FROM respostas_formulario
          " . ($formulario_id ? "WHERE formulario_id = '$formulario_id'" : "") . "
          GROUP BY serie
          ORDER BY serie";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $serie = $row['serie'] ? trim($row['serie'], '"') : 'Não Informada';
    fputcsv($output, [$serie, round($row['media_pontuacao'], 2), $row['total_alunos']]);
}

// Percentual de Acertos por Pergunta
fputcsv($output, []);
fputcsv($output, ['Percentual de Acertos por Pergunta', '', '']);
fputcsv($output, ['Pergunta', 'Habilidade BNCC', 'Percentual de Acertos']);
if ($formulario_id) {
    $query = "SELECT pergunta_texto, bncc_habilidade, resposta_correta
              FROM perguntas_formulario
              WHERE formulario_id = '$formulario_id'";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pergunta = $row['pergunta_texto'];
            $pergunta_escaped = $conn->real_escape_string($pergunta);
            $resposta_correta = !empty($row['resposta_correta']) ? $conn->real_escape_string($row['resposta_correta']) : null;
            if ($resposta_correta) {
                $query_acertos = "SELECT COUNT(*) AS total,
                                         SUM(CASE WHEN JSON_EXTRACT(dados_json, '$.\"$pergunta_escaped\"') = '$resposta_correta' THEN 1 ELSE 0 END) AS acertos
                                  FROM respostas_formulario
                                  WHERE formulario_id = '$formulario_id'";
                $result_acertos = $conn->query($query_acertos);
                if ($result_acertos) {
                    $acertos_row = $result_acertos->fetch_assoc();
                    $percentual = $acertos_row['total'] > 0 ? round(($acertos_row['acertos'] / $acertos_row['total']) * 100, 2) : 0;
                } else {
                    $percentual = 0;
                }
            } else {
                $percentual = 0; // Resposta correta não definida
            }
            fputcsv($output, [$pergunta, $row['bncc_habilidade'] ?: 'N/A', "$percentual%"]);
        }
    } else {
        fputcsv($output, ['Nenhuma pergunta encontrada para o formulário selecionado.', '', '']);
    }
}

fclose($output);
$conn->close();
exit;
?>