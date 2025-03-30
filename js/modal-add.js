/* Responsabilidade: Gerencia o modal de cadastro de alunos */
function openAddModal() {
    $('#add-nome').val('');
    $('#add-sobrenome').val('');
    $('#add-data_nascimento').val('');
    $('#add-matricula').val('');
    $('#add-foto').val('');
    $('#add-nome_pai').val('');
    $('#add-nome_mae').val('');
    $('#add-turma_id').val('');
    $('#add-data_matricula_hidden').val('');

    // Carregar turmas
    $.get('fetch_turmas.php', function(response) {
        if (response.success) {
            let select = $('#add-turma_id');
            select.empty().append('<option value="">Selecione uma turma</option>');
            response.turmas.forEach(turma => {
                select.append(`<option value="${turma.id}">${turma.nome} (${turma.ano})</option>`);
            });
        }
    });

    $('#modal-cadastrar-aluno').css('display', 'block');
}

$(document).ready(function() {
    $('#cadastro-aluno-form').on('submit', function(e) {
        e.preventDefault();

        // Capturar data e hora
        var now = new Date();
        var year = now.getFullYear();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var seconds = String(now.getSeconds()).padStart(2, '0');
        var dataMatricula = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        $('#add-data_matricula_hidden').val(dataMatricula);

        let formData = new FormData(this);
        $.ajax({
            url: 'cadastro_aluno.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                var modalContent = $('#modal-cadastrar-aluno .modal-content');
                if (response.success) {
                    modalContent.html(`
                        <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Cadastro Concluído</h2>
                        <p class="modal-message">${response.message}</p>
                        <div class="modal-buttons">
                            <button class="btn close-modal-btn">Fechar</button>
                        </div>
                    `);
                    $('#tabela-alunos').html(response.tabela_alunos);
                    if (response.total_alunos !== undefined) {
                        $('#total-alunos').text(response.total_alunos);
                    }
                    $(`.box-turmas-single[data-turma-id="${formData.get('turma_id')}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                    setTimeout(function() {
                        $('#modal-cadastrar-aluno').css('display', 'none');
                    }, 2000);
                } else {
                    modalContent.prepend(`<p class="modal-message error">${response.message}</p>`);
                }
            },
            error: function(xhr) {
                $('#modal-cadastrar-aluno .modal-content').prepend(`<p class="modal-message error">Erro ao comunicar com o servidor: ${xhr.statusText}</p>`);
            }
        });
    });
});