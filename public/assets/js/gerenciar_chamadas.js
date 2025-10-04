// assets/js/gerenciar_chamadas.js (JS para a página de gerenciamento de chamadas)
$(document).ready(function() {
    let selectedTurmaId = null;
    let selectedData = $('#dataChamada').val();  // Inicializa com data atual

    // Carregar todas as turmas (sem filtro por professor)
    $.ajax({
        url: 'fetch_turmas.php',
        method: 'POST',
        data: { action: 'all_turmas' },  // Nova ação para todas as turmas
        dataType: 'json',
        success: function(response) {
            console.log('Resposta das turmas:', response);  // Debug
            if (response.success) {
                const select = $('#turmaSelect');
                select.empty().append('<option value="">Selecione uma turma</option>');
                response.turmas.forEach(turma => {
                    select.append(`<option value="${turma.id}">${turma.nome} (${turma.ano}º Ano)</option>`);
                });
                $('#carregarChamadasBtn').prop('disabled', false);  // Habilita após carregar
            } else {
                showMessage('Erro ao carregar turmas: ' + response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro AJAX turmas:', xhr.responseText);
            showMessage('Erro ao carregar turmas.', 'error');
        }
    });

    // Evento para seleção de turma
    $('#turmaSelect').change(function() {
        selectedTurmaId = $(this).val();
        toggleLoadButton();
    });

    // Evento para seleção de data
    $('#dataChamada').change(function() {
        selectedData = $(this).val();
        toggleLoadButton();
    });

    // Função para habilitar/desabilitar botão de carregar
    function toggleLoadButton() {
        $('#carregarChamadasBtn').prop('disabled', !selectedTurmaId || !selectedData);
    }

    // Evento para carregar presenças (visualização apenas)
    $('#carregarChamadasBtn').click(function() {
        if (!selectedTurmaId || !selectedData) return;

        $.ajax({
            url: 'fetch_presencas.php',  // Nova API para presenças
            method: 'POST',
            data: { turma_id: selectedTurmaId, data: selectedData },
            dataType: 'json',
            success: function(response) {
                console.log('Resposta das presenças:', response);  // Debug
                if (response.success) {
                    const tbody = $('#tabela-presencas tbody');
                    tbody.empty();
                    if (response.presencas.length === 0) {
                        tbody.append('<tr><td colspan="3">Nenhuma presença registrada para esta data.</td></tr>');
                    } else {
                        response.presencas.forEach(presenca => {
                            const status = presenca.presente ? 'Sim' : 'Não';
                            const statusClass = presenca.presente ? 'status-presente' : 'status-ausente';
                            tbody.append(`
                                <tr>
                                    <td>${presenca.nome_aluno}</td>
                                    <td>${presenca.matricula}</td>
                                    <td class="${statusClass}">${status}</td>
                                </tr>
                            `);
                        });
                    }
                    $('#tabelaChamadas').show();
                } else {
                    showMessage('Erro ao carregar presenças: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX presenças:', xhr.responseText);
                showMessage('Erro ao carregar presenças.', 'error');
            }
        });
    });

    // Função para exibir mensagens
    function showMessage(msg, type) {
        const box = $('#message-box');
        box.removeClass('success error').addClass(type).text(msg).fadeIn();
        setTimeout(() => box.fadeOut(), 3000);
    }
});