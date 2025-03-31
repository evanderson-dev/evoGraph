/* js/modal-add-turma.js */
/* Responsabilidade: Gerencia o modal de cadastro de turmas */
function openAddTurmaModal() {
    $('#add-turma-nome').val('');
    $('#add-turma-ano').val('');
    $('#add-professor-id').val('');

    // Carregar professores
    $.ajax({
        url: 'fetch_professores.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let select = $('#add-professor-id');
                select.empty().append('<option value="">Selecione um professor</option>');
                response.professores.forEach(professor => {
                    select.append(`<option value="${professor.id}">${professor.nome} ${professor.sobrenome}</option>`);
                });
            } else {
                $('#add-professor-id').empty().append('<option value="">Erro ao carregar professores</option>');
            }
        },
        error: function(xhr) {
            $('#add-professor-id').empty().append('<option value="">Erro ao carregar professores</option>');
        }
    });

    $('#modal-cadastrar-turma').css('display', 'block');
}

$(document).ready(function() {
    $('#cadastro-turma-form').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append('nova-turma', 'true'); // Mantém a lógica do cadastro_turma.php

        $.ajax({
            url: 'cadastro_turma.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
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
                    // Atualizar a lista de turmas no dashboard
                    if (response.turmas_html) {
                        $('.box-turmas').html(response.turmas_html);
                        if ($('.box-turmas-single').length > 0) {
                            $('.box-turmas-single').first().click(); // Carrega a primeira turma
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
});