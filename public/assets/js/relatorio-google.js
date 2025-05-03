let dadosPlanilha = [];
let perguntas = [];
let respostasCorretas = [];

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

    if (!funcionarioId) {
        alert("Erro: Usuário não autenticado.");
        return;
    }

    const button = document.querySelector('button[onclick="importarParaBanco()"]');
    button.disabled = true;
    button.textContent = "Importando...";

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
            bnccHabilidadeId: document.getElementById('bnccHabilidade').value || null,
            funcionarioId: funcionarioId
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

// Funções para carregar dropdowns de BNCC
function carregarAnos() {
    fetch('fetch_anos.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('bnccAno');
            select.innerHTML = '<option value="">Selecione o ano</option>';
            data.forEach(ano => {
                const option = document.createElement('option');
                option.value = ano.id;
                option.textContent = ano.nome;
                select.appendChild(option);
            });
            select.disabled = false;
        })
        .catch(err => {
            console.error("Erro ao carregar anos:", err);
            document.getElementById('message-box').innerHTML = `<div class="mensagem-erro">Erro ao carregar anos: ${err}</div>`;
        });
}

function carregarDisciplinas(anoId) {
    const disciplinaSelect = document.getElementById('bnccDisciplina');
    const habilidadeSelect = document.getElementById('bnccHabilidade');
    
    // Resetar e desabilitar dropdowns dependentes
    disciplinaSelect.innerHTML = '<option value="">Selecione a disciplina</option>';
    habilidadeSelect.innerHTML = '<option value="">Selecione a habilidade</option>';
    disciplinaSelect.disabled = true;
    habilidadeSelect.disabled = true;

    // Validação do anoId
    if (!anoId || isNaN(anoId) || parseInt(anoId) <= 0) {
        console.error("Ano inválido:", anoId);
        document.getElementById('message-box').innerHTML = `<div class="mensagem-erro">ID do ano inválido: ${anoId}</div>`;
        return;
    }

    console.log("Enviando anoId:", anoId);

    fetch('fetch_disciplinas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ano_id: parseInt(anoId) })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            document.getElementById('message-box').innerHTML = `<div class="mensagem-erro">${data.error}</div>`;
            return;
        }
        data.forEach(disciplina => {
            const option = document.createElement('option');
            option.value = disciplina.id;
            option.textContent = disciplina.nome;
            disciplinaSelect.appendChild(option);
        });
        disciplinaSelect.disabled = false;
    })
    .catch(err => {
        console.error("Erro ao carregar disciplinas:", err);
        document.getElementById('message-box').innerHTML = `<div class="mensagem-erro">Erro ao carregar disciplinas: ${err.message}</div>`;
    });
}

function carregarHabilidades(anoId, disciplinaId) {
    const habilidadeSelect = document.getElementById('bnccHabilidade');
    
    // Resetar e desabilitar dropdown de habilidades
    habilidadeSelect.innerHTML = '<option value="">Selecione a habilidade</option>';
    habilidadeSelect.disabled = true;

    if (!anoId || !disciplinaId || isNaN(anoId) || isNaN(disciplinaId) || parseInt(anoId) <= 0 || parseInt(disciplinaId) <= 0) {
        console.error("Ano ou disciplina inválido:", { anoId, disciplinaId });
        document.getElementById('message-box').innerHTML = `<div class="mensagem-erro">ID do ano ou disciplina inválido</div>`;
        return;
    }

    fetch('fetch_habilidades.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ano_id: parseInt(anoId), disciplina_id: parseInt(disciplinaId) })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            document.getElementById('message-box').innerHTML = `<div class="mensagem-erro">${data.error}</div>`;
            return;
        }
        data.forEach(habilidade => {
            const option = document.createElement('option');
            option.value = habilidade.id;
            option.textContent = `${habilidade.codigo} - ${habilidade.descricao.substring(0, 50)}...`;
            option.title = `${habilidade.codigo} - ${habilidade.descricao}`;
            habilidadeSelect.appendChild(option);
        });
        habilidadeSelect.disabled = false;
    })
    .catch(err => {
        console.error("Erro ao carregar habilidades:", err);
        document.getElementById('message-box').innerHTML = `<div class="mensagem-erro">Erro ao carregar habilidades: ${err.message}</div>`;
    });
}

// Event listeners para os dropdowns
document.addEventListener('DOMContentLoaded', () => {
    atualizarDropdownFormularios();
    carregarAnos();

    const anoSelect = document.getElementById('bnccAno');
    const disciplinaSelect = document.getElementById('bnccDisciplina');

    anoSelect.addEventListener('change', () => {
        const anoId = anoSelect.value;
        console.log("Mudança no ano, valor selecionado:", anoId);
        carregarDisciplinas(anoId);
    });

    disciplinaSelect.addEventListener('change', () => {
        const anoId = anoSelect.value;
        const disciplinaId = disciplinaSelect.value;
        console.log("Mudança na disciplina, valores:", { anoId, disciplinaId });
        carregarHabilidades(anoId, disciplinaId);
    });
});