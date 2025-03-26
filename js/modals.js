/* Responsabilidade: Gerencia os modais de detalhes e exclusão, além da função de edição. */
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
                    // Atualizar a tabela, total geral e quantidade da turma
                    $('#tabela-alunos').html(response.tabela_alunos);
                    if (response.total_alunos !== undefined) { // Só atualizar se retornado (Diretor)
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

// Função placeholder para Editar
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

// Fechar o modal de edição
$('#modal-editar-aluno .close-modal-btn').click(function() {
    $('#modal-editar-aluno').css('display', 'none');
});

// Processar o formulário de edição
$('#editar-aluno-form').submit(function(e) {
    e.preventDefault();
    var turmaId = $('#modal-editar-aluno').data('turma-id');
    var formData = {
        action: 'update',
        matricula: $('#edit-matricula').val(),
        nome: $('#edit-nome').val(),
        sobrenome: $('#edit-sobrenome').val(),
        data_nascimento: $('#edit-data_nascimento').val(),
        data_matricula: $('#edit-data_matricula_hidden').val(),
        nome_pai: $('#edit-nome_pai').val() || null,
        nome_mae: $('#edit-nome_mae').val() || null,
        turma_id: $('#edit-turma_id').val(),
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
                // Substitui o conteúdo do modal por uma mensagem de sucesso
                modalContent.html(`
                    <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Sucesso</h2>
                    <p class="modal-message">Aluno atualizado com sucesso!</p>
                    <div class="modal-buttons">
                        <button class="btn close-modal-btn">Fechar</button>
                    </div>
                `);
                // Atualiza a tabela e as turmas
                $('#tabela-alunos').html(response.tabela_alunos);
                if (response.total_alunos !== undefined) {
                    $('#total-alunos').text(response.total_alunos);
                }
                $(`.box-turmas-single[data-turma-id="${turmaId}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                
                // Fecha o modal automaticamente após 2 segundos
                setTimeout(function() {
                    $('#modal-editar-aluno').css('display', 'none');
                }, 2000);
                
                // Reassocia o evento de fechar ao novo botão
                $('#modal-editar-aluno .close-modal-btn').click(function() {
                    $('#modal-editar-aluno').css('display', 'none');
                });
            } else {
                // Exibe erro no mesmo modal
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
        error: function() {
            var modalContent = $('#modal-editar-aluno .modal-content');
            modalContent.html(`
                <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                <p class="modal-message">Erro ao comunicar com o servidor.</p>
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