/* Funções para abrir e fechar o modal de exclusão de turma */
function showDeleteTurmaModal(turmaId) {
    // Restaurar o conteúdo original do modal antes de abrir
    const originalContent = `
        <h2 class="modal-title">Excluir Turma</h2>
        <p class="modal-message">Tem certeza que deseja excluir esta turma? Esta ação não pode ser desfeita.</p>
        <input type="hidden" id="delete-turma-id">
        <div class="modal-buttons">
            <button class="btn delete-btn" onclick="confirmDeleteTurma()">Excluir</button>
            <button class="btn close-modal-btn">Cancelar</button>
        </div>
    `;
    $('#modal-delete-turma .modal-content').html(originalContent);
    $('#delete-turma-id').val(turmaId);
    $('#modal-delete-turma').css('display', 'block');
}

function confirmDeleteTurma() {
    const turmaId = $('#delete-turma-id').val();
    $.ajax({
        url: 'delete_turma.php',
        method: 'POST',
        data: { turma_id: turmaId },
        dataType: 'json',
        success: function(response) {
            var modalContent = $('#modal-delete-turma .modal-content');
            if (response.success) {
                modalContent.html(`
                    <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Exclusão Concluída</h2>
                    <p class="modal-message">${response.message}</p>
                    <div class="modal-buttons">
                        <button class="btn close-modal-btn">Fechar</button>
                    </div>
                `);
                if (response.turmas_html) {
                    $('.box-turmas').html(response.turmas_html);
                    if ($('.box-turmas-single').length > 0) {
                        $('.box-turmas-single').first().click();
                    } else {
                        $('#tabela-alunos').html('<tr><td colspan="5">Nenhuma turma disponível</td></tr>');
                    }
                }
                if (response.total_turmas !== undefined) {
                    $('.overview-box:contains("Total de Turmas") h3').text(response.total_turmas);
                }
                setTimeout(function() {
                    $('#modal-delete-turma').css('display', 'none');
                }, 2000);
            } else {
                modalContent.prepend(`<p class="modal-message error">${response.message}</p>`);
            }
        },
        error: function(xhr) {
            $('#modal-delete-turma .modal-content').prepend(`<p class="modal-message error">Erro ao comunicar com o servidor: ${xhr.statusText}</p>`);
        }
    });
}

$(document).ready(function() {
    $(document).on('click', '#modal-delete-turma .close-modal-btn', function() {
        $('#modal-delete-turma').css('display', 'none');
    });
});