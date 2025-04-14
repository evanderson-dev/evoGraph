/* js/modal-delete-aluno.js */
/* Responsabilidade: Gerencia o modal de exclusão de alunos */
$(document).ready(function() {
    $(document).on('click', '#modal-delete-aluno .close-modal-btn', function() {
        $('#modal-delete-aluno').css('display', 'none');
    });
});

function showDeleteAlunoModal(matricula, turmaId) {
    // Buscar o nome do aluno antes de abrir o modal
    $.ajax({
        url: 'fetch_aluno.php',
        method: 'POST',
        data: { action: 'fetch_aluno', matricula: matricula, context: 'details' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const alunoNome = `${response.aluno.nome} ${response.aluno.sobrenome}`;
                const content = `
                    <h2 class="modal-title">Confirmar Exclusão</h2>
                    <p class="modal-message">Deseja realmente excluir o aluno<br><span class="highlight-aluno">${alunoNome}</span><br> (matrícula: <span class="highlight-matricula">${matricula}</span>)?</p>
                    <div class="modal-buttons">
                        <button class="btn confirm-btn" id="confirm-delete-btn">Confirmar</button>
                        <button class="btn cancel-btn" id="cancel-delete-btn">Cancelar</button>
                    </div>
                `;
                $('#modal-delete-aluno .modal-content').html(content);
                $('#modal-delete-aluno').css('display', 'block');

                $('#confirm-delete-btn').on('click', function() {
                    $.ajax({
                        url: 'delete_aluno.php',
                        method: 'POST',
                        data: { action: 'delete', matricula: matricula, turma_id: turmaId },
                        dataType: 'json',
                        success: function(response) {
                            const modalContent = $('#modal-delete-aluno .modal-content');
                            if (response.success) {
                                modalContent.html(`
                                    <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Exclusão Concluída</h2>
                                    <p class="modal-message">${response.message}</p>
                                    <div class="modal-buttons">
                                        <button class="btn close-modal-btn">Fechar</button>
                                    </div>
                                `);
                                $('#tabela-alunos').html(response.tabela_alunos);
                                if (response.total_alunos !== undefined) {
                                    $('#total-alunos').text(response.total_alunos);
                                }
                                $(`.box-turmas-single[data-turma-id="${turmaId}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                                setTimeout(() => $('#modal-delete-aluno').css('display', 'none'), 2000);
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
                        error: function(xhr) {
                            const modalContent = $('#modal-delete-aluno .modal-content');
                            modalContent.html(`
                                <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                                <p class="modal-message">Erro ao comunicar com o servidor: ${xhr.statusText}</p>
                                <div class="modal-buttons">
                                    <button class="btn close-modal-btn">Fechar</button>
                                </div>
                            `);
                        }
                    });
                });

                $('#cancel-delete-btn').on('click', function() {
                    $('#modal-delete-aluno').css('display', 'none');
                });
            } else {
                alert('Erro ao carregar dados do aluno: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Erro ao buscar nome do aluno: ' + xhr.statusText);
        }
    });
}