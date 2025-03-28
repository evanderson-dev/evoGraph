/* Responsabilidade: Gerencia o modal de edição de alunos */
window.editAluno = function(matricula) {
    alert('Editar aluno com matrícula: ' + matricula); // Pode ser removido se não for mais necessário
};

function resetEditModal() {
    var modalContent = $('#modal-editar-aluno .modal-content');
    modalContent.html(`
        <h2>Editar Aluno</h2>
        <div class="cadastro-form">
            <form method="POST" id="editar-aluno-form" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-nome">Nome:</label>
                        <input type="text" id="edit-nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-sobrenome">Sobrenome:</label>
                        <input type="text" id="edit-sobrenome" name="sobrenome" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-data_nascimento">Data de Nascimento:</label>
                        <input type="date" id="edit-data_nascimento" name="data_nascimento" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-matricula">Matrícula:</label>
                        <input type="text" id="edit-matricula" name="matricula" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="edit-foto">Foto do Aluno:</label>
                        <input type="file" id="edit-foto" name="foto" accept="image/*">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="edit-nome_pai">Nome do Pai (opcional):</label>
                        <input type="text" id="edit-nome_pai" name="nome_pai">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="edit-nome_mae">Nome da Mãe (opcional):</label>
                        <input type="text" id="edit-nome_mae" name="nome_mae">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="edit-turma_id">Turma:</label>
                        <select id="edit-turma_id" name="turma_id" required>
                            <option value="">Selecione uma turma</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" id="edit-data_matricula_hidden" name="data_matricula_hidden">
                <div class="form-buttons">
                    <button type="submit" class="btn">Salvar</button>
                    <button type="button" class="btn close-modal-btn">Cancelar</button>
                </div>
            </form>
        </div>
    `);

    $.ajax({
        url: 'fetch_turmas.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var select = $('#edit-turma_id');
                select.empty();
                select.append('<option value="">Selecione uma turma</option>');
                response.turmas.forEach(function(turma) {
                    select.append(`<option value="${turma.id}">${turma.nome} (${turma.ano})</option>`);
                });
            }
        },
        error: function() {
            console.error('Erro ao carregar turmas.');
        }
    });
}

window.openEditModal = function(matricula, turmaId) {
    $.ajax({
        url: 'delete_and_fetch.php',
        method: 'POST',
        data: { action: 'fetch_aluno', matricula: matricula, context: 'edit' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                resetEditModal();
                $('#edit-nome').val(response.aluno.nome);
                $('#edit-sobrenome').val(response.aluno.sobrenome);
                $('#edit-data_nascimento').val(response.aluno.data_nascimento === 'N/A' ? '' : response.aluno.data_nascimento);
                $('#edit-matricula').val(response.aluno.matricula);
                $('#edit-nome_pai').val(response.aluno.nome_pai || '');
                $('#edit-nome_mae').val(response.aluno.nome_mae || '');
                $('#edit-turma_id').val(response.aluno.turma_id);
                $('#edit-data_matricula_hidden').val(response.aluno.data_matricula === 'N/A' ? '' : response.aluno.data_matricula);
                $('#modal-editar-aluno').data('turma-id', turmaId);
                $('#modal-editar-aluno').css('display', 'block');

                $('#editar-aluno-form').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    var formData = new FormData(this); // Inclui todos os campos, incluindo a foto

                    $.ajax({
                        url: 'edit_aluno.php', // Novo endpoint para edição com upload
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(response) {
                            var modalContent = $('#modal-editar-aluno .modal-content');
                            if (response.success) {
                                modalContent.html(`
                                    <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Sucesso</h2>
                                    <p class="modal-message">Aluno atualizado com sucesso!</p>
                                    <div class="modal-buttons">
                                        <button class="btn close-modal-btn">Fechar</button>
                                    </div>
                                `);
                                fetchAlunos($('#modal-editar-aluno').data('turma-id')); // Atualiza a tabela
                                updateAllTurmas(); // Atualiza as caixas de turmas
                                setTimeout(function() {
                                    $('#modal-editar-aluno').css('display', 'none');
                                }, 2000);
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
                            var modalContent = $('#modal-editar-aluno .modal-content');
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
            } else {
                alert('Erro ao carregar dados do aluno: ' + response.message);
            }
        },
        error: function() {
            alert('Erro ao comunicar com o servidor.');
        }
    });
};

// Função para atualizar todas as caixas de turmas
function updateAllTurmas() {
    $('.box-turmas-single').each(function() {
        var turmaId = $(this).data('turma-id');
        $.ajax({
            url: 'delete_and_fetch.php',
            method: 'POST',
            data: { turma_id: turmaId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $(`.box-turmas-single[data-turma-id="${turmaId}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                }
            },
            error: function() {
                console.error('Erro ao atualizar contagem da turma ' + turmaId);
            }
        });
    });
}

// Fechar o modal (mantido globalmente)
$(document).on('click', '.close-modal-btn', function() {
    $('#modal-editar-aluno').css('display', 'none');
});