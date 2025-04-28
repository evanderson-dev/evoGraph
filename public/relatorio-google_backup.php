<?php
session_start();

// Definir os cargos permitidos para acessar a página
$allowed_cargos = ['Professor', 'Coordenador', 'Diretor', 'Administrador'];

// Verificar se o usuário está logado e tem um cargo permitido
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["cargo"]) || !in_array($_SESSION["cargo"], $allowed_cargos)) {
    header('Location: index.php');
    exit;
}

// Definir a variável $cargo para uso no HTML
$cargo = $_SESSION["cargo"];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/sidebar.css" />
    <link rel="stylesheet" href="./assets/css/relatorio-google.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-add-funcionario.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-add-turma.css" />
    <link rel="stylesheet" href="./assets/css/modals/modal-add-aluno.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>evoGraph - Relatório Google</title>    
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
            <a href="./src/Views/profile/my_profile.php"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>

            <?php if (in_array($cargo, ['Coordenador', 'Diretor', 'Administrador'])): ?>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <a href="#" onclick="openAddFuncionarioModal()"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                </div>
            </div>
            <a href="funcionarios.php"><i class="fa-solid fa-users"></i>Funcionários</a>
            <?php endif; ?>
            
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
        </div>
        <!-- FIM SIDEBAR -->

        <div class="main-content" id="main-content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Formulário do Google Forms</span>
            </div>

            <section class="relatorio-section">
                <div id="message-box"></div>
                <div class="profile-form">
                    <form id="profile-form" enctype="multipart/form-data">
                        <input type="hidden" name="save_profile" value="1">
                        
                        <div class="form-group">
                            <div>
                                <label for="googleSheetLink">Link da planilha do Google:</label>
                                <input type="text" id="googleSheetLink" placeholder="https://docs.google.com/spreadsheets/d/..." required>
                            </div>
                            <div>
                                <label for="bnccHabilidade">Habilidade BNCC (Opcional):</label>
                                <input type="text" id="bnccHabilidade" placeholder="Ex.: EF06GE10">
                            </div>
                            <div>
                                <label for="formularioId">Identificador do formulário:</label>
                                <input type="text" id="formularioId" placeholder="Identificador do formulário" required>
                            </div>
                            <div>
                                <label>&nbsp;</label>
                                <button type="button" class="btn-carregar" onclick="carregarPlanilha()">Carregar</button>
                            </div>
                            <div>
                                <label>&nbsp;</label>
                                <button type="button" class="btn-importar" onclick="importarParaBanco()">Importar para o banco</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div>
                                <label for="formularioIdDelete">Excluir formulário:</label>
                                <select id="formularioIdDelete">
                                    <option value="">Selecione um formulário</option>
                                    <?php
                                    require_once "db_connection.php";
                                    $query = "SELECT DISTINCT formulario_id FROM respostas_formulario ORDER BY formulario_id";
                                    $result = $conn->query($query);
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $form_id = htmlspecialchars($row['formulario_id']);
                                            echo "<option value=\"$form_id\">$form_id</option>";
                                        }
                                    }
                                    $conn->close();
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label>&nbsp;</label>
                                <button type="button" class="btn-excluir" onclick="excluirFormulario()">Excluir</button>
                            </div>
                        </div>

                        <div class="table-container">
                            <h4>Dados Carregados da Planilha</h4>
                            <div style="overflow-x: auto;">
                                <table id="tabela-dados">
                                    <thead></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <script>
                            let dadosPlanilha = []; // Variável global para armazenar os dados
                            let perguntas = []; // Armazenar os cabeçalhos das perguntas
                            let respostasCorretas = []; // Armazenar as respostas corretas

                            function formatGoogleSheetUrl(userInputUrl) {
                                const idMatch = userInputUrl.match(/\/d\/([a-zA-Z0-9-_]+)/);
                                if (!idMatch) {
                                    alert("Link inválido! Verifique o link da planilha.");
                                    return null;
                                }

                                const sheetId = idMatch[1];
                                let url = `https://docs.google.com/spreadsheets/d/${sheetId}/export?format=csv`;
                                return url;
                            }

                            function carregarPlanilha() {
                                const urlInput = document.getElementById('googleSheetLink').value;
                                const formattedUrl = formatGoogleSheetUrl(urlInput);

                                if (!formattedUrl) return;

                                const button = document.querySelector('button[onclick="carregarPlanilha()"]');
                                button.disabled = true;
                                button.textContent = "Carregando...";

                                fetch(formattedUrl)
                                    .then(res => res.text())
                                    .then(csv => {
                                        Papa.parse(csv, {
                                            header: true,
                                            skipEmptyLines: true,
                                            complete: function(results) {
                                                dadosPlanilha = results.data;
                                                const thead = document.querySelector("#tabela-dados thead");
                                                const tbody = document.querySelector("#tabela-dados tbody");

                                                // Limpa tabela
                                                thead.innerHTML = '';
                                                tbody.innerHTML = '';

                                                if (dadosPlanilha.length === 0) {
                                                    alert("Nenhum dado encontrado na planilha.");
                                                    button.disabled = false;
                                                    button.textContent = "Carregar";
                                                    return;
                                                }

                                                // Identificar cabeçalhos
                                                const headers = Object.keys(dadosPlanilha[0]);
                                                console.log("Cabeçalhos da planilha:", headers);

                                                // Colunas fixas que não são perguntas
                                                const fixedColumns = [
                                                    'Carimbo de data/hora', 'Pontuação', 'Nome:', 'Série:', 'Endereço de e-mail',
                                                    'Timestamp', 'Data', 'Date', 'Email', 'E-mail', 'EMAIL', 'E-Mail', 'Endereço de Email'
                                                ];

                                                // Filtrar cabeçalhos que são perguntas
                                                perguntas = headers.filter(header => !fixedColumns.includes(header));
                                                console.log("Perguntas identificadas:", perguntas);

                                                // Identificar a linha GABARITO ou com pontuação 10/10
                                                let gabaritoRow = null;
                                                for (let row of dadosPlanilha) {
                                                    if (row['Nome:'] && row['Nome:'].trim().toUpperCase() === 'GABARITO') {
                                                        gabaritoRow = row;
                                                        break;
                                                    }
                                                }

                                                if (!gabaritoRow) {
                                                    // Procurar uma linha com pontuação 10/10
                                                    gabaritoRow = dadosPlanilha.find(row => row['Pontuação'] === '10 / 10');
                                                }

                                                if (!gabaritoRow) {
                                                    alert("Nenhuma linha 'GABARITO' ou com pontuação 10/10 encontrada.");
                                                    button.disabled = false;
                                                    button.textContent = "Carregar";
                                                    return;
                                                }

                                                // Extrair respostas corretas
                                                respostasCorretas = perguntas.map(pergunta => gabaritoRow[pergunta] || '');
                                                console.log("Respostas corretas:", respostasCorretas);

                                                // Exibir tabela (excluindo a linha GABARITO)
                                                const headerRow = document.createElement("tr");
                                                headers.forEach(h => {
                                                    const th = document.createElement("th");
                                                    th.textContent = h;
                                                    headerRow.appendChild(th);
                                                });
                                                thead.appendChild(headerRow);

                                                dadosPlanilha.forEach(row => {
                                                    if (row['Nome:'] && row['Nome:'].trim().toUpperCase() === 'GABARITO') {
                                                        return; // Ignora a linha GABARITO na tabela visual
                                                    }
                                                    const tr = document.createElement("tr");
                                                    headers.forEach(h => {
                                                        const td = document.createElement("td");
                                                        td.textContent = row[h] || '';
                                                        tr.appendChild(td);
                                                    });
                                                    tbody.appendChild(tr);
                                                });

                                                button.disabled = false;
                                                button.textContent = "Carregar";
                                            },
                                            error: function(error) {
                                                alert("Erro ao parsear o CSV: " + error);
                                                button.disabled = false;
                                                button.textContent = "Carregar";
                                            }
                                        });
                                    })
                                    .catch(err => {
                                        alert("Erro ao carregar a planilha: " + err);
                                        button.disabled = false;
                                        button.textContent = "Carregar";
                                    });
                            }

                            function importarParaBanco() {
                                const formularioIdInput = document.getElementById('formularioId');
                                if (!formularioIdInput.value.trim()) {
                                    alert("O campo 'Identificador do formulário' é obrigatório.");
                                    return;
                                }

                                if (dadosPlanilha.length === 0) {
                                    alert("Nenhum dado carregado.");
                                    return;
                                }

                                const button = document.querySelector('button[onclick="importarParaBanco()"]');
                                button.disabled = true;
                                button.textContent = "Importando...";

                                // Preparar dados para enviar (excluindo a linha GABARITO)
                                const dadosFiltrados = dadosPlanilha.filter(row => !(row['Nome:'] && row['Nome:'].trim().toUpperCase() === 'GABARITO'));

                                fetch('importar_formulario.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        dados: dadosFiltrados,
                                        formularioId: document.getElementById('formularioId').value,
                                        perguntas: perguntas,
                                        respostasCorretas: respostasCorretas,
                                        bnccHabilidade: document.getElementById('bnccHabilidade').value.trim()
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    const box = document.getElementById("message-box");
                                    let mensagem = data.mensagem;
                                    if (data.erros && data.erros.length > 0) {
                                        mensagem += "<br>Detalhes:<br>" + data.erros.join("<br>");
                                    }
                                    if (data.mensagem.includes("Erro") || data.erros) {
                                        box.innerHTML = `<div class="mensagem-erro">${mensagem}</div>`;
                                    } else {
                                        box.innerHTML = `<div class="mensagem-sucesso">${mensagem}</div>`;
                                        // Atualizar o dropdown após importação
                                        atualizarDropdownFormularios();
                                    }
                                    button.disabled = false;
                                    button.textContent = "Importar para o banco";
                                })
                                .catch(err => {
                                    console.error(err);
                                    const box = document.getElementById("message-box");
                                    box.innerHTML = `<div class="mensagem-erro">Erro ao importar os dados: ${err}</div>`;
                                    button.disabled = false;
                                    button.textContent = "Importar para o banco";
                                });
                            }

                            function excluirFormulario() {
                                const formularioId = document.getElementById('formularioIdDelete').value;
                                if (!formularioId) {
                                    alert("Selecione um formulário para excluir.");
                                    return;
                                }

                                if (!confirm(`Tem certeza que deseja excluir o formulário "${formularioId}"? Essa ação não pode ser desfeita.`)) {
                                    return;
                                }

                                const button = document.querySelector('button[onclick="excluirFormulario()"]');
                                button.disabled = true;
                                button.textContent = "Excluindo...";

                                fetch('delete_formulario.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        formularioId: formularioId
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    const box = document.getElementById("message-box");
                                    if (data.status === "success") {
                                        box.innerHTML = `<div class="mensagem-sucesso">${data.mensagem}</div>`;
                                        // Atualizar o dropdown após exclusão
                                        atualizarDropdownFormularios();
                                    } else {
                                        box.innerHTML = `<div class="mensagem-erro">${data.mensagem}</div>`;
                                    }
                                    button.disabled = false;
                                    button.textContent = "Excluir";
                                })
                                .catch(err => {
                                    console.error(err);
                                    const box = document.getElementById("message-box");
                                    box.innerHTML = `<div class="mensagem-erro">Erro ao excluir o formulário: ${err}</div>`;
                                    button.disabled = false;
                                    button.textContent = "Excluir";
                                });
                            }

                            function atualizarDropdownFormularios() {
                                fetch('fetch_formularios.php')
                                    .then(response => response.json())
                                    .then(data => {
                                        const select = document.getElementById('formularioIdDelete');
                                        select.innerHTML = '<option value="">Selecione um formulário</option>';
                                        data.forEach(formId => {
                                            const option = document.createElement('option');
                                            option.value = formId;
                                            option.textContent = formId;
                                            select.appendChild(option);
                                        });
                                    })
                                    .catch(err => {
                                        console.error("Erro ao atualizar dropdown de formulários:", err);
                                    });
                            }

                            // Atualizar o dropdown ao carregar a página
                            document.addEventListener('DOMContentLoaded', atualizarDropdownFormularios);
                        </script>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <?php if (in_array($cargo, ['Coordenador', 'Diretor', 'Administrador'])): ?>
    <div id="modal-cadastrar-turma" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-funcionario" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <div id="modal-add-aluno" class="modal" style="display: none;">
        <div class="modal-content"></div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <footer>
        <p>© 2025 evoGraph. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    <script src="./assets/js/utils.js"></script>
    <script src="./assets/js/modal-add-funcionario.js"></script>
    <script src="./assets/js/modal-add-turma.js"></script>
    <script src="./assets/js/modal-add-aluno.js"></script>
    <script src="./assets/js/sidebar.js"></script>
    <script src="./assets/js/ajax.js"></script>
</body>
</html>