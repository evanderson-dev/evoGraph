/* Responsabilidade: Gerencia o modal de detalhes do aluno */
$(document).on('click', '.aluno-row', function(e) {
    if (!$(e.target).hasClass('action-btn') && !$(e.target).parent().hasClass('action-btn')) {
        var matricula = $(this).data('matricula');

        $.ajax({
            url: 'delete_and_fetch.php',
            method: 'POST',
            data: { action: 'fetch_aluno', matricula: matricula, context: 'details' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var aluno = response.aluno;
                    var nomeCompleto = aluno.nome + " " + aluno.sobrenome;
                    var dataNascimento = aluno.data_nascimento;
                    var dataMatricula = aluno.data_matricula;
                    var turmaNome = aluno.turma_nome || 'Sem turma';
            
                    $('#detalhes-nome').val(nomeCompleto);
                    $('#detalhes-nascimento').val(dataNascimento);
                    $('#detalhes-matricula').val(aluno.matricula);
                    $('#detalhes-data-matricula').val(dataMatricula);
                    $('#detalhes-pai').val(aluno.nome_pai || 'N/A');
                    $('#detalhes-mae').val(aluno.nome_mae || 'N/A');
                    $('#detalhes-turma').val(turmaNome);
            
                    if (aluno.foto) {
                        $('#detalhes-foto').attr('src', aluno.foto);
                    } else {
                        $('#detalhes-foto').attr('src', '.img/default-photo.jpg');
                    }
            
                    $('#modal-detalhes-aluno').css('display', 'block');
                } else {
                    alert('Erro ao carregar detalhes do aluno: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Erro ao comunicar com o servidor: ' + xhr.statusText);
            }
        });
    }
});