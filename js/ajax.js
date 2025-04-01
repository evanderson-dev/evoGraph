/* Responsabilidade: Lida com requisições AJAX e eventos relacionados ao carregamento de turmas. */
function loadTurma(turmaId) {
    $.ajax({
        url: 'fetch_turmas.php',
        method: 'POST',
        data: { turma_id: turmaId, action: 'details' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#tabela-alunos').html(response.tabela_alunos);
                if (response.total_alunos !== undefined) {
                    $('#total-alunos').text(response.total_alunos);
                }
                $(`.box-turmas-single[data-turma-id="${turmaId}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
            } else {
                $('#tabela-alunos').html('<tr><td colspan="5">Erro: ' + response.message + '</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            $('#tabela-alunos').html('<tr><td colspan="5">Erro ao carregar alunos: ' + xhr.statusText + '</td></tr>');
        }
    });
}

window.editAluno = function(matricula) {
    var turmaId = $('.box-turmas-single.active').data('turma-id') || $('#tabela-alunos tr[data-matricula="' + matricula + '"]').data('turma-id');
    openEditModal(matricula, turmaId);
};

$(document).ready(function() {
    $(document).on('click', '.box-turmas-single', function() {
        $('.box-turmas-single').removeClass('active');
        $(this).addClass('active');
        var turmaId = $(this).data('turma-id');
        loadTurma(turmaId);
    });

    if ($('.box-turmas-single').length > 0) {
        $('.box-turmas-single').first().click();
    }
});