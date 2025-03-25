/* Responsabilidade: Lida com requisições AJAX e eventos relacionados ao carregamento de turmas. */
function loadTurma(turmaId) {
    $.ajax({
        url: 'delete_and_fetch.php',
        method: 'POST',
        data: { turma_id: turmaId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#tabela-alunos').html(response.tabela_alunos);
                if (response.total_alunos !== undefined) { // Só atualizar se retornado (Diretor)
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

$(document).ready(function() {
    // Clique nas turmas
    $('.box-turmas-single').click(function() {
        var turmaId = $(this).data('turma-id');
        loadTurma(turmaId);
    });

    if ($('.box-turmas-single').length > 0) {
        $('.box-turmas-single').first().click();
    }
});