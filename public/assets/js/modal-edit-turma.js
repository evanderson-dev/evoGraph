$(document).ready(function() {
    $(document).on('click', '#modal-edit-turma .close-modal-btn', function() {
        $('#modal-edit-turma').css('display', 'none');
    });
});

function showEditTurmaModal(turmaId) {
    $.ajax({
        url: 'fetch_turmas.php', // Alterado de fetch_turma.php
        method: 'POST',
        data: { turma_id: turmaId, action: 'edit' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const originalContent = `
                    <h2 class="modal-title">Editar Turma</h2>
                    <form id="form-edit-turma">
                        <div class="form-group">
                            <label for="nome">Nome da Turma:</label>
                            <input type="text" id="nome" name="nome" value="${response.turma.nome}" placeholder="Ex.: 5º Ano A" required>
                        </div>
                        <div class="form-group">
                            <label for="ano">Ano Escolar:</label>
                            <input type="number" id="ano" name="ano" value="${response.turma.ano}" placeholder="Ex.: 5" min="1" max="9" required>
                        </div>
                        <div class="form-group">
                            <label for="professor_id">Professor:</label>
                            <select id="professor_id" name="professor_id" required></select>
                        </div>
                        <input type="hidden" name="turma_id" value="${turmaId}">
                        <div class="modal-buttons">
                            <button type="submit" class="btn">Salvar</button>
                            <button type="button" class="btn close-modal-btn">Cancelar</button>
                        </div>
                    </form>
                `;
                $('#modal-edit-turma .modal-content').html(originalContent);
                $('#modal-edit-turma').css('display', 'block');

                $.ajax({
                    url: 'fetch_professores.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(profResponse) {
                        if (profResponse.success) {
                            let options = '<option value="">Selecione um professor</option>';
                            profResponse.professores.forEach(function(professor) {
                                const selected = professor.id == response.turma.professor_id ? 'selected' : '';
                                options += `<option value="${professor.id}" ${selected}>${professor.nome} ${professor.sobrenome}</option>`;
                            });
                            $('#professor_id').html(options);
                        } else {
                            $('#professor_id').html('<option value="">Erro ao carregar professores</option>');
                        }
                    },
                    error: function(xhr) {
                        $('#professor_id').html('<option value="">Erro ao carregar professores</option>');
                    }
                });

                $('#form-edit-turma').on('submit', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: 'edit_turma.php',
                        method: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(editResponse) {
                            var modalContent = $('#modal-edit-turma .modal-content');
                            if (editResponse.success) {
                                modalContent.html(`
                                    <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Edição Concluída</h2>
                                    <p class="modal-message">${editResponse.message}</p>
                                    <div class="modal-buttons">
                                        <button class="btn close-modal-btn">Fechar</button>
                                    </div>
                                `);
                                if (editResponse.turmas_html) {
                                    $('.box-turmas').html(editResponse.turmas_html);
                                    if ($('.box-turmas-single').length > 0) {
                                        $('.box-turmas-single').first().click();
                                    }
                                }
                                setTimeout(function() {
                                    $('#modal-edit-turma').css('display', 'none');
                                }, 2000);
                            } else {
                                modalContent.prepend(`<p class="modal-message error">${editResponse.message}</p>`);
                            }
                        },
                        error: function(xhr) {
                            $('#modal-edit-turma .modal-content').prepend(`<p class="modal-message error">Erro ao comunicar com o servidor: ${xhr.statusText}</p>`);
                        }
                    });
                });
            } else {
                alert('Erro ao carregar dados da turma: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Erro ao carregar dados da turma: ' + xhr.statusText);
        }
    });
}