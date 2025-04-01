/* Responsabilidade: Funções utilitárias comuns */
$(document).on('click', '.close-btn, #cancel-delete-btn, .close-modal-btn', function() {
    $('.modal').css('display', 'none');
});

function fetchAlunos(turmaId) {
    $.ajax({
        url: 'fetch_turmas.php',
        method: 'POST',
        data: { turma_id: turmaId, action: 'details' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#tabela-alunos').html(response.tabela_alunos);
                $('#total-alunos').text(response.total_alunos);
            }
        },
        error: function() {
            console.error('Erro ao atualizar tabela de alunos.');
        }
    });
}