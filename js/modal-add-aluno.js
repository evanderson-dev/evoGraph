/* js/modal-add-aluno.js */
/* Responsabilidade: Gerencia o modal de cadastro de alunos */
$(document).ready(function() {
    $(document).on('click', '#modal-add-aluno .close-modal-btn', function() {
        $('#modal-add-aluno').css('display', 'none');
    });
});

function openAddModal() {
    // Carregar turmas dinamicamente
    $.ajax({
        url: 'fetch_turmas.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            let turmaOptions = '<option value="">Selecione uma turma</option>';
            if (response.success && response.turmas) {
                response.turmas.forEach(turma => {
                    turmaOptions += `<option value="${turma.id}">${turma.nome} (${turma.ano})</option>`;
                });
            } else {
                turmaOptions = '<option value="">Erro ao carregar turmas</option>';
            }

            // Buscar próxima matrícula disponível
            $.ajax({
                url: 'fetch_next_matricula.php',
                method: 'GET',
                dataType: 'json',
                success: function(matriculaResponse) {
                    let nextMatricula = '0000000001'; // Valor padrão em caso de erro
                    if (matriculaResponse.success && matriculaResponse.matricula) {
                        nextMatricula = matriculaResponse.matricula;
                    }

                    const originalContent = `
                        <h2 class="modal-title">Cadastrar Aluno</h2>
                        <form id="cadastro-aluno-form" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="add-nome">Nome:</label>
                                <input type="text" id="add-nome" name="nome" placeholder="Ex.: João" required>
                            </div>
                            <div class="form-group">
                                <label for="add-sobrenome">Sobrenome:</label>
                                <input type="text" id="add-sobrenome" name="sobrenome" placeholder="Ex.: Silva" required>
                            </div>
                            <div class="form-group">
                                <label for="add-data_nascimento">Data de Nascimento:</label>
                                <input type="date" id="add-data_nascimento" name="data_nascimento" required>
                            </div>
                            <div class="form-group">
                                <label for="add-matricula">Matrícula:</label>
                                <input type="text" id="add-matricula" name="matricula" value="${nextMatricula}" required>
                            </div>
                            <div class="form-group">
                                <label for="add-email">E-mail (opcional):</label>
                                <input type="email" id="add-email" name="email" placeholder="Ex.: joao.silva@email.com">
                            </div>
                            <div class="form-group">
                                <label for="add-foto">Foto (opcional):</label>
                                <input type="file" id="add-foto" name="foto" accept="image/*">
                            </div>
                            <div class="form-group full-width">
                                <label for="add-nome_pai">Nome do Pai (opcional):</label>
                                <input type="text" id="add-nome_pai" name="nome_pai" placeholder="Ex.: José Silva">
                            </div>
                            <div class="form-group full-width">
                                <label for="add-nome_mae">Nome da Mãe (opcional):</label>
                                <input type="text" id="add-nome_pai" name="nome_mae" placeholder="Ex.: Maria Silva">
                            </div>
                            <div class="form-group full-width">
                                <label for="add-turma_id">Turma:</label>
                                <select id="add-turma_id" name="turma_id" required>
                                    ${turmaOptions}
                                </select>
                            </div>
                            <input type="hidden" id="add-data_matricula_hidden" name="data_matricula_hidden">
                            <div class="modal-buttons">
                                <button type="submit" class="btn">Cadastrar</button>
                                <button type="button" class="btn close-modal-btn">Cancelar</button>
                            </div>
                        </form>
                    `;
                    $('#modal-add-aluno .modal-content').html(originalContent);
                    $('#modal-add-aluno').css('display', 'block');

                    // Evento de envio do formulário
                    $('#cadastro-aluno-form').on('submit', function(e) {
                        e.preventDefault();

                        // Validação do e-mail
                        const email = $('#add-email').val().trim();
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (email && !emailRegex.test(email)) {
                            const modalContent = $('#modal-add-aluno .modal-content');
                            modalContent.prepend(`<p class="modal-message error">Por favor, insira um e-mail válido (ex.: nome@dominio.com).</p>`);
                            return;
                        }

                        const now = new Date();
                        const dataMatricula = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
                        $('#add-data_matricula_hidden').val(dataMatricula);

                        const formData = new FormData(this);
                        $.ajax({
                            url: 'cadastro_aluno.php',
                            method: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: 'json',
                            success: function(response) {
                                const modalContent = $('#modal-add-aluno .modal-content');
                                if (response.success) {
                                    modalContent.html(`
                                        <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Cadastro Concluído</h2>
                                        <p class="modal-message">${response.message}</p>
                                        <div class="modal-buttons">
                                            <button class="btn close-modal-btn">Fechar</button>
                                        </div>
                                    `);
                                    if (response.tabela_alunos) {
                                        $('#tabela-alunos').html(response.tabela_alunos);
                                    }
                                    if (response.total_alunos !== undefined) {
                                        $('#total-alunos').text(response.total_alunos);
                                    }
                                    if (response.quantidade_turma !== undefined) {
                                        $(`.box-turmas-single[data-turma-id="${formData.get('turma_id')}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                                    }
                                    setTimeout(function() {
                                        $('#modal-add-aluno').css('display', 'none');
                                    }, 2000);
                                } else {
                                    modalContent.prepend(`<p class="modal-message error">${response.message}</p>`);
                                }
                            },
                            error: function(xhr) {
                                $('#modal-add-aluno .modal-content').prepend(`<p class="modal-message error">Erro ao comunicar com o servidor: ${xhr.statusText}</p>`);
                            }
                        });
                    });
                },
                error: function(xhr) {
                    console.error('Erro ao buscar próxima matrícula:', xhr.statusText);
                    // Prosseguir com valor padrão em caso de erro
                    const originalContent = `
                        <h2 class="modal-title">Cadastrar Aluno</h2>
                        <form id="cadastro-aluno-form" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="add-nome">Nome:</label>
                                <input type="text" id="add-nome" name="nome" placeholder="Ex.: João" required>
                            </div>
                            <div class="form-group">
                                <label for="add-sobrenome">Sobrenome:</label>
                                <input type="text" id="add-sobrenome" name="sobrenome" placeholder="Ex.: Silva" required>
                            </div>
                            <div class="form-group">
                                <label for="add-data_nascimento">Data de Nascimento:</label>
                                <input type="date" id="add-data_nascimento" name="data_nascimento" required>
                            </div>
                            <div class="form-group">
                                <label for="add-matricula">Matrícula:</label>
                                <input type="text" id="add-matricula" name="matricula" value="0000000001" required>
                            </div>
                            <div class="form-group">
                                <label for="add-email">E-mail (opcional):</label>
                                <input type="email" id="add-email" name="email" placeholder="Ex.: joao.silva@email.com">
                            </div>
                            <div class="form-group">
                                <label for="add-foto">Foto (opcional):</label>
                                <input type="file" id="add-foto" name="foto" accept="image/*">
                            </div>
                            <div class="form-group full-width">
                                <label for="add-nome_pai">Nome do Pai (opcional):</label>
                                <input type="text" id="add-nome_pai" name="nome_pai" placeholder="Ex.: José Silva">
                            </div>
                            <div class="form-group full-width">
                                <label for="add-nome_mae">Nome da Mãe (opcional):</label>
                                <input type="text" id="add-nome_mae" name="nome_mae" placeholder="Ex.: Maria Silva">
                            </div>
                            <div class="form-group full-width">
                                <label for="add-turma_id">Turma:</label>
                                <select id="add-turma_id" name="turma_id" required>
                                    ${turmaOptions}
                                </select>
                            </div>
                            <input type="hidden" id="add-data_matricula_hidden" name="data_matricula_hidden">
                            <div class="modal-buttons">
                                <button type="submit" class="btn">Cadastrar</button>
                                <button type="button" class="btn close-modal-btn">Cancelar</button>
                            </div>
                        </form>
                    `;
                    $('#modal-add-aluno .modal-content').html(originalContent);
                    $('#modal-add-aluno').css('display', 'block');

                    $('#cadastro-aluno-form').on('submit', function(e) {
                        e.preventDefault();

                        const email = $('#add-email').val().trim();
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (email && !emailRegex.test(email)) {
                            const modalContent = $('#modal-add-aluno .modal-content');
                            modalContent.prepend(`<p class="modal-message error">Por favor, insira um e-mail válido (ex.: nome@dominio.com).</p>`);
                            return;
                        }

                        const now = new Date();
                        const dataMatricula = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
                        $('#add-data_matricula_hidden').val(dataMatricula);

                        const formData = new FormData(this);
                        $.ajax({
                            url: 'cadastro_aluno.php',
                            method: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: 'json',
                            success: function(response) {
                                const modalContent = $('#modal-add-aluno .modal-content');
                                if (response.success) {
                                    modalContent.html(`
                                        <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Cadastro Concluído</h2>
                                        <p class="modal-message">${response.message}</p>
                                        <div class="modal-buttons">
                                            <button class="btn close-modal-btn">Fechar</button>
                                        </div>
                                    `);
                                    if (response.tabela_alunos) {
                                        $('#tabela-alunos').html(response.tabela_alunos);
                                    }
                                    if (response.total_alunos !== undefined) {
                                        $('#total-alunos').text(response.total_alunos);
                                    }
                                    if (response.quantidade_turma !== undefined) {
                                        $(`.box-turmas-single[data-turma-id="${formData.get('turma_id')}"] p:contains("alunos")`).text(`${response.quantidade_turma} alunos`);
                                    }
                                    setTimeout(function() {
                                        $('#modal-add-aluno').css('display', 'none');
                                    }, 2000);
                                } else {
                                    modalContent.prepend(`<p class="modal-message error">${response.message}</p>`);
                                }
                            },
                            error: function(xhr) {
                                $('#modal-add-aluno .modal-content').prepend(`<p class="modal-message error">Erro ao comunicar com o servidor: ${xhr.statusText}</p>`);
                            }
                        });
                    });
                }
            });
        },
        error: function(xhr) {
            const errorContent = `
                <h2 class="modal-title">Cadastrar Aluno</h2>
                <p class="modal-message error">Erro ao carregar turmas: ${xhr.statusText}</p>
                <div class="modal-buttons">
                    <button type="button" class="btn close-modal-btn">Fechar</button>
                </div>
            `;
            $('#modal-add-aluno .modal-content').html(errorContent);
            $('#modal-add-aluno').css('display', 'block');
        }
    });
}