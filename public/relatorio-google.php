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
    <title>evoGraph - Meu Perfil</title>
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
            <a href="#"><i class="fa-solid fa-chart-bar"></i>Relatórios</a>
            <a href="my_profile.php"><i class="fa-solid fa-user-gear"></i>Meu Perfil</a>

            <?php if ($cargo === "Coordenador" || $cargo === "Diretor" || $cargo === "Administrador"): ?>
            <div class="sidebar-item">
                <a href="#" class="sidebar-toggle"><i class="fa-solid fa-plus"></i>Cadastro<i class="fa-solid fa-chevron-down submenu-toggle"></i></a>
                <div class="submenu">
                    <a href="#" onclick="openAddTurmaModal(); return false;"><i class="fa-solid fa-chalkboard"></i>Turma</a>
                    <?php if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor" || $cargo === "Administrador"): ?>
                    <a href="#" onclick="openAddFuncionarioModal()"><i class="fa-solid fa-user-plus"></i>Funcionário</a>
                    <?php endif; ?>
                    <a href="#" onclick="openAddModal(); return false;"><i class="fa-solid fa-graduation-cap"></i>Aluno</a>
                </div>
            </div>
            <?php endif; ?>
            
            <a href="funcionarios.php"><i class="fa-solid fa-users"></i>Funcionários</a>
            <a href="logout.php"><i class="fa-solid fa-sign-out"></i>Sair</a>
        </div>
        <!-- FIM SIDEBAR -->

        <div class="main-content" id="main-content">
            <div class="titulo-secao">
                <span><a href="dashboard.php" class="home-link"><i class="fa-solid fa-house"></i></a>/ Formulário do Google Forms</span>
            </div>

            <section class="meu-perfil">
                <div id="message-box"></div>
                <div class="profile-form">
                    <form id="profile-form" enctype="multipart/form-data">
                        <input type="hidden" name="save_profile" value="1">
                        
                        <h3>Relatório - Respostas do Google Forms</h3>
                        <div class="input-google-link">
                            <label for="googleSheetLink">Cole o link da planilha do Google:</label>
                            <input type="text" id="googleSheetLink" placeholder="https://docs.google.com/spreadsheets/d/...">
                            <input type="text" id="googleSheetTab" placeholder="(Opcional) Nome da aba"><br>
                            <button type="button" onclick="carregarPlanilha()">Carregar</button><br>
                            <button type="button" onclick="importarParaBanco()">Importar para o banco</button>
                        </div>
                        <div style="overflow-x: auto;">
                            <table id="tabela-dados">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <script>
                            function formatGoogleSheetUrl(userInputUrl, sheetName = '') {
                                const idMatch = userInputUrl.match(/\/d\/([a-zA-Z0-9-_]+)/);
                                const gidMatch = userInputUrl.match(/gid=(\d+)/);

                                if (!idMatch) {
                                    alert("Link inválido! Verifique o link da planilha.");
                                    return null;
                                }

                                const sheetId = idMatch[1];
                                const baseUrl = `https://docs.google.com/spreadsheets/d/${sheetId}/gviz/tq?tqx=out:csv`;

                                if (sheetName) {
                                    return `${baseUrl}&sheet=${encodeURIComponent(sheetName)}`;
                                }

                                if (gidMatch) {
                                    const gid = gidMatch[1];
                                    return `https://docs.google.com/spreadsheets/d/${sheetId}/export?format=csv&gid=${gid}`;
                                }

                                return baseUrl;
                            }

                            function carregarPlanilha() {
                                const urlInput = document.getElementById('googleSheetLink').value;
                                const abaInput = document.getElementById('googleSheetTab').value;
                                const formattedUrl = formatGoogleSheetUrl(urlInput, abaInput);

                                if (!formattedUrl) return;

                                fetch(formattedUrl)
                                .then(res => res.text())
                                .then(csv => {
                                    console.log("CSV bruto:", csv);  // <-- Aqui você verá o conteúdo original da planilha
                                    // Use PapaParse para analisar o CSV
                                    Papa.parse(csv, {
                                        header: true,
                                        skipEmptyLines: true,
                                        complete: function(results) {
                                            const data = results.data;
                                            const thead = document.querySelector("#tabela-dados thead");
                                            const tbody = document.querySelector("#tabela-dados tbody");

                                            // Limpa tabela
                                            thead.innerHTML = '';
                                            tbody.innerHTML = '';

                                            const headers = Object.keys(data[0]);
                                            const headerRow = document.createElement("tr");
                                            headers.forEach(h => {
                                                const th = document.createElement("th");
                                                th.textContent = h;
                                                headerRow.appendChild(th);
                                            });
                                            thead.appendChild(headerRow);

                                            data.forEach(row => {
                                                const tr = document.createElement("tr");
                                                headers.forEach(h => {
                                                    const td = document.createElement("td");
                                                    td.textContent = row[h];
                                                    tr.appendChild(td);
                                                });
                                                tbody.appendChild(tr);
                                            });
                                        }
                                    });
                                });
                            }

                            function importarParaBanco() {
                                if (dadosPlanilha.length === 0) {
                                    alert("Nenhum dado carregado.");
                                    return;
                                }

                                fetch('importar_formulario.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(dadosPlanilha)
                                })
                                .then(response => response.json())
                                .then(data => {
                                    const box = document.getElementById("message-box");
                                    box.innerHTML = `<div class="mensagem-sucesso">${data.mensagem}</div>`;
                                })
                                .catch(err => {
                                    console.error(err);
                                    alert("Erro ao importar os dados.");
                                });
                            }
                        </script>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <?php if ($cargo === "Coordenador" || $cargo === "Diretor" || $cargo === "Administrador"): ?>
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
        <p>&copy; 2025 evoGraph. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    <script src="./assets/js/utils.js"></script>
    <script src="./assets/js/modal-add-funcionario.js"></script>
    <script src="./assets/js/modal-add-turma.js"></script>
    <script src="./assets/js/modal-add-aluno.js"></script>
    
    <script src="./assets/js/my-profile.js"></script>
    <script src="./assets/js/dashboard.js"></script>
    <script src="./assets/js/ajax.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
    
            // Atualiza o estado no localStorage
            const isActive = sidebar.classList.contains('active');
            localStorage.setItem('sidebarActive', isActive);
        }
    
        $(document).ready(function() {
            // Inicializa o estado da sidebar com base no localStorage
            if (localStorage.getItem('sidebarActive') === 'true') {
                $('#sidebar').addClass('active');
                $('#main-content').addClass('shifted');
            }
    
            $('#menu-toggle').on('click', function() {
                toggleSidebar();
            });
    
            // Toggle do submenu
            $('.sidebar-toggle').on('click', function(e) {
                e.preventDefault();
                const $submenu = $(this).next('.submenu');
                const $toggleIcon = $(this).find('.submenu-toggle');
    
                $submenu.slideToggle(200); // Animação suave
                $toggleIcon.toggleClass('open'); // Gira a seta
            });
        });
    </script>

</body>
</html>