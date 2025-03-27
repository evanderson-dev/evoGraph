/* Responsabilidade: Gerencia o modal de detalhes do aluno */
$(document).on('click', '.aluno-row', function(e) {
    if (!$(e.target).hasClass('action-btn') && !$(e.target).parent().hasClass('action-btn')) {
        var matricula = $(this).data('matricula');
        var nome = $(this).data('nome');
        var nascimento = $(this).data('nascimento');
        var dataMatricula = $(this).data('matricula-data');
        var pai = $(this).data('pai');
        var mae = $(this).data('mae');

        $('#detalhes-nome').val(nome);
        $('#detalhes-nascimento').val(nascimento);
        $('#detalhes-matricula').val(matricula);
        $('#detalhes-data-matricula').val(dataMatricula);
        $('#detalhes-pai').val(pai);
        $('#detalhes-mae').val(mae);

        $('#modal-detalhes-aluno').css('display', 'block');
    }
});