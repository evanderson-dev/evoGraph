<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</th>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #f4f4f4;
            padding: 20px;
            height: 100vh;
            position: fixed;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #333;
            margin-bottom: 5px;
        }
        .sidebar a:hover {
            background-color: #ddd;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }
        .card {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form select, .filter-form button {
            padding: 8px;
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        canvas {
            max-width: 400px;
            margin: 20px 0;
        }
        .pagination {
            margin-top: 10px;
        }
        .pagination a {
            padding: 5px 10px;
            margin: 0 2px;
            text-decoration: none;
            color: #333;
            border: 1px solid #ddd;
        }
        .pagination a.active, .pagination a:hover {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Menu</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="relatorios.php" class="active">Relatórios</a>
        <a href="configuracoes.php">Configurações</a>
        <a href="logout.php">Sair</a>
    </div>

    <div class="content">
        <h1>Relatórios</h1>

        <div class="filter-form">
            <form method="GET">
                <select name="formulario_id" onchange="this.form.submit()">
                    <option value="">Selecione o formulário</option>
                    <?php
                    require_once "db_connection.php";
                    $query = "SELECT DISTINCT formulario_id FROM respostas_formulario ORDER BY formulario_id";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        $selected = (isset($_GET['formulario_id']) && $_GET['formulario_id'] == $row['formulario_id']) ? 'selected' : '';
                        echo "<option value='{$row['formulario_id']}' $selected>{$row['formulario_id']}</option>";
                    }
                    ?>
                </select>
                <select name="pergunta" onchange="this.form.submit()">
                    <option value="">Selecione a pergunta</option>
                    <?php
                    if (isset($_GET['formulario_id']) && !empty($_GET['formulario_id'])) {
                        $formulario_id = $conn->real_escape_string($_GET['formulario_id']);
                        $query = "SELECT pergunta_texto FROM perguntas_formulario WHERE formulario_id = '$formulario_id' ORDER BY pergunta_texto";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            $selected = (isset($_GET['pergunta']) && $_GET['pergunta'] == $row['pergunta_texto']) ? 'selected' : '';
                            echo "<option value='".htmlspecialchars($row['pergunta_texto'])."' $selected>".htmlspecialchars($row['pergunta_texto'])."</option>";
                        }
                    }
                    ?>
                </select>
                <button type="submit">Filtrar</button>
                <button type="button" onclick="exportToCSV()">Exportar CSV</button>
            </form>
        </div>

        <?php
        $formulario_id = isset($_GET['formulario_id']) ? $conn->real_escape_string($_GET['formulario_id']) : '';
        ?>

        <!-- Média por série -->
        <div class="card">
            <h2>Média por Série</h2>
            <table>
                <thead>
                    <tr>
                        <th>Série</th>
                        <th>Média</th>
                        <th>Total Alunos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT JSON_EXTRACT(dados_json, '$.Série') AS serie,
                                    AVG(pontuacao) AS media_pontuacao,
                                    COUNT(*) AS total_alunos
                             FROM respostas_formulario
                             ".($formulario_id ? "WHERE formulario_id = '$formulario_id'" : "")."
                             GROUP BY serie
                             ORDER BY serie";
                    $result = $conn->query($query);
                    $series = [];
                    $medias = [];
                    while ($row = $result->fetch_assoc()) {
                        $serie = $row['serie'] ? trim($row['serie'], '"') : 'Não informada';
                        $series[] = $serie;
                        $medias[] = round($row['media_pontuacao'], 2);
                        echo "<tr>
                                <td>$serie</td>
                                <td>".round($row['media_pontuacao'], 2)."</td>
                                <td>{$row['total_alunos']}</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
            <canvas id="graficoMedia"></canvas>
        </div>

        <!-- Percentual de acertos por pergunta -->
        <div class="card">
            <h2>Percentual de Acertos por Pergunta</h2>
            <table>
                <thead>
                    <tr>
                        <th>Pergunta</th>
                        <th>Habilidade BNCC</th>
                        <th>Percentual de Acertos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($formulario_id) {
                        $query = "SELECT pergunta_texto, bncc_habilidade, resposta_correta
                                 FROM perguntas_formulario
                                 WHERE formulario_id = '$formulario_id'";
                        $result = $conn->query($query);
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
                                $acertos_row = $result_acertos->fetch_assoc();
                                $percentual = $acertos_row['total'] > 0 ? round(($acertos_row['acertos'] / $acertos_row['total']) * 100, 2) : 0;
                            } else {
                                $percentual = 0;
                            }
                            
                            echo "<tr>
                                    <td>".htmlspecialchars($pergunta)."</td>
                                    <td>".($row['bncc_habilidade'] ?: 'N/A')."</td>
                                    <td>$percentual%</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Selecione um formulário para ver os resultados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Distribuição de respostas -->
        <?php
        if (isset($_GET['pergunta']) && $formulario_id) {
            $pergunta = $conn->real_escape_string($_GET['pergunta']);
            $query = "SELECT JSON_EXTRACT(dados_json, '$.\"$pergunta\"') AS resposta,
                            COUNT(*) AS total
                     FROM respostas_formulario
                     WHERE formulario_id = '$formulario_id'
                     GROUP BY resposta";
            $result = $conn->query($query);
            $respostas = [];
            $quantidades = [];
            while ($row = $result->fetch_assoc()) {
                $resposta = $row['resposta'] ? trim($row['resposta'], '"') : 'Não respondida';
                $respostas[] = $resposta;
                $quantidades[] = $row['total'];
            }
            ?>
            <div class="card">
                <h2>Distribuição de Respostas: <?php echo htmlspecialchars($_GET['pergunta']); ?></h2>
                <canvas id="graficoRespostas"></canvas>
            </div>
            <?php
        }
        ?>

        <!-- Alunos com pontuação abaixo de 7 -->
        <div class="card">
            <h2>Alunos com Pontuação Abaixo de 7</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Série</th>
                        <th>Pontuação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $limite = 10;
                    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                    $offset = ($pagina - 1) * $limite;
                    
                    $query = "SELECT rf.email, rf.pontuacao, JSON_EXTRACT(dados_json, '$.Série') AS serie,
                                    CONCAT(a.nome, ' ', a.sobrenome) AS nome_completo
                             FROM respostas_formulario rf
                             LEFT JOIN alunos a ON rf.aluno_id = a.id
                             WHERE rf.pontuacao < 7.0
                             ".($formulario_id ? "AND rf.formulario_id = '$formulario_id'" : "")."
                             ORDER BY rf.pontuacao
                             LIMIT $limite OFFSET $offset";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        $serie = $row['serie'] ? trim($row['serie'], '"') : 'Não informada';
                        echo "<tr>
                                <td>".($row['nome_completo'] ?: 'N/A')."</td>
                                <td>".htmlspecialchars($row['email'])."</td>
                                <td>$serie</td>
                                <td>{$row['pontuacao']}</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <?php
            $query_total = "SELECT COUNT(*) AS total
                          FROM respostas_formulario rf
                          WHERE rf.pontuacao < 7.0
                          ".($formulario_id ? "AND rf.formulario_id = '$formulario_id'" : "");
            $total_result = $conn->query($query_total);
            $total = $total_result->fetch_assoc()['total'];
            $total_paginas = ceil($total / $limite);
            
            if ($total_paginas > 1) {
                echo '<div class="pagination">';
                for ($i = 1; $i <= $total_paginas; $i++) {
                    $active = $i == $pagina ? 'active' : '';
                    echo "<a class='$active' href='relatorios.php?formulario_id=".urlencode($formulario_id)."&pagina=$i'>$i</a>";
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <script>
        // Gráfico de Média por Série
        const ctxMedia = document.getElementById('graficoMedia').getContext('2d');
        new Chart(ctxMedia, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($series); ?>,
                datasets: [{
                    label: 'Média de Pontuação',
                    data: <?php echo json_encode($medias); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10
                    }
                }
            }
        });

        // Gráfico de Distribuição de Respostas
        <?php if (isset($_GET['pergunta']) && $formulario_id) { ?>
        const ctxRespostas = document.getElementById('graficoRespostas').getContext('2d');
        new Chart(ctxRespostas, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($respostas); ?>,
                datasets: [{
                    data: <?php echo json_encode($quantidades); ?>,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
        <?php } ?>

        function exportToCSV() {
            const formulario_id = document.querySelector('select[name="formulario_id"]').value;
            if (!formulario_id) {
                alert('Por favor, selecione um formulário antes de exportar.');
                return;
            }
            window.location.href = 'export_csv.php?formulario_id=' + encodeURIComponent(formulario_id);
        }
    </script>
</body>
</html>

filepath: src/export_csv.php

<?php
require_once "db_connection.php";

if (!isset($_GET['formulario_id']) || empty($_GET['formulario_id'])) {
    die("Formulário não especificado.");
}

$formulario_id = $conn->real_escape_string($_GET['formulario_id']);

// Cabeçalhos para download do CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="relatorio_' . $formulario_id . '.csv"');

// Cria um arquivo CSV
$output = fopen('php://output', 'w');

// Adiciona BOM para UTF-8 (necessário para Excel reconhecer acentos corretamente)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalhos do CSV
fputcsv($output, ['Email', 'Série', 'Pontuação', 'Data de Submissão']);

// Busca os dados
$query = "SELECT email, JSON_EXTRACT(dados_json, '$.Série') AS serie, pontuacao, data_submissao
         FROM respostas_formulario
         WHERE formulario_id = '$formulario_id'
         ORDER BY data_submissao";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $serie = $row['serie'] ? trim($row['serie'], '"') : 'Não informada';
    fputcsv($output, [
        $row['email'],
        $serie,
        $row['pontuacao'],
        $row['data_submissao']
    ]);
}

fclose($output);
exit();
?>

filepath: src/db_connection.php

<?php
$host = 'localhost';
$dbname = 'seu_banco_de_dados';
$username = 'seu_usuario';
$password = 'sua_senha';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

filepath: src/import_google_form.php

<?php
require_once "db_connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $formulario_id = $_POST['formulario_id'];

    // Validação básica
    if (empty($formulario_id)) {
        die("ID do formulário é obrigatório.");
    }

    if (($handle = fopen($file, 'r')) !== FALSE) {
        // Ignora o cabeçalho
        $header = fgetcsv($handle, 1000, ',');
        
        // Prepara as queries
        $insert_resposta = $conn->prepare("INSERT INTO respostas_formulario (formulario_id, email, dados_json, pontuacao, data_submissao) VALUES (?, ?, ?, ?, ?)");
        $insert_pergunta = $conn->prepare("INSERT INTO perguntas_formulario (formulario_id, pergunta_texto, bncc_habilidade, resposta_correta) VALUES (?, ?, ?, ?)");
        
        // Mapear índices das colunas do CSV
        $email_idx = array_search('Endereço de e-mail', $header);
        $data_idx = array_search('Carimbo de data/hora', $header);
        $serie_idx = false;
        
        // Encontrar coluna da série (pode ter nomes variados)
        foreach ($header as $idx => $col) {
            if (stripos($col, 'série') !== false || stripos($col, 'ano') !== false) {
                $serie_idx = $idx;
                break;
            }
        }

        // Processar perguntas (excluindo email, data e série)
        $perguntas = [];
        foreach ($header as $idx => $col) {
            if ($idx != $email_idx && $idx != $data_idx && $idx != $serie_idx) {
                $perguntas[] = ['texto' => $col, 'index' => $idx];
            }
        }

        // Inserir perguntas no banco
        foreach ($perguntas as $pergunta) {
            // Aqui você pode definir a habilidade BNCC e resposta correta manualmente ou via interface
            $bncc_habilidade = ''; // Ajuste conforme necessário
            $resposta_correta = ''; // Ajuste conforme necessário
            $insert_pergunta->execute([$formulario_id, $pergunta['texto'], $bncc_habilidade, $resposta_correta]);
        }

        // Processar respostas
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $email = $data[$email_idx];
            $data_submissao = date('Y-m-d H:i:s', strtotime($data[$data_idx]));
            $serie = $serie_idx !== false ? $data[$serie_idx] : '';
            
            // Criar JSON com respostas
            $dados_json = [];
            if ($serie) {
                $dados_json['Série'] = $serie;
            }
            foreach ($perguntas as $pergunta) {
                $dados_json[$pergunta['texto']] = $data[$pergunta['index']];
            }
            
            // Calcular pontuação (exemplo simples, ajuste conforme necessário)
            $pontuacao = 0;
            foreach ($perguntas as $pergunta) {
                $resposta = $data[$pergunta['index']];
                // Lógica para verificar se a resposta está correta
                // Exemplo: se resposta correta estiver definida, comparar
                $query = "SELECT resposta_correta FROM perguntas_formulario WHERE formulario_id = ? AND pergunta_texto = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$formulario_id, $pergunta['texto']]);
                $resposta_correta = $stmt->fetchColumn();
                
                if ($resposta_correta && $resposta === $resposta_correta) {
                    $pontuacao += 2; // Exemplo: 2 pontos por resposta correta
                }
            }
            
            // Inserir resposta
            $insert_resposta->execute([
                $formulario_id,
                $email,
                json_encode($dados_json),
                $pontuacao,
                $data_submissao
            ]);
        }
        
        fclose($handle);
        echo "Importação concluída com sucesso!";
    } else {
        echo "Erro ao abrir o arquivo CSV.";
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Importar Formulário Google</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 20px auto;
                padding: 20px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                display: block;
                margin-bottom: 5px;
            }
            input[type="text"], input[type="file"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            button {
                background-color: #007bff;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <h1>Importar Formulário Google</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="formulario_id">ID do Formulário:</label>
                <input type="text" id="formulario_id" name="formulario_id" required>
            </div>
            <div class="form-group">
                <label for="csv_file">Arquivo CSV:</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
            </div>
            <button type="submit">Importar</button>
        </form>
    </body>
    </html>
    <?php
}
?>