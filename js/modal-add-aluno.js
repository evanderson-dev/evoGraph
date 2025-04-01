/* js/modal-add-aluno.js */
/* Responsabilidade: Gerencia o modal de cadastro de alunos */
$(document).ready(function() {
    $(document).on('click', '#modal-cadastrar-aluno .close-modal-btn', function() {
        $('#modal-cadastrar-aluno').css('display', 'none');
    });
});

function openAddModal() {
    const originalContent = `
        <h2 class="modal-title">Cadastrar Aluno</h2>
        <form id="cadastro-aluno-form" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="add-nome">Nome:</label>
                    <input type="text" id="add-nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="add-sobrenome">Sobrenome:</label>
                    <input type="text" id="add-sobrenome" name="sobrenome" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="add-data_nascimento">Data de Nascimento:</label>
                    <input type="date" id="add-data_nascimento" name="data_nascimento" required>
                </div>
                <div class="form-group">
                    <label for="add-matricula">Matrícula:</label>
                    <input type="text" id="add-matricula" name="matricula" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="add-foto">Foto (opcional):</label>
                    <input type="file" id="add-foto" name="foto" accept="image/*">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="add-nome_pai">Nome do Pai (opcional):</label>
                    <input type="text" id="add-nome_pai" name="nome_pai">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="add-nome_mae">Nome da Mãe (opcional):</label>
                    <input type="text" id="add-nome_mae" name="nome_mae">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="add-turma_id">Turma:</label>
                    <select id="add-turma_id" name="turma_id" required>
                        <option value="">Selecione uma turma</option>
                    </select>
                </div>
            </div>
            <input type="hidden" id="add-data_matricula_hidden" name="data_matricula_hidden">
            <div class="modal-buttons">
                <button type="submit" class="btn">Cadastrar</button>
                <button type="button" class="btn close-modal-btn">Cancelar</button>
            </div>
        </form>
    `;
    $('#modal-cadastrar-aluno .modal-content').html(originalContent);
    $('#modal-cadastrar-aluno').css('display', 'block');

    // Carregar turmas no select
    $.ajax({
        url: 'fetch_turmas.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let select = $('#add-turma_id');
                select.empty().append('<option value="">Selecione uma turma</option>');
                response.turmas.forEach(turma => {
                    select.append(`<option value="${turma.id}">${turma.nome} (${turma.ano})</option>`);
                });
            } else {
                $('#add-turma_id').html('<option value="">Erro ao carregar turmas</option>');
            }
        },
        error: function(xhr) {
            $('#add-turma_id').html('<option value="">Erro ao carregar turmas</option>');
        }
    });

    // Evento de envio do formulário
    $('#cadastro-aluno-form').on('submit', function(e) {
        e.preventDefault();
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
}