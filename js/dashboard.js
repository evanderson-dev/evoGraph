$(document).ready(function() {
    if (localStorage.getItem('sidebarActive') === 'true') {
        $('#sidebar').addClass('active');
        $('#content').addClass('shifted');
    }

    $('#menu-toggle').on('click', function() {
        $('#sidebar').addClass('transition-enabled');
        $('#content').addClass('transition-enabled');
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('shifted');
        localStorage.setItem('sidebarActive', $('#sidebar').hasClass('active'));
        setTimeout(function() {
            $('#sidebar').removeClass('transition-enabled');
            $('#content').removeClass('transition-enabled');
        }, 300);
    });

    // Função para carregar os dados da turma
    function loadTurma(turmaId) {
        $.ajax({
            url: 'delete_and_fetch.php',
            method: 'POST',
            data: { turma_id: turmaId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#tabela-alunos').html(response.tabela_alunos);
                    $('#total-alunos').text(response.total_alunos);
                    $(`.box-turmas-single[data-turma-id="${turmaId}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                } else {
                    $('#tabela-alunos').html('<tr><td colspan="5">Erro: ' + response.message + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                $('#tabela-alunos').html('<tr><td colspan="5">Erro ao carregar alunos: ' + xhr.statusText + '</td></tr>');
            }
        });
    }

    // Clique nas turmas
    $('.box-turmas-single').click(function() {
        var turmaId = $(this).data('turma-id');
        loadTurma(turmaId);
    });

    if ($('.box-turmas-single').length > 0) {
        $('.box-turmas-single').first().click();
    }

    // Clique na linha do aluno para abrir modal de detalhes
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
                        $('#total-alunos').text(response.total_alunos);
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
});