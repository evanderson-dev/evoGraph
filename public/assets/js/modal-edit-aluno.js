/* Responsabilidade: Gerencia o modal de edição de alunos */
$(document).ready(function() {
    $(document).on('click', '#modal-edit-aluno .close-modal-btn', function() {
        $('#modal-edit-aluno').css('display', 'none');
    });
});

function openEditAlunoModal(matricula, turmaId) {
    $.ajax({
        url: 'fetch_aluno.php',
        method: 'POST',
        data: { action: 'fetch_aluno', matricula: matricula, context: 'edit' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $.ajax({
                    url: 'fetch_turmas.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(turmaResponse) {
                        let turmaOptions = '<option value="">Selecione uma turma</option>';
                        if (turmaResponse.success && turmaResponse.turmas) {
                            turmaResponse.turmas.forEach(turma => {
                                turmaOptions += `<option value="${turma.id}" ${turma.id == response.aluno.turma_id ? 'selected' : ''}>${turma.nome} (${turma.ano})</option>`;
                            });
                        }

                        const content = `
                            <h2 class="modal-title">Editar Aluno</h2>
                            <form id="edit-aluno-form" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="edit-nome">Nome:</label>
                                    <input type="text" id="edit-nome" name="nome" value="${response.aluno.nome}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-sobrenome">Sobrenome:</label>
                                    <input type="text" id="edit-sobrenome" name="sobrenome" value="${response.aluno.sobrenome}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-data_nascimento">Data de Nascimento:</label>
                                    <input type="date" id="edit-data_nascimento" name="data_nascimento" value="${response.aluno.data_nascimento === 'N/A' ? '' : response.aluno.data_nascimento}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-matricula">Matrícula:</label>
                                    <input type="text" id="edit-matricula" name="matricula" value="${response.aluno.matricula}" readonly>
                                </div>
                                <div class="form-group full-width">
                                    <label for="edit-email">E-mail (opcional):</label>
                                    <input type="email" id="edit-email" name="email" value="${response.aluno.email || ''}">
                                </div>
                                <div class="form-group full-width">
                                    <label for="edit-foto">Foto do Aluno (opcional):</label>
                                    <input type="file" id="edit-foto" name="foto" accept="image/*">
                                </div>
                                <div class="form-group full-width">
                                    <label for="edit-nome_pai">Nome do Pai (opcional):</label>
                                    <input type="text" id="edit-nome_pai" name="nome_pai" value="${response.aluno.nome_pai || ''}">
                                </div>
                                <div class="form-group full-width">
                                    <label for="edit-nome_mae">Nome da Mãe (opcional):</label>
                                    <input type="text" id="edit-nome_mae" name="nome_mae" value="${response.aluno.nome_mae || ''}">
                                </div>
                                <div class="form-group full-width">
                                    <label for="edit-turma_id">Turma:</label>
                                    <select id="edit-turma_id" name="turma_id" required>
                                        ${turmaOptions}
                                    </select>
                                </div>
                                <input type="hidden" id="edit-data_matricula_hidden" name="data_matricula_hidden" value="${response.aluno.data_matricula === 'N/A' ? '' : response.aluno.data_matricula}">
                                <div class="modal-buttons">
                                    <button type="submit" class="btn">Salvar</button>
                                    <button type="button" class="btn close-modal-btn">Cancelar</button>
                                </div>
                            </form>
                        `;
                        $('#modal-edit-aluno .modal-content').html(content);
                        $('#modal-edit-aluno').data('turma-id', turmaId);
                        $('#modal-edit-aluno').css('display', 'block');

                        // Evento de envio do formulário
                        $('#edit-aluno-form').on('submit', function(e) {
                            e.preventDefault();

                            // Validação do e-mail
                            const email = $('#edit-email').val().trim();
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (email && !emailRegex.test(email)) {
                                const modalContent = $('#modal-edit-aluno .modal-content');
                                modalContent.html(`
                                    <h2 class="modal-title error"><i class="fa-solid fa-exclamation-circle"></i> Erro</h2>
                                    <p class="modal-message">Por favor, insira um e-mail válido (ex.: nome@dominio.com).</p>
                                    <div class="modal-buttons">
                                        <button class="btn close-modal-btn">Fechar</button>
                                    </div>
                                `);
                                return;
                            }

                            const formData = new FormData(this);
                            $.ajax({
                                url: 'edit_aluno.php',
                                method: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                dataType: 'json',
                                success: function(response) {
                                    const modalContent = $('#modal-edit-aluno .modal-content');
                                    if (response.success) {
                                        modalContent.html(`
                                            <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Sucesso</h2>
                                            <p class="modal-message">Aluno atualizado com sucesso!</p>
                                            <div class="modal-buttons">
                                                <button class="btn close-modal-btn">Fechar</button>
                                            </div>
                                        `);
                                        fetchAlunos($('#modal-edit-aluno').data('turma-id')); // Atualiza a tabela
                                        updateAllTurmas(); // Atualiza as caixas de turmas
                                        setTimeout(() => $('#modal-edit-aluno').css('display', 'none'), 2000);
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
                                    const modalContent = $('#modal-edit-aluno .modal-content');
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
                    },
                    error: function(xhr) {
                        alert('Erro ao carregar turmas: ' + xhr.statusText);
                    }
                });
            } else {
                alert('Erro ao carregar dados do aluno: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Erro ao comunicar com o servidor: ' + xhr.statusText);
        }
    });
}

// Função para atualizar todas as caixas de turmas
function updateAllTurmas() {
    $('.box-turmas-single').each(function() {
        const turmaId = $(this).data('turma-id');
        $.ajax({
            url: 'fetch_turmas.php',
            method: 'POST',
            data: { turma_id: turmaId, action: 'details' },
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

// Função para atualizar a tabela de alunos
function fetchAlunos(turmaId) {
    $.ajax({
        url: 'fetch_turmas.php',
        method: 'POST',
        data: { turma_id: turmaId, action: 'details' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#tabela-alunos').html(response.tabela_alunos);
            }
        },
        error: function() {
            console.error('Erro ao atualizar tabela de alunos');
        }
    });
}