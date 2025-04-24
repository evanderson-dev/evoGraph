<?php
require_once "db_connection.php";

// Receber parâmetros
$formulario_id = isset($_GET['formulario_id']) ? $conn->real_escape_string($_GET['formulario_id']) : '';
$serie = isset($_GET['serie']) ? $conn->real_escape_string($_GET['serie']) : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

if (!$formulario_id) {
    echo '<table id="alunos-acima-7-table"><thead><tr><th>Nome</th><th>Email</th><th>Série</th><th>Pontuação</th></tr></thead><tbody><tr><td colspan="4">Selecione um formulário para ver os alunos com alto desempenho.</td></tr></tbody></table>';
    exit;
}

// Configurações de paginação
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Construir a query com filtro por série, se aplicável
$query = "SELECT rf.email, rf.pontuacao, JSON_EXTRACT(dados_json, '$.\"Série:\"') AS serie,
                 CONCAT(a.nome, ' ', a.sobrenome) AS nome_completo
          FROM respostas_formulario rf
          LEFT JOIN alunos a ON rf.aluno_id = a.id
          WHERE rf.pontuacao > 7.0 AND rf.formulario_id = '$formulario_id'";
if ($serie) {
    $query .= " AND TRIM(JSON_EXTRACT(dados_json, '$.\"Série:\"')) = '\"$serie\"'";
}
$query .= " ORDER BY rf.pontuacao DESC LIMIT $limite OFFSET $offset";
$result = $conn->query($query);

// Construir a tabela
$output = '<table id="alunos-acima-7-table"><thead><tr><th>Nome</th><th>Email</th><th>Série</th><th>Pontuação</th></tr></thead><tbody>';
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $serie_display = $row['serie'] ? trim($row['serie'], '"') : 'Não Informada';
        $output .= "<tr>
                      <td>" . ($row['nome_completo'] ?: 'N/A') . "</td>
                      <td>" . htmlspecialchars($row['email']) . "</td>
                      <td>$serie_display</td>
                      <td>" . $row['pontuacao'] . "</td>
                    </tr>";
    }
} else {
    $output .= '<tr><td colspan="4">Nenhum aluno com pontuação acima de 7.0 para a série selecionada.</td></tr>';
}
$output .= '</tbody></table>';

// Calcular total de páginas para a paginação
$query_total = "SELECT COUNT(*) AS total
                FROM respostas_formulario rf
                WHERE rf.pontuacao > 7.0 AND rf.formulario_id = '$formulario_id'";
if ($serie) {
    $query_total .= " AND TRIM(JSON_EXTRACT(dados_json, '$.\"Série:\"')) = '\"$serie\"'";
}
$total_result = $conn->query($query_total);
$total = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total / $limite);

// Construir a paginação
if ($total_paginas > 1) {
    $output .= '<div class="paginacao">';
    for ($i = 1; $i <= $total_paginas; $i++) {
        $active = $i == $pagina ? 'active' : '';
        $output .= "<button class='pagination-btn $active' data-page='$i'>$i</button> ";
    }
    $output .= '</div>';
}

echo $output;
$conn->close();
?>