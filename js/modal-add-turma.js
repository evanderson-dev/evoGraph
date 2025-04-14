/* js/modal-add-turma.js */
/* Responsabilidade: Gerencia o modal de cadastro de turmas */
$(document).ready(function() {
    // Fechar modal ao clicar em Cancelar ou Fechar
    $(document).on('click', '#modal-cadastrar-turma .close-modal-btn', function() {
        $('#modal-cadastrar-turma').css('display', 'none');
    });
});

function openAddTurmaModal() {
    // Restaurar o conteúdo original do modal antes de abrir
    const originalContent = `
        <h2 class="modal-title">Cadastrar Turma</h2>
        <form id="form-add-turma">
            <div class="form-group">
                <label for="nome">Nome da Turma:</label>
                <input type="text" id="nome" name="nome" placeholder="Ex.: 5º Ano A" required>
            </div>
            <div class="form-group">
                <label for="ano">Ano Escolar:</label>
                <input type="number" id="ano" name="ano" placeholder="Ex.: 5" min="1" max="9" required>
            </div>
            <div class="form-group">
                <label for="professor_id">Professor:</label>
                <select id="professor_id" name="professor_id" required></select>
            </div>
            <input type="hidden" name="nova-turma" value="1">
            <div class="modal-buttons">
                <button type="submit" class="btn">Cadastrar</button>
                <button type="button" class="btn close-modal-btn">Cancelar</button>
            </div>
        </form>
    `;
    $('#modal-cadastrar-turma .modal-content').html(originalContent);
    
    // Carregar professores no select após recriar o formulário
    $.ajax({
        url: 'fetch_professores.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Selecione um professor</option>';
                response.professores.forEach(function(professor) {
                    options += `<option value="${professor.id}">${professor.nome} ${professor.sobrenome}</option>`;
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

    $('#modal-cadastrar-turma').css('display', 'block');

    // Reaplicar o evento de submit após recriar o formulário
    $('#form-add-turma').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'cadastro_turma.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                var modalContent = $('#modal-cadastrar-turma .modal-content');
                if (response.success) {
                    modalContent.html(`
                        <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Cadastro Concluído</h2>
                        <p class="modal-message">${response.message}</p>
                        <div class="modal-buttons">
                            <button class="btn close-modal-btn">Fechar</button>
                        </div>
                    `);
                    if (response.turmas_html) {
                        $('.box-turmas').html(response.turmas_html);
                        if ($('.box-turmas-single').length > 0) {
                            $('.box-turmas-single').first().click();
                        }
                    }
                    if (response.total_turmas !== undefined) {
                        $('.overview-box:contains("Total de Turmas") h3').text(response.total_turmas);
                    }
                    setTimeout(function() {
                        $('#modal-cadastrar-turma').css('display', 'none');
                    }, 2000);
                } else {
                    modalContent.prepend(`<p class="modal-message error">${response.message}</p>`);
                }
            },
            error: function(xhr) {
                $('#modal-cadastrar-turma .modal-content').prepend(`<p class="modal-message error">Erro ao comunicar com o servidor: ${xhr.statusText}</p>`);
            }
        });
    });
}