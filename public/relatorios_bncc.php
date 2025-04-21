<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="./assets/css/dashboard.css" />
    <link rel="stylesheet" href="./assets/css/sidebar.css" />
    <link rel="stylesheet" href="./assets/css/relatorio-google.css" />
    <link rel="stylesheet" href="./assets/css/modal-add-funcionario.css" />
    <link rel="stylesheet" href="./assets/css/modal-add-turma.css" />
    <link rel="stylesheet" href="./assets/css/modal-add-aluno.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Relatórios BNCC</title>
    <style>
        .mensagem-sucesso {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .mensagem-erro {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .relatorio-section {
            margin-bottom: 30px;
        }
        .relatorio-section h3 {
            margin-bottom: 15px;
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
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form select, .filter-form button {
            padding: 8px;
            margin-right: 10px;
        }
        canvas {
            max-width: 600px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="menu-toggle" onclick="toggleSidebar()">
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
    <!-- Fim do Header -->

    <div class="container">
        <!-- SIDEBAR -->
        <div class="sidebar" id="sidebar">
            <a href="dashboard.php" class="sidebar-active"><i class="fa-solid fa-house"></i>Home</a>
            <a href="relatorio-google.php"><i class="fa-solid fa-chart-bar"></i>Importar Relatório</a>
            <a href="relatorios_bncc.php"><i class="fa-solid fa-chart-bar"></i>Visualizar Relatório</a>
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
        <!-- FIM SIDEBAR -->

        <div class="main-content" id="main-content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Relatórios BNCC</span>
            </div>

            <section class="relatorio-section">
                <h2>Relatórios BNCC</h2>
                <div id="message-box"></div>

                <!-- Filtro -->
                <form class="filter-form" method="GET">
                    <label for="formulario_id">Filtrar por Formulário:</label>
                    <select name="formulario_id" id="formulario_id">
                        <option value="">Todos</option>
                        <?php
                        require_once "db_connection.php";
                        $query = "SELECT DISTINCT formulario_id FROM respostas_formulario ORDER BY formulario_id";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            $selected = (isset($_GET['formulario_id']) && $_GET['formulario_id'] === $row['formulario_id']) ? 'selected' : '';
                            echo "<option value='{$row['formulario_id']}' $selected>{$row['formulario_id']}</option>";
                        }
                        ?>
                    </select>
                    <button type="submit">Filtrar</button>
                    <button type="button" onclick="exportarCSV()">Exportar como CSV</button>
                </form>

                <!-- Média por Série -->
                <div class="relatorio-section">
                    <h3>Média de Pontuação por Série</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Série</th>
                                <th>Média de Pontuação</th>
                                <th>Total de Alunos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $formulario_id = isset($_GET['formulario_id']) ? $conn->real_escape_string($_GET['formulario_id']) : '';
                            $query = "SELECT JSON_EXTRACT(dados_json, '$.\"Série:\"') AS serie,
                                             AVG(pontuacao) AS media_pontuacao,
                                             COUNT(*) AS total_alunos
                                      FROM respostas_formulario
                                      " . ($formulario_id ? "WHERE formulario_id = '$formulario_id'" : "") . "
                                      GROUP BY serie
                                      ORDER BY serie";
                            $result = $conn->query($query);
                            $series = [];
                            $medias = [];
                            while ($row = $result->fetch_assoc()) {
                                $serie = $row['serie'] ? trim($row['serie'], '"') : 'Não Informada';
                                $series[] = $serie;
                                $medias[] = round($row['media_pontuacao'], 2);
                                echo "<tr>
                                        <td>$serie</td>
                                        <td>" . round($row['media_pontuacao'], 2) . "</td>
                                        <td>{$row['total_alunos']}</td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <canvas id="graficoMediaSerie"></canvas>
                </div>

                <!-- Percentual de Acertos por Pergunta -->
                <div class="relatorio-section">
                    <h3>Percentual de Acertos por Pergunta</h3>
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
                                $query = "SELECT pergunta_texto, bncc_habilidade
                                          FROM perguntas_formulario
                                          WHERE formulario_id = '$formulario_id'";
                                $result = $conn->query($query);
                                while ($row = $result->fetch_assoc()) {
                                    $pergunta = $row['pergunta_texto'];
                                    $pergunta_escaped = $conn->real_escape_string($pergunta);
                                    // Supondo que respostas corretas sejam marcadas como "Correto" no JSON
                                    $query_acertos = "SELECT COUNT(*) AS total,
                                                             SUM(CASE WHEN JSON_EXTRACT(dados_json, '$.\"$pergunta_escaped\"') = '\"Correto\"' THEN 1 ELSE 0 END) AS acertos
                                                      FROM respostas_formulario
                                                      WHERE formulario_id = '$formulario_id'";
                                    $result_acertos = $conn->query($query_acertos);
                                    $acertos_row = $result_acertos->fetch_assoc();
                                    $percentual = $acertos_row['total'] > 0 ? round(($acertos_row['acertos'] / $acertos_row['total']) * 100, 2) : 0;
                                    echo "<tr>
                                            <td>" . htmlspecialchars($pergunta) . "</td>
                                            <td>" . ($row['bncc_habilidade'] ?: 'N/A') . "</td>
                                            <td>$percentual%</td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>Selecione um formulário para ver as perguntas.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Modals -->
    <div id="modal-cadastrar-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2025 evoGraph. All rights reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./assets/js/utils.js"></script>
    <script src="./assets/js/modal-add-funcionario.js"></script>
    <script src="./assets/js/modal-add-turma.js"></script>
    <script src="./assets/js/modal-add-aluno.js"></script>
    <script src="./assets/js/my-profile.js"></script>
    <script src="./assets/js/dashboard.js"></script>
    <script src="./assets/js/ajax.js"></script>

    <script>
        // Gráfico de Média por Série
        const ctx = document.getElementById('graficoMediaSerie').getContext('2d');
        new Chart(ctx, {
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

        // Função para exportar como CSV
        function exportarCSV() {
            const formulario_id = document.getElementById('formulario_id').value;
            window.location.href = 'exportar_relatorio.php?formulario_id=' + encodeURIComponent(formulario_id);
        }

        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
            localStorage.setItem('sidebarActive', sidebar.classList.contains('active'));
        }

        $(document).ready(function() {
            if (localStorage.getItem('sidebarActive') === 'true') {
                $('#sidebar').addClass('active');
                $('#main-content').addClass('shifted');
            }
            $('#menu-toggle').on('click', function() {
                toggleSidebar();
            });
            $('.sidebar-toggle').on('click', function(e) {
                e.preventDefault();
                const $submenu = $(this).next('.submenu');
                const $toggleIcon = $(this).find('.submenu-toggle');
                $submenu.slideToggle(200);
                $toggleIcon.toggleClass('open');
            });
        });
    </script>
</body>
</html>