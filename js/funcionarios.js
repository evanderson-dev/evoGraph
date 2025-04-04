/* js/funcionarios.js */
/* Responsabilidade: Gerencia a página de funcionários */
$(document).ready(function() {
    loadFuncionarios();

    $('#btn-pesquisar').on('click', loadFuncionarios);
    $('#search-funcionario').on('keypress', function(e) {
        if (e.which == 13) loadFuncionarios();
    });

    $(document).on('click', '#modal-edit-funcionario .close-modal-btn', function() {
        $('#modal-edit-funcionario').css('display', 'none');
    });
    $(document).on('click', '#modal-delete-funcionario .close-modal-btn', function() {
        $('#modal-delete-funcionario').css('display', 'none');
    });
});

function loadFuncionarios() {
    const search = $('#search-funcionario').val();
    const cargo = $('#filtro-cargo').val();
    $.ajax({
        url: 'fetch_funcionarios.php',
        method: 'POST',
        data: { search: search, cargo: cargo },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '';
                response.funcionarios.forEach(func => {
                    const nomeCompleto = `${func.nome} ${func.sobrenome}`; // Concatenar nome e sobrenome
                    html += `
                        <tr>
                            <td>${nomeCompleto}</td>
                            <td>${func.email}</td>
                            <td>${func.rf}</td>
                            <td>${func.cargo}</td>
                            <td>
                                <button class="action-btn edit-btn" title="Editar" onclick="showEditFuncionarioModal(${func.id})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="action-btn delete-btn" title="Excluir" onclick="showDeleteFuncionarioModal(${func.id}, '${nomeCompleto}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#tabela-funcionarios').html(html);
            } else {
                $('#tabela-funcionarios').html('<tr><td colspan="5">Nenhum funcionário encontrado.</td></tr>'); // Ajustado colspan para 5
            }
        },
        error: function(xhr) {
            $('#tabela-funcionarios').html('<tr><td colspan="5">Erro ao carregar funcionários.</td></tr>'); // Ajustado colspan para 5
        }
    });
}

function showEditFuncionarioModal(funcId) {
    $.ajax({
        url: 'fetch_funcionarios.php',
        method: 'POST',
        data: { id: funcId }, // Apenas o ID para buscar um funcionário específico
        dataType: 'json',
        success: function(response) {
            if (response.success && response.funcionarios.length > 0) {
                const func = response.funcionarios[0];
                const content = `
                    <h2 class="modal-title">Editar Funcionário</h2>
                    <form id="form-edit-funcionario">
                        <div class="form-group">
                            <label for="nome">Nome:</label>
                            <input type="text" id="nome" name="nome" value="${func.nome}" required>
                        </div>
                        <div class="form-group">
                            <label for="sobrenome">Sobrenome:</label>
                            <input type="text" id="sobrenome" name="sobrenome" value="${func.sobrenome}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail:</label>
                            <input type="email" id="email" name="email" value="${func.email}" required>
                        </div>
                        <div class="form-group">
                            <label for="senha">Senha (deixe em branco para manter):</label>
                            <input type="password" id="senha" name="senha">
                        </div>
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento:</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" value="${func.data_nascimento || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="rf">Registro Funcional (RF):</label>
                            <input type="text" id="rf" name="rf" value="${func.rf}" required>
                        </div>
                        <div class="form-group">
                            <label for="cargo">Cargo:</label>
                            <select id="cargo" name="cargo" required>
                                <option value="Professor" ${func.cargo === 'Professor' ? 'selected' : ''}>Professor</option>
                                <option value="Coordenador" ${func.cargo === 'Coordenador' ? 'selected' : ''}>Coordenador</option>
                                <option value="Diretor" ${func.cargo === 'Diretor' ? 'selected' : ''}>Diretor</option>
                                <option value="Administrador" ${func.cargo === 'Administrador' ? 'selected' : ''}>Administrador</option>
                            </select>
                        </div>
                        <input type="hidden" name="edit_id" value="${func.id}">
                        <div class="modal-buttons">
                            <button type="submit" class="btn">Salvar</button>
                            <button type="button" class="btn close-modal-btn">Cancelar</button>
                        </div>
                    </form>
                `;
                $('#modal-edit-funcionario .modal-content').html(content);
                $('#modal-edit-funcionario').css('display', 'block');

                $('#form-edit-funcionario').on('submit', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: 'cadastro_funcionario.php',
                        method: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            var modalContent = $('#modal-edit-funcionario .modal-content');
                            if (response.success) {
                                modalContent.html(`
                                    <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Edição Concluída</h2>
                                    <p class="modal-message">${response.message}</p>
                                    <div class="modal-buttons">
                                        <button class="btn close-modal-btn">Fechar</button>
                                    </div>
                                `);
                                loadFuncionarios();
                                setTimeout(() => $('#modal-edit-funcionario').css('display', 'none'), 2000);
                            } else {
                                modalContent.prepend(`<p class="modal-message error">${response.message}</p>`);
                            }
                        },
                        error: function(xhr) {
                            $('#modal-edit-funcionario .modal-content').prepend(`<p class="modal-message error">Erro: ${xhr.statusText}</p>`);
                        }
                    });
                });
            } else {
                alert('Erro ao carregar funcionário: ' + (response.message || 'Dados não encontrados'));
            }
        },
        error: function(xhr) {
            alert('Erro ao carregar funcionário: ' + xhr.statusText);
        }
    });
}

function showDeleteFuncionarioModal(funcId, nomeCompleto) {
    const content = `
        <h2 class="modal-title">Confirmar Exclusão</h2>
        <p>Tem certeza que deseja excluir o funcionário <strong>${nomeCompleto}</strong>?</p>
        <div class="modal-buttons">
            <button id="confirm-delete-btn" class="btn">Sim</button>
            <button class="btn close-modal-btn">Não</button>
        </div>
    `;
    $('#modal-delete-funcionario .modal-content').html(content);
    $('#modal-delete-funcionario').css('display', 'block');

    $('#confirm-delete-btn').on('click', function() {
        $.ajax({
            url: 'delete_funcionario.php',
            method: 'POST',
            data: { funcionario_id: funcId },
            dataType: 'json',
            success: function(response) {
                var modalContent = $('#modal-delete-funcionario .modal-content');
                if (response.success) {
                    modalContent.html(`
                        <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Exclusão Concluída</h2>
                        <p class="modal-message">${response.message}</p>
                        <div class="modal-buttons">
                            <button class="btn close-modal-btn">Fechar</button>
                        </div>
                    `);
                    loadFuncionarios();
                    setTimeout(() => $('#modal-delete-funcionario').css('display', 'none'), 2000);
                } else {
                    modalContent.prepend(`<p class="modal-message error">${response.message}</p>`);
                }
            },
            error: function(xhr) {
                $('#modal-delete-funcionario .modal-content').prepend(`<p class="modal-message error">Erro: ${xhr.statusText}</p>`);
            }
        });
    });
}