$(document).on('click', '.aluno-row', function(e) {
    if (!$(e.target).hasClass('action-btn') && !$(e.target).parent().hasClass('action-btn')) {
        var matricula = $(this).data('matricula');
        var nome = $(this).data('nome');
        var nascimento = $(this).data('nascimento');
        var dataMatricula = $(this).data('matricula-data');
        var pai = $(this).data('pai');
        var mae = $(this).data('mae');
        var turma = $(this).data('turma-nome');
        var professor = $(this).data('professor');
        var turmaId = $(this).data('turma-id');

        $('#detalhes-nome').val(nome);
        $('#detalhes-nascimento').val(nascimento);
        $('#detalhes-matricula').val(matricula);
        $('#detalhes-data-matricula').val(dataMatricula);
        $('#detalhes-pai').val(pai);
        $('#detalhes-mae').val(mae);
        $('#detalhes-turma').val(turma);
        $('#detalhes-professor').val(professor);

        $('#modal-detalhes-aluno').data('turma-id', turmaId); // Armazenar turma atual para edição
        $('#modal-detalhes-aluno').css('display', 'block');
    }
});

// Fechar modais
$('.close-btn, #cancel-delete-btn, .close-modal-btn').click(function() {
    $('.modal').css('display', 'none');
    resetModal(); // Resetar para modo visualização
});

// Função para abrir modal de exclusão
window.showDeleteModal = function(matricula, turmaId) {
    $('#delete-matricula').text(matricula);
    $('#modal-confirm-delete').css('display', 'block');
    $('#confirm-delete-btn').off('click').on('click', function() {
        $.ajax({
            url: 'delete_and_fetch.php',
            method: 'POST',
            data: { matricula: matricula, turma_id: turmaId, action: 'delete' },
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

// Função para alternar para modo de edição
$('#edit-aluno-btn').click(function() {
    var form = $('#detalhes-form');
    form.find('input').prop('readonly', false);
    $('#detalhes-matricula').prop('readonly', true); // Matrícula não editável

    // Substituir input de turma por select
    var turmaAtualId = $('#modal-detalhes-aluno').data('turma-id');
    $.ajax({
        url: 'fetch_turmas.php', // Novo arquivo para listar turmas
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            var select = '<select id="detalhes-turma-select">';
            response.turmas.forEach(function(turma) {
                select += `<option value="${turma.id}" ${turma.id == turmaAtualId ? 'selected' : ''}>${turma.nome} (${turma.ano})</option>`;
            });
            select += '</select>';
            $('#detalhes-turma').replaceWith(select);
        },
        error: function() {
            alert('Erro ao carregar turmas para edição.');
        }
    });

    // Mudar botão "Editar" para "Salvar" e "Cancelar"
    $(this).replaceWith(`
        <button class="btn" id="save-aluno-btn">Salvar</button>
        <button class="btn" id="cancel-edit-btn">Cancelar</button>
    `);

    // Salvar alterações
    $('#save-aluno-btn').click(function() {
        var matricula = $('#detalhes-matricula').val();
        var nomeSobrenome = $('#detalhes-nome').val().split(' ');
        var nome = nomeSobrenome[0];
        var sobrenome = nomeSobrenome.slice(1).join(' ') || '';
        var data_nascimento = $('#detalhes-nascimento').val();
        var data_matricula = $('#detalhes-data-matricula').val();
        var nome_pai = $('#detalhes-pai').val();
        var nome_mae = $('#detalhes-mae').val();
        var turma_id_nova = $('#detalhes-turma-select').val();
        var turma_id_atual = $('#modal-detalhes-aluno').data('turma-id');

        $.ajax({
            url: 'delete_and_fetch.php',
            method: 'POST',
            data: {
                action: 'update',
                matricula: matricula,
                nome: nome,
                sobrenome: sobrenome,
                data_nascimento: data_nascimento,
                data_matricula: data_matricula,
                nome_pai: nome_pai,
                nome_mae: nome_mae,
                turma_id: turma_id_atual,
                turma_id_nova: turma_id_nova
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Aluno atualizado com sucesso!');
                    $('#modal-detalhes-aluno').css('display', 'none');
                    $('#tabela-alunos').html(response.tabela_alunos);
                    if (response.total_alunos !== undefined) {
                        $('#total-alunos').text(response.total_alunos);
                    }
                    $(`.box-turmas-single[data-turma-id="${turma_id_atual}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                } else {
                    alert('Erro: ' + response.message);
                }
            },
            error: function() {
                alert('Erro ao salvar as alterações.');
            }
        });
    });

    // Cancelar edição
    $('#cancel-edit-btn').click(function() {
        $('#modal-detalhes-aluno').css('display', 'none');
        resetModal();
    });
});

// Função para resetar o modal ao modo visualização
function resetModal() {
    var form = $('#detalhes-form');
    form.find('input').prop('readonly', true);
    $('#detalhes-turma-select').replaceWith('<input type="text" id="detalhes-turma" readonly>');
    $('#save-aluno-btn, #cancel-edit-btn').replaceWith('<button class="btn" id="edit-aluno-btn">Editar</button>');
}

// Função placeholder para Editar (substituída pelo botão no modal)
window.editAluno = function(matricula) {
    $(`tr[data-matricula="${matricula}"]`).click(); // Simula clique na linha
};