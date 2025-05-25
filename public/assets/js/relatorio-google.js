(function ($) {
    let dadosPlanilha = [];
    let perguntas = [];
    let respostasCorretas = [];

    function formatGoogleSheetUrl(url) {
        const regex = /\/d\/([a-zA-Z0-9-_]+)(?:\/edit|\/view|$)/;
        const match = url.match(regex);
        if (!match) {
            alert("URL do Google Sheets inválida.");
            return null;
        }
        return `https://docs.google.com/spreadsheets/d/${match[1]}/export?format=csv`;
    }

    window.carregarPlanilha = function() {
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

                        // Normalizar cabeçalhos
                        const headers = Object.keys(dadosPlanilha[0]).map(header => {
                            const cleanedHeader = header.trim();
                            // Normalizar variações de "Série:"
                            if (/^S[eé]rie\s*:?\s*$/i.test(cleanedHeader)) {
                                return "Série:";
                            }
                            return cleanedHeader;
                        });
                        console.log("Cabeçalhos normalizados:", headers);

                        // Atualizar dadosPlanilha com cabeçalhos normalizados
                        dadosPlanilha = dadosPlanilha.map(row => {
                            let newRow = {};
                            Object.keys(row).forEach((key, index) => {
                                newRow[headers[index]] = row[key];
                            });
                            return newRow;
                        });

                        const fixedColumns = [
                            'Carimbo de data/hora', 'Pontuação', 'Nome:', 'Série:', 'Endereço de e-mail',
                            'Timestamp', 'Data', 'Date', 'Email', 'E-mail', 'EMAIL', 'E-Mail', 'Endereço de Email'
                        ];

                        perguntas = headers.filter(header => !fixedColumns.includes(header));
                        console.log("Perguntas identificadas:", perguntas);

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

                        respostasCorretas = perguntas.map(pergunta => gabaritoRow[pergunta] || '');
                        console.log("Respostas corretas:", respostasCorretas);

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
    };

    window.importarParaBanco = function() {
        const formularioId = document.getElementById('formulario_id').value;
        const anoEscolar = document.getElementById('ano_escolar').value;
        const disciplina = document.getElementById('disciplina').value;
        const habilidadeBncc = document.getElementById('habilidade_bncc').value;

        if (!formularioId || !anoEscolar || !disciplina || !habilidadeBncc) {
            alert("Preencha todos os campos obrigatórios!");
            return;
        }

        if (dadosPlanilha.length === 0) {
            alert("Nenhum dado carregado para importar.");
            return;
        }

        const button = document.querySelector('button[onclick="importarParaBanco()"]');
        button.disabled = true;
        button.textContent = "Importando...";

        $.ajax({
            url: 'importar_formulario.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                formulario_id: formularioId,
                ano_escolar: anoEscolar,
                disciplina: disciplina,
                habilidade_bncc: habilidadeBncc,
                perguntas: perguntas,
                respostas_corretas: respostasCorretas,
                dados: dadosPlanilha
            }),
            success: function(response) {
                const messageBox = $('#message-box');
                if (response.success) {
                    messageBox.html('<div class="mensagem-sucesso">' + response.mensagem + '</div>');
                } else {
                    messageBox.html('<div class="mensagem-erro">' + response.mensagem + '</div>');
                }
                button.disabled = false;
                button.textContent = "Importar";
            },
            error: function(xhr, status, error) {
                $('#message-box').html('<div class="mensagem-erro">Erro ao importar: ' + error + '</div>');
                button.disabled = false;
                button.textContent = "Importar";
            }
        });
    };

    window.excluirFormulario = function(formularioId) {
        if (!confirm("Tem certeza que deseja excluir o formulário " + formularioId + "?")) {
            return;
        }

        $.ajax({
            url: 'delete_formulario.php',
            method: 'POST',
            data: { formulario_id: formularioId },
            success: function(response) {
                const messageBox = $('#message-box');
                if (response.success) {
                    messageBox.html('<div class="mensagem-sucesso">' + response.mensagem + '</div>');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    messageBox.html('<div class="mensagem-erro">' + response.mensagem + '</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#message-box').html('<div class="mensagem-erro">Erro ao excluir: ' + error + '</div>');
            }
        });
    };

    function carregarDisciplinas(anoEscolarId) {
        if (!anoEscolarId) {
            $('#disciplina').html('<option value="">Selecione uma disciplina</option>');
            $('#habilidade_bncc').html('<option value="">Selecione uma habilidade BNCC</option>');
            return;
        }

        $.ajax({
            url: 'fetch_anos_disciplinas_habilidades.php',
            method: 'GET',
            data: { ano_escolar_id: anoEscolarId },
            success: function(response) {
                let disciplinas = '<option value="">Selecione uma disciplina</option>';
                response.disciplinas.forEach(d => {
                    disciplinas += `<option value="${d.id}">${d.nome}</option>`;
                });
                $('#disciplina').html(disciplinas);
                $('#habilidade_bncc').html('<option value="">Selecione uma habilidade BNCC</option>');
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar disciplinas:', error);
            }
        });
    }

    function carregarHabilidades(disciplinaId) {
        if (!disciplinaId) {
            $('#habilidade_bncc').html('<option value="">Selecione uma habilidade BNCC</option>');
            return;
        }

        $.ajax({
            url: 'fetch_anos_disciplinas_habilidades.php',
            method: 'GET',
            data: { disciplina_id: disciplinaId },
            success: function(response) {
                let habilidades = '<option value="">Selecione uma habilidade BNCC</option>';
                response.habilidades.forEach(h => {
                    habilidades += `<option value="${h.id}">${h.codigo} - ${h.descricao}</option>`;
                });
                $('#habilidade_bncc').html(habilidades);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar habilidades:', error);
            }
        });
    }

    $(document).ready(function() {
        $('#ano_escolar').on('change', function() {
            carregarDisciplinas(this.value);
        });

        $('#disciplina').on('change', function() {
            carregarHabilidades(this.value);
        });
    });
})(jQuery);