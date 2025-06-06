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
                            return;
                        }
                        const tr = document.createElement("tr");
                        headers.forEach(h => {
                            const td = document.createElement("td");
                            td.textContent = row[h] || '';
                            tr.appendChild(td);
                        });
                        tbody.appendChild(tr);
                    });

                    // Carregar dropdowns globais de ano escolar
                    const globalSelectAno = document.getElementById('globalBnccAno');
                    const globalSelectDisciplina = document.getElementById('globalBnccDisciplina');
                    globalSelectAno.innerHTML = '<option value="">Selecione o ano</option>';
                    globalSelectDisciplina.innerHTML = '<option value="">Selecione a disciplina</option>';
                    globalSelectDisciplina.disabled = true;

                    fetch('fetch_anos_disciplinas_habilidades.php?action=anos_disciplinas', {
                        method: 'GET',
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Resposta da API para anos_disciplinas:', data);
                        if (data.status === 'success' && data.data && Array.isArray(data.data.anos_escolares) && data.data.anos_escolares.length > 0) {
                            data.data.anos_escolares.forEach(ano => {
                                const option = document.createElement('option');
                                option.value = ano.id;
                                option.textContent = ano.nome;
                                globalSelectAno.appendChild(option);
                            });
                        } else {
                            alert('Nenhum ano escolar disponível.');
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao carregar anos escolares:', err);
                        alert('Erro ao carregar anos escolares.');
                    });

                    // Carregar dropdowns para cada pergunta via AJAX
                    const perguntasHabilidadesList = document.getElementById('perguntas-habilidades-list');
                    perguntasHabilidadesList.innerHTML = '';
                    perguntas.forEach((pergunta, index) => {
                        fetch('render_pergunta_dropdowns.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ index: index, pergunta: pergunta })
                        })
                        .then(response => response.text())
                        .then(html => {
                            const div = document.createElement('div');
                            div.innerHTML = html;
                            perguntasHabilidadesList.appendChild(div);
                        })
                        .catch(err => {
                            console.error('Erro ao carregar dropdowns:', err);
                        });
                    });

                    // Adicionar eventos de mudança para os dropdowns globais
                    globalSelectAno.addEventListener('change', () => {
                        const anoId = globalSelectAno.value;
                        console.log(`Ano global selecionado: ${anoId}`);
                        carregarDisciplinas(anoId, globalSelectDisciplina);
                    });

                    globalSelectDisciplina.addEventListener('change', () => {
                        const anoId = globalSelectAno.value;
                        const disciplinaId = globalSelectDisciplina.value;
                        console.log(`Disciplina global selecionada: ${disciplinaId}, ano: ${anoId}`);
                        perguntas.forEach((_, index) => {
                            const selectHabilidade = document.getElementById(`bnccHabilidade_${index}`);
                            carregarHabilidades(anoId, disciplinaId, selectHabilidade);
                        });
                    });

                    document.getElementById('perguntas-habilidades-section').style.display = 'block';

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

    // Coletar habilidades BNCC para cada pergunta
    const habilidadesPorPergunta = perguntas.map((pergunta, index) => {
        const habilidade = document.getElementById(`bnccHabilidade_${index}`).value;
        if (!habilidade) {
            alert(`Selecione a habilidade BNCC para a pergunta ${index + 1}: ${pergunta}`);
            throw new Error('Habilidade não selecionada');
        }
        return { pergunta, habilidade };
    });

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
            formularioId: formularioIdInput.value,
            perguntas: perguntas,
            respostasCorretas: respostasCorretas,
            habilidadesPorPergunta: habilidadesPorPergunta,
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
            document.getElementById('perguntas-habilidades-section').style.display = 'none';
        }
        button.disabled = false;
        button.textContent = "Importar";
    })
    .catch(err => {
        console.error(err);
        const box = document.getElementById("message-box");
        box.innerHTML = `<div class="mensagem-erro">Erro ao importar os dados: ${err}</div>`;
        button.disabled = false;
        button.textContent = "Importar";
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

function carregarDisciplinas(anoId, selectDisciplina) {
    selectDisciplina.innerHTML = '<option value="">Selecione a disciplina</option>';
    selectDisciplina.disabled = true;

    if (!anoId) {
        console.log('Nenhum anoId fornecido, mantendo disciplina desabilitada');
        return;
    }

    console.log(`Carregando disciplinas para anoId: ${anoId}`);
    fetch(`fetch_anos_disciplinas_habilidades.php?action=disciplinas&ano_id=${anoId}`)
        .then(response => response.json())
        .then(data => {
            console.log(`Resposta do backend para disciplinas (anoId: ${anoId}):`, data);
            if (data.status === 'success' && data.data.length > 0) {
                selectDisciplina.disabled = false;
                data.data.forEach(disciplina => {
                    const option = document.createElement('option');
                    option.value = disciplina.id;
                    option.textContent = disciplina.nome;
                    selectDisciplina.appendChild(option);
                });
                console.log(`Disciplinas carregadas para anoId ${anoId}:`, data.data);
            } else {
                console.warn(`Nenhuma disciplina encontrada para anoId ${anoId}:`, data.message || 'Sem dados');
                alert('Nenhuma disciplina encontrada para o ano selecionado.');
            }
        })
        .catch(err => {
            console.error('Erro ao carregar disciplinas:', err);
            alert('Erro ao carregar disciplinas.');
        });
}

function carregarHabilidades(anoId, disciplinaId, selectHabilidade) {
    selectHabilidade.innerHTML = '<option value="">Selecione a habilidade</option>';
    selectHabilidade.disabled = true;

    if (!anoId || !disciplinaId) {
        console.log(`Parâmetros inválidos - anoId: ${anoId}, disciplinaId: ${disciplinaId}`);
        return;
    }

    console.log(`Carregando habilidades para anoId: ${anoId}, disciplinaId: ${disciplinaId}`);
    fetch(`fetch_anos_disciplinas_habilidades.php?action=habilidades&ano_id=${anoId}&disciplina_id=${disciplinaId}`)
        .then(response => response.json())
        .then(data => {
            console.log(`Resposta do backend para habilidades (anoId: ${anoId}, disciplinaId: ${disciplinaId}):`, data);
            if (data.status === 'success' && data.data.length > 0) {
                selectHabilidade.disabled = false;
                data.data.forEach(habilidade => {
                    const option = document.createElement('option');
                    option.value = habilidade.codigo;
                    option.textContent = `${habilidade.codigo} - ${habilidade.descricao.substring(0, 100)}${habilidade.descricao.length > 100 ? '...' : ''}`;
                    selectHabilidade.appendChild(option);
                });
                console.log(`Habilidades carregadas para disciplinaId ${disciplinaId}:`, data.data);
            } else {
                console.warn(`Nenhuma habilidade encontrada para anoId ${anoId}, disciplinaId ${disciplinaId}:`, data.message || 'Sem dados');
                alert('Nenhuma habilidade encontrada para a disciplina selecionada.');
            }
        })
        .catch(err => {
            console.error('Erro ao carregar habilidades:', err);
            alert('Erro ao carregar habilidades.');
        });
}

// Adicionar eventos de mudança
document.addEventListener('DOMContentLoaded', () => {
    atualizarDropdownFormularios();
});