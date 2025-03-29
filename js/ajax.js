/* Responsabilidade: Lida com requisições AJAX e eventos relacionados ao carregamento de turmas. */
function loadTurma(turmaId) {
    $.ajax({
        url: 'fetch_turma_data.php',
        method: 'POST',
        data: { turma_id: turmaId },
        dataType: 'json',
        success: function(response) {
            console.log(response);
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

// Função para abrir o modal de edição (chamada pelo botão na tabela)
window.editAluno = function(matricula) {
    var turmaId = $('.box-turmas-single.active').data('turma-id') || $('#tabela-alunos tr[data-matricula="' + matricula + '"]').data('turma-id');
    openEditModal(matricula, turmaId); // Chama a função do modals.js
};

$(document).ready(function() {
    // Clique nas turmas
    $('.box-turmas-single').click(function() {
        $('.box-turmas-single').removeClass('active'); // Remove a classe active de todas as turmas
        $(this).addClass('active'); // Adiciona a classe active à turma clicada
        var turmaId = $(this).data('turma-id');
        loadTurma(turmaId);
    });

    // Carrega a primeira turma automaticamente ao abrir o dashboard
    if ($('.box-turmas-single').length > 0) {
        $('.box-turmas-single').first().click();
    }
});