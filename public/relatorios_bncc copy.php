<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/dashboard.css" />
    <link rel="stylesheet" href="./assets/css/sidebar.css" />
    <link rel="stylesheet" href="./assets/css/relatorios_bncc.css" />
    <link rel="stylesheet" href="./assets/css/relatorio-google.css" />
    <link rel="stylesheet" href="./assets/css/modal-add-funcionario.css" />
    <link rel="stylesheet" href="./assets/css/modal-add-turma.css" />
    <link rel="stylesheet" href="./assets/css/modal-add-aluno.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Relatórios BNCC</title>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script>if (typeof jQuery === 'undefined') { document.write('<script src="./assets/js/jquery-3.6.0.min.js"><\/script>'); }</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" crossorigin="anonymous"></script>    <script>if (typeof Chart === 'undefined') { document.write('<script src="./assets/js/chart.min.js"><\/script>'); }</script>
    <script src="./assets/js/relatorios_bncc.js"></script>
</head>
<body>
    <header>
        <div class="menu-toggle" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h1>evoGraph</h1>
        <div class="icons">
            <i class="fas fa-envelope"></i>
            <i class="fas fa-bell"></i>
            <i class="fas fa-cog"></i>
            <i class="fas fa-user"></i>
        </div>
    </header>

    <div class="container">
        <div class="sidebar" id="sidebar">
            <a href="dashboard.php" class="sidebar-active"><i class="fa-solid fa-house"></i>Home</a>
            <a href="relatorio-google.php"><i class="fa-solid fa-chart-bar"></i>Importar Relatório</a>
            <a href="relatorios_bncc.php"><i class="fa-solid fa-chart-bar"></i>Visualizar Relatório</a>
            <a href="#" onclick="exportarCSV()"><i class="fa-solid fa-chart-bar"></i>Exportar Relatório (CSV)</a>
            <a href="my_profile.php"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <a href="#" onclick="openAddFuncionarioModal()"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                </div>
            </div>
            <a href="funcionarios.php"><i class="fa-solid fa-users"></i>Funcionários</a>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
        </div>

        <div class="main-content" id="main-content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Relatórios BNCC</span>
            </div>

            <section class="relatorio-section">
                <h2>Relatórios BNCC</h2>
                <div id="message-box"></div>

                <?php
                require_once "db_connection.php";
                $formulario_id = isset($_GET['formulario_id']) ? $conn->real_escape_string($_GET['formulario_id']) : '';
                ?>

                <form class="filter-form" method="GET">
                    <label for="formulario_id">Filtrar por Formulário:</label>
                    <select name="formulario_id" id="formulario_id" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <?php
                        $query = "SELECT DISTINCT formulario_id FROM respostas_formulario ORDER BY formulario_id";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($formulario_id === $row['formulario_id']) ? 'selected' : '';
                            echo "<option value='{$row['formulario_id']}' $selected>{$row['formulario_id']}</option>";
                        }
                        ?>
                    </select>
                    <label for="pergunta">Selecionar Pergunta:</label>
                    <select name="pergunta" id="pergunta" onchange="this.form.submit()">
                        <option value="">Nenhuma</option>
                        <?php
                        if ($formulario_id) {
                            $query = "SELECT pergunta_texto FROM perguntas_formulario WHERE formulario_id = '$formulario_id' ORDER BY pergunta_texto";
                            $result = $conn->query($query);
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $selected = (isset($_GET['pergunta']) && $_GET['pergunta'] === $row['pergunta_texto']) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($row['pergunta_texto']) . "' $selected>" . htmlspecialchars($row['pergunta_texto']) . "</option>";
                                }
                            } else {
                                echo "<option value='' disabled>Nenhuma pergunta encontrada</option>";
                            }
                        }
                        ?>
                    </select>
                    <button type="submit">Filtrar</button>
                    <button type="button" onclick="exportarCSV()">Exportar como CSV</button>
                </form>

                <!-- ################################### -->

                <style>
                .relatorio-section {
                    margin: 20px 0;
                }

                .media-por-serie-container {
                    text-align: center;
                }

                .media-por-serie-wrapper {
                    display: flex;
                    justify-content: center;
                    align-items: flex-start;
                    gap: 20px;
                    flex-wrap: wrap;
                }

                .media-table-container {
                    flex: 1;
                    min-width: 300px;
                    max-width: 400px;
                }

                #media-por-serie-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 0 auto;
                }

                #media-por-serie-table th,
                #media-por-serie-table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: center;
                }

                #media-por-serie-table th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                }

                .media-chart-container {
                    flex: 1;
                    min-width: 400px;
                    max-width: 500px;
                }

                @media (max-width: 768px) {
                    .media-por-serie-wrapper {
                        flex-direction: column;
                        align-items: center;
                    }

                    .media-table-container,
                    .media-chart-container {
                        max-width: 100%;
                    }
                }
                </style>

                <div class="relatorio-section media-por-serie-container">
                    <h3>Média de Pontuação por Série</h3>
                    <div class="media-por-serie-wrapper">
                        <!-- Tabela de Médias -->
                        <div class="media-table-container">
                            <table id="media-por-serie-table">
                                <thead>
                                    <tr>
                                        <th>Série</th>
                                        <th>Média de Pontuação</th>
                                    </tr>
                                </thead>
                                <tbody id="media-por-serie-table-body">
                                    <?php
                                    if ($formulario_id) {
                                        // Buscar todas as respostas para calcular a média manualmente
                                        $query_medias = "SELECT JSON_EXTRACT(dados_json, '$.\"Série:\"') AS serie, 
                                                        JSON_EXTRACT(dados_json, '$.\"Pontuação\"') AS pontuacao
                                                FROM respostas_formulario
                                                WHERE formulario_id = '$formulario_id'
                                                ORDER BY serie";
                                        $result_medias = $conn->query($query_medias);
                                        $series_medias = [];
                                        $medias = [];
                                        $pontuacoes_por_serie = [];

                                        if ($result_medias && $result_medias->num_rows > 0) {
                                            // Agrupar pontuações por série
                                            while ($row = $result_medias->fetch_assoc()) {
                                                $serie = $row['serie'] ? trim($row['serie'], '"') : 'Não Informada';
                                                $pontuacao = $row['pontuacao'] ? trim($row['pontuacao'], '"') : '0 / 10';
                                                // Extrair o número antes de " / 10"
                                                $pontuacao_valor = (float) explode(' / ', $pontuacao)[0];
                                                if (!isset($pontuacoes_por_serie[$serie])) {
                                                    $pontuacoes_por_serie[$serie] = [];
                                                }
                                                $pontuacoes_por_serie[$serie][] = $pontuacao_valor;
                                            }

                                            // Calcular médias e preencher a tabela
                                            foreach ($pontuacoes_por_serie as $serie => $pontuacoes) {
                                                $media = count($pontuacoes) > 0 ? array_sum($pontuacoes) / count($pontuacoes) : 0;
                                                $media = round($media, 2);
                                                $series_medias[] = $serie;
                                                $medias[] = $media;
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($serie) . "</td>";
                                                echo "<td>$media</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='2'>Nenhuma média encontrada para o formulário selecionado.</td></tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='2'>Selecione um formulário para ver as médias.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Gráfico de Barras -->
                        <div class="media-chart-container">
                            <canvas id="mediaPorSerieChart" width="400" height="300"
                                    data-series='<?php echo json_encode($series_medias); ?>'
                                    data-medias='<?php echo json_encode($medias); ?>'></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- ################################### -->

                <div class="relatorio-section">
                <h3>Percentual de Acertos por Série</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Pergunta</th>
                            <th>Habilidade BNCC</th>
                            <?php
                            if ($formulario_id) {
                                // Buscar séries/salas distintas
                                $query_series = "SELECT DISTINCT JSON_EXTRACT(dados_json, '$.\"Série:\"') AS serie
                                                FROM respostas_formulario
                                                WHERE formulario_id = '$formulario_id'
                                                ORDER BY serie";
                                $result_series = $conn->query($query_series);
                                $series = [];
                                while ($row = $result_series->fetch_assoc()) {
                                    $serie = $row['serie'] ? trim($row['serie'], '"') : 'Não Informada';
                                    $series[] = $serie;
                                    echo "<th>Série $serie</th>";
                                }
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($formulario_id) {
                            // Buscar perguntas do formulário
                            $query = "SELECT pergunta_texto, bncc_habilidade, resposta_correta
                                    FROM perguntas_formulario
                                    WHERE formulario_id = '$formulario_id'";
                            $result = $conn->query($query);
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $pergunta = $row['pergunta_texto'];
                                    $pergunta_escaped = $conn->real_escape_string($pergunta);
                                    $resposta_correta = !empty($row['resposta_correta']) ? $conn->real_escape_string($row['resposta_correta']) : null;

                                    // Log para debug
                                    error_log("Pergunta: $pergunta_escaped, Resposta Correta: $resposta_correta");

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($pergunta) . "</td>";
                                    echo "<td>" . ($row['bncc_habilidade'] ?: 'N/A') . "</td>";

                                    // Calcular percentual por série
                                    foreach ($series as $serie) {
                                        $serie_escaped = $conn->real_escape_string($serie);
                                        if ($resposta_correta) {
                                            // Ajustar a query para usar TRIM e LOWER
                                            $query_acertos = "SELECT COUNT(*) AS total,
                                                            SUM(CASE WHEN LOWER(JSON_EXTRACT(dados_json, '$.\"$pergunta_escaped\"')) = LOWER('\"$resposta_correta\"') THEN 1 ELSE 0 END) AS acertos
                                                    FROM respostas_formulario
                                                    WHERE formulario_id = '$formulario_id'
                                                    AND TRIM(JSON_EXTRACT(dados_json, '$.\"Série:\"')) = '\"$serie_escaped\"'";
                                            $result_acertos = $conn->query($query_acertos);
                                            if ($result_acertos) {
                                                $acertos_row = $result_acertos->fetch_assoc();
                                                $total = $acertos_row['total'];
                                                $acertos = $acertos_row['acertos'];
                                                $percentual = $total > 0 ? round(($acertos / $total) * 100, 2) : 0;

                                                // Log para debug
                                                error_log("Série: $serie_escaped, Total: $total, Acertos: $acertos, Percentual: $percentual");
                                            } else {
                                                $percentual = 0;
                                                error_log("Erro na query de acertos: " . $conn->error);
                                            }
                                        } else {
                                            $percentual = 0;
                                            error_log("Resposta correta não definida para a pergunta: $pergunta_escaped");
                                        }
                                        echo "<td>$percentual%</td>";
                                    }

                                    echo "</tr>";
                                }
                            } else {
                                $colspan = count($series) + 2;
                                echo "<tr><td colspan='$colspan'>Nenhuma pergunta encontrada para o formulário selecionado.</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>Selecione um formulário para ver as perguntas.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

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
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $resposta = $row['resposta'] ? trim($row['resposta'], '"') : 'Não Respondida';
                            $respostas[] = $resposta;
                            $quantidades[] = $row['total'];
                        }
                    } else {
                        $respostas = ['Nenhuma resposta'];
                        $quantidades = [1];
                    }
                ?>
                <div class="relatorio-section">
                    <h3>Distribuição de Respostas: <?php echo htmlspecialchars($_GET['pergunta']); ?></h3>
                    <canvas id="graficoRespostas" data-respostas='<?php echo json_encode($respostas); ?>' data-quantidades='<?php echo json_encode($quantidades); ?>'></canvas>
                </div>
                <?php } ?>

                <?php if ($formulario_id) { ?>
                <div class="relatorio-section">
                    <h3>Alunos com Pontuação Abaixo de 7.0</h3>
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
                            $query = "SELECT rf.email, rf.pontuacao, JSON_EXTRACT(dados_json, '$.\"Série:\"') AS serie,
                                             CONCAT(a.nome, ' ', a.sobrenome) AS nome_completo
                                      FROM respostas_formulario rf
                                      LEFT JOIN alunos a ON rf.aluno_id = a.id
                                      WHERE rf.pontuacao < 7.0 AND rf.formulario_id = '$formulario_id'
                                      ORDER BY rf.pontuacao
                                      LIMIT $limite OFFSET $offset";
                            $result = $conn->query($query);
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $serie = $row['serie'] ? trim($row['serie'], '"') : 'Não Informada';
                                    echo "<tr>
                                            <td>" . ($row['nome_completo'] ?: 'N/A') . "</td>
                                            <td>" . htmlspecialchars($row['email']) . "</td>
                                            <td>$serie</td>
                                            <td>" . $row['pontuacao'] . "</td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>Nenhum aluno com pontuação abaixo de 7.0.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php
                    $query_total = "SELECT COUNT(*) AS total
                                    FROM respostas_formulario rf
                                    WHERE rf.pontuacao < 7.0 AND rf.formulario_id = '$formulario_id'";
                    $total_result = $conn->query($query_total);
                    $total = $total_result->fetch_assoc()['total'];
                    $total_paginas = ceil($total / $limite);
                    if ($total_paginas > 1) {
                        echo '<div class="paginacao">';
                        for ($i = 1; $i <= $total_paginas; $i++) {
                            $active = $i == $pagina ? 'active' : '';
                            echo "<a class='$active' href='relatorios_bncc.php?formulario_id=" . urlencode($formulario_id) . "&pergunta=" . urlencode(isset($_GET['pergunta']) ? $_GET['pergunta'] : '') . "&pagina=$i'>$i</a> ";
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
                <?php } else { ?>
                <div class="relatorio-section">
                    <div class="placeholder">Selecione um formulário para ver os alunos com baixo desempenho.</div>
                </div>
                <?php } ?>
            </section>
        </div>
    </div>

    <div id="modal-cadastrar-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>

    <footer>
        <p>© 2025 evoGraph. All rights reserved.</p>
    </footer>

    <script src="./assets/js/utils.js"></script>
    <script src="./assets/js/modal-add-funcionario.js"></script>
    <script src="./assets/js/modal-add-turma.js"></script>
    <script src="./assets/js/modal-add-aluno.js"></script>
    <script src="./assets/js/my-profile.js"></script>
    <script src="./assets/js/dashboard.js"></script>
    <script src="./assets/js/ajax.js"></script>
</body>
</html>