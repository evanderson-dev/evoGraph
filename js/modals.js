/* Responsabilidade: Gerencia os modais de detalhes, exclusão e edição de alunos. */
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

// Fechar modais
$('.close-btn, #cancel-delete-btn, .close-modal-btn').click(function() {
    $('.modal').css('display', 'none');
});

// Função para abrir modal de exclusão
window.showDeleteModal = function(matricula, turmaId) {
    $('#delete-matricula').text(matricula);
    $('#modal-confirm-delete').css('display', 'block');
    $('#confirm-delete-btn').off('click').on('click', function() {
        $.ajax({
            url: 'delete_and_fetch.php',
            method: 'POST',
            data: { matricula: matricula, turma_id: turmaId },
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
                    $('#tabela-alunos').html(response.tabela_alunos);
                    if (response.total_alunos !== undefined) {
                        $('#total-alunos').text(response.total_alunos);
                    }
                    $(`.box-turmas-single[data-turma-id="${turmaId}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                } else {
                    modalContent.html(`
                        <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                        <p class="modal-message">${response.message}</p>
                        <div class="modal-buttons">
                            <button class="btn close-modal-btn">Fechar</button>
                        </div>
                    `);
                }
                $('.close-modal-btn').click(function() {
                    $('#modal-confirm-delete').css('display', 'none');
                });
            },
            error: function() {
                $('#modal-confirm-delete .modal-content').html(`
                    <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                    <p class="modal-message">Erro ao comunicar com o servidor.</p>
                    <div class="modal-buttons">
                        <button class="btn close-modal-btn">Fechar</button>
                    </div>
                `);
                $('.close-modal-btn').click(function() {
                    $('#modal-confirm-delete').css('display', 'none');
                });
            }
        });
    });
};

// Função placeholder para Editar (será substituída por ajax.js, mas mantida como fallback)
window.editAluno = function(matricula) {
    alert('Editar aluno com matrícula: ' + matricula);
};

// Função para abrir o modal de edição e carregar os dados do aluno
window.openEditModal = function(matricula, turmaId) {
    $.ajax({
        url: 'delete_and_fetch.php',
        method: 'POST',
        data: { action: 'fetch_aluno', matricula: matricula },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                resetEditModal(); // Reseta o modal e associa eventos
                $('#edit-nome').val(response.aluno.nome);
                $('#edit-sobrenome').val(response.aluno.sobrenome);
                $('#edit-data_nascimento').val(response.aluno.data_nascimento);
                $('#edit-matricula').val(response.aluno.matricula);
                $('#edit-nome_pai').val(response.aluno.nome_pai || '');
                $('#edit-nome_mae').val(response.aluno.nome_mae || '');
                $('#edit-turma_id').val(response.aluno.turma_id);
                $('#edit-data_matricula_hidden').val(response.aluno.data_matricula);
                $('#modal-editar-aluno').data('turma-id', turmaId); // Armazena a turma atual
                $('#modal-editar-aluno').css('display', 'block');
            } else {
                alert('Erro ao carregar dados do aluno: ' + response.message);
            }
        },
        error: function() {
            alert('Erro ao comunicar com o servidor.');
        }
    });
};

// Função para resetar o modal de edição ao estado inicial e associar eventos
function resetEditModal() {
    var modalContent = $('#modal-editar-aluno .modal-content');
    modalContent.html(`
        <h2>Editar Aluno</h2>
        <div class="cadastro-form">
            <form method="POST" id="editar-aluno-form">
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
                            <!-- As opções serão preenchidas dinamicamente via fetch_turmas.php -->
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

    // Carrega as opções de turmas dinamicamente
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

    // Associa o evento de submit ao formulário recém-criado
    $('#editar-aluno-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        var turmaId = $('#modal-editar-aluno').data('turma-id');
        var novaTurmaId = $('#edit-turma_id').val();
        var formData = {
            action: 'update',
            matricula: $('#edit-matricula').val(),
            nome: $('#edit-nome').val(),
            sobrenome: $('#edit-sobrenome').val(),
            data_nascimento: $('#edit-data_nascimento').val(),
            data_matricula: $('#edit-data_matricula_hidden').val(),
            nome_pai: $('#edit-nome_pai').val() || null,
            nome_mae: $('#edit-nome_mae').val() || null,
            turma_id: novaTurmaId,
            turma_id_atual: turmaId
        };

        $.ajax({
            url: 'delete_and_fetch.php',
            method: 'POST',
            data: formData,
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
                    $('#tabela-alunos').html(response.tabela_alunos);
                    if (response.total_alunos !== undefined) {
                        $('#total-alunos').text(response.total_alunos);
                    }
                    updateAllTurmas();
                    setTimeout(function() {
                        $('#modal-editar-aluno').css('display', 'none');
                    }, 2000);
                    $('#modal-editar-aluno .close-modal-btn').click(function() {
                        $('#modal-editar-aluno').css('display', 'none');
                    });
                } else {
                    modalContent.html(`
                        <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                        <p class="modal-message">${response.message}</p>
                        <div class="modal-buttons">
                            <button class="btn close-modal-btn">Fechar</button>
                        </div>
                    `);
                    $('#modal-editar-aluno .close-modal-btn').click(function() {
                        $('#modal-editar-aluno').css('display', 'none');
                    });
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
                $('#modal-editar-aluno .close-modal-btn').click(function() {
                    $('#modal-editar-aluno').css('display', 'none');
                });
            }
        });
    });

    // Associa o evento de fechar ao botão "Cancelar"
    $('#modal-editar-aluno .close-modal-btn').off('click').on('click', function() {
        $('#modal-editar-aluno').css('display', 'none');
    });
}

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