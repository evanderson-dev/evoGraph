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

                    // Exibir perguntas e dropdowns para associar habilidades
                    const perguntasHabilidadesList = document.getElementById('perguntas-habilidades-list');
                    perguntasHabilidadesList.innerHTML = '';
                    perguntas.forEach((pergunta, index) => {
                        const div = document.createElement('div');
                        div.className = 'form-group-importar';
                        div.innerHTML = `
                            <div class="col-100">
                                <label>Pergunta ${index + 1}: ${pergunta}</label>
                            </div>
                            <div class="col-18">
                                <label for="bnccAno_${index}">Ano Escolar:</label>
                                <select id="bnccAno_${index}" name="bnccAno_${index}" required>
                                    <option value="">Selecione o ano</option>
                                </select>
                            </div>
                            <div class="col-18">
                                <label for="bnccDisciplina_${index}">Disciplina:</label>
                                <select id="bnccDisciplina_${index}" name="bnccDisciplina_${index}" disabled required>
                                    <option value="">Selecione a disciplina</option>
                                </select>
                            </div>
                            <div class="col-18">
                                <label for="bnccHabilidade_${index}">Habilidade BNCC:</label>
                                <select id="bnccHabilidade_${index}" name="bnccHabilidade_${index}" disabled required>
                                    <option value="">Selecione a habilidade</option>
                                </select>
                            </div>
                        `;
                        perguntasHabilidadesList.appendChild(div);

                        // Carregar anos escolares
                        fetch('fetch_anos_disciplinas_habilidades.php?action=anos_disciplinas')
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    const selectAno = document.getElementById(`bnccAno_${index}`);
                                    data.data.anos_escolares.forEach(ano => {
                                        const option = document.createElement('option');
                                        option.value = ano.id;
                                        option.textContent = ano.nome;
                                        selectAno.appendChild(option);
                                    });

                                    // Adicionar evento de mudança para carregar disciplinas
                                    selectAno.addEventListener('change', () => {
                                        const anoId = selectAno.value;
                                        const selectDisciplina = document.getElementById(`bnccDisciplina_${index}`);
                                        carregarDisciplinas(anoId, selectDisciplina);
                                    });

                                    // Adicionar evento de mudança para carregar habilidades
                                    const selectDisciplina = document.getElementById(`bnccDisciplina_${index}`);
                                    selectDisciplina.addEventListener('change', () => {
                                        const anoId = selectAno.value;
                                        const disciplinaId = selectDisciplina.value;
                                        const selectHabilidade = document.getElementById(`bnccHabilidade_${index}`);
                                        carregarHabilidades(anoId, disciplinaId, selectHabilidade);
                                    });
                                }
                            })
                            .catch(err => {
                                console.error('Erro ao carregar anos escolares:', err);
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
        const anoId = document.getElementById(`bnccAno_${index}`).value;
        const disciplinaId = document.getElementById(`bnccDisciplina_${index}`).value;
        const habilidade = document.getElementById(`bnccHabilidade_${index}`).value;
        if (!anoId || !disciplinaId || !habilidade) {
            alert(`Selecione ano escolar, disciplina e habilidade BNCC para a pergunta ${index + 1}: ${pergunta}`);
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

// Função para carregar disciplinas com base no ano selecionado
function carregarDisciplinas(anoId) {
    const selectDisciplina = document.getElementById('bnccDisciplina');
    const selectHabilidade = document.getElementById('bnccHabilidade');
    
    // Limpar e desabilitar disciplinas e habilidades
    selectDisciplina.innerHTML = '<option value="">Selecione a disciplina</option>';
    selectDisciplina.disabled = true;
    selectHabilidade.innerHTML = '<option value="">Selecione a habilidade</option>';
    selectHabilidade.disabled = true;

    if (!anoId) return;

    fetch(`fetch_anos_disciplinas_habilidades.php?action=disciplinas&ano_id=${anoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                selectDisciplina.disabled = false;
                data.data.forEach(disciplina => {
                    const option = document.createElement('option');
                    option.value = disciplina.id;
                    option.textContent = disciplina.nome;
                    selectDisciplina.appendChild(option);
                });
            } else {
                console.error('Erro ao carregar disciplinas:', data.message);
                alert('Erro ao carregar disciplinas: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Erro ao carregar disciplinas:', err);
            alert('Erro ao carregar disciplinas.');
        });
}

// Função para carregar habilidades com base no ano e disciplina selecionados
function carregarHabilidades(anoId, disciplinaId) {
    const selectHabilidade = document.getElementById('bnccHabilidade');
    
    // Limpar e desabilitar habilidades
    selectHabilidade.innerHTML = '<option value="">Selecione a habilidade</option>';
    selectHabilidade.disabled = true;

    if (!anoId || !disciplinaId) return;

    fetch(`fetch_anos_disciplinas_habilidades.php?action=habilidades&ano_id=${anoId}&disciplina_id=${disciplinaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                selectHabilidade.disabled = false;
                data.data.forEach(habilidade => {
                    const option = document.createElement('option');
                    option.value = habilidade.codigo;
                    option.textContent = `${habilidade.codigo} - ${habilidade.descricao.substring(0, 100)}${habilidade.descricao.length > 100 ? '...' : ''}`;
                    selectHabilidade.appendChild(option);
                });
            } else {
                console.error('Erro ao carregar habilidades:', data.message);
                alert('Erro ao carregar habilidades: ' + data.message);
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

    const selectAno = document.getElementById('bnccAno');
    const selectDisciplina = document.getElementById('bnccDisciplina');

    selectAno.addEventListener('change', () => {
        const anoId = selectAno.value;
        carregarDisciplinas(anoId);
    });

    selectDisciplina.addEventListener('change', () => {
        const anoId = selectAno.value;
        const disciplinaId = selectDisciplina.value;
        carregarHabilidades(anoId, disciplinaId);
    });
});