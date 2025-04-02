/* Responsabilidade: Gerencia o modal de exclusão de alunos */
function resetDeleteModal() {
    var modalContent = $('#modal-confirm-delete .modal-content');
    modalContent.html(`
        <h2 class="modal-title">Confirmar Exclusão</h2>
        <p class="modal-message">Deseja realmente excluir o aluno com matrícula <span id="delete-matricula"></span>?</p>
        <div class="modal-buttons">
            <button class="btn" id="confirm-delete-btn">Confirmar</button>
            <button class="btn" id="cancel-delete-btn">Cancelar</button>
        </div>
    `);
}

window.showDeleteModal = function(matricula, turmaId) {
    resetDeleteModal();
    $('#delete-matricula').text(matricula);
    $('#modal-confirm-delete').css('display', 'block');
    
    $('#confirm-delete-btn').off('click').on('click', function() {
        $.ajax({
            url: 'delete_aluno.php',
            method: 'POST',
            data: { 
                action: 'delete',
                matricula: matricula, 
                turma_id: turmaId 
            },
            dataType: 'json',
            success: function(response) {
                var modalContent = $('#modal-confirm-delete .modal-content');
                if (response.success) {
                    modalContent.html(`
                        <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Exclusão Concluída</h2>
                        <p class="modal-message">${response.message}</p>
                        <div class="modal-buttons">
                            <button class="btn close-modal-btn">Fechar</button>
                        </div>
                    `);
                    // Atualizar a tabela e contagens imediatamente
                    $('#tabela-alunos').html(response.tabela_alunos);
                    if (response.total_alunos !== undefined) {
                        $('#total-alunos').text(response.total_alunos);
                    }
                    $(`.box-turmas-single[data-turma-id="${turmaId}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                    setTimeout(function() {
                        $('#modal-confirm-delete').css('display', 'none');
                    }, 2000); // Fechar o modal após 2 segundos
                } else {
                    modalContent.html(`
                        <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                        <p class="modal-message">${response.message}</p>
                        <div class="modal-buttons">
                            <button class="btn close-modal-btn">Fechar</button>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                $('#modal-confirm-delete .modal-content').html(`
                    <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                    <p class="modal-message">Erro ao comunicar com o servidor: ${xhr.statusText}</p>
                    <div class="modal-buttons">
                        <button class="btn close-modal-btn">Fechar</button>
                    </div>
                `);
            }
        });
    });

    $('#cancel-delete-btn').off('click').on('click', function() {
        $('#modal-confirm-delete').css('display', 'none');
    });
};