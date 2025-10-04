$(document).ready(function() {
    const funcionarioId = JSON.parse('<?= json_encode($funcionario_id ?? null); ?>');
    let selectedTurmaId = null;
    let selectedData = null;

    // Carregar turmas do professor
    $.ajax({
        url: 'fetch_turmas.php',
        method: 'POST',
        data: { professor_id: funcionarioId, action: 'list' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const select = $('#turmaSelect');
                select.empty().append('<option value="">Selecione uma turma</option>');
                response.turmas.forEach(turma => {
                    select.append(`<option value="${turma.id}">${turma.nome} (${turma.ano}º Ano)</option>`);
                });
                $('#loadAlunosBtn').prop('disabled', false);
            } else {
                showMessage('Erro ao carregar turmas: ' + response.message, 'error');
            }
        },
        error: function() {
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
        $('#loadAlunosBtn').prop('disabled', !selectedTurmaId || !selectedData);
    }

    // Evento para carregar alunos
    $('#loadAlunosBtn').click(function() {
        if (!selectedTurmaId || !selectedData) return;

        $.ajax({
            url: 'fetch_turmas.php',
            method: 'POST',
            data: { turma_id: selectedTurmaId, action: 'alunos' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const container = $('.alunos-container');
                    container.empty();
                    response.alunos.forEach(aluno => {
                        // Verificar presença existente para pré-marcar
                        const isPresente = response.presencas[aluno.matricula] || true; // Default true
                        container.append(`
                            <div class="aluno-item">
                                <span>${aluno.nome} ${aluno.sobrenome} (${aluno.matricula})</span>
                                <label class="checkbox-label">
                                    <input type="checkbox" class="presenca-checkbox" data-matricula="${aluno.matricula}" ${isPresente ? 'checked' : ''}>
                                    Presente
                                </label>
                            </div>
                        `);
                    });
                    $('#alunosList').show();
                    $('#salvarChamadaBtn').prop('disabled', false);
                } else {
                    showMessage('Erro ao carregar alunos: ' + response.message, 'error');
                }
            },
            error: function() {
                showMessage('Erro ao carregar alunos.', 'error');
            }
        });
    });

    // Evento para salvar chamada
    $('#salvarChamadaBtn').click(function() {
        const presencas = [];
        $('.presenca-checkbox').each(function() {
            const matricula = $(this).data('matricula');
            const presente = $(this).is(':checked');
            presencas.push({ matricula, presente });
        });

        $.ajax({
            url: 'salvar_chamada.php',
            method: 'POST',
            data: { turma_id: selectedTurmaId, data: selectedData, presencas: JSON.stringify(presencas) },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    $('#salvarChamadaBtn').prop('disabled', true);
                } else {
                    showMessage('Erro ao salvar chamada: ' + response.message, 'error');
                }
            },
            error: function() {
                showMessage('Erro ao salvar chamada.', 'error');
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