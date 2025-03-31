/* js/modal-add-funcionario.js */
/* Responsabilidade: Gerencia o modal de cadastro de funcionários */
$(document).ready(function() {
    $(document).on('click', '#modal-add-funcionario .close-modal-btn', function() {
        $('#modal-add-funcionario').css('display', 'none');
    });
});

function openAddFuncionarioModal() {
    const originalContent = `
        <h2 class="modal-title">Cadastrar Funcionário</h2>
        <form id="form-add-funcionario">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" placeholder="Ex.: João" required>
            </div>
            <div class="form-group">
                <label for="sobrenome">Sobrenome:</label>
                <input type="text" id="sobrenome" name="sobrenome" placeholder="Ex.: Silva" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" placeholder="Ex.: joao.silva@escola.com" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" placeholder="Digite a senha" required>
            </div>
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>
            </div>
            <div class="form-group">
                <label for="rf">Registro Funcional (RF):</label>
                <input type="text" id="rf" name="rf" placeholder="Ex.: 123456" required>
            </div>
            <div class="form-group">
                <label for="cargo">Cargo:</label>
                <select id="cargo" name="cargo" required>
                    <option value="">Selecione o cargo</option>
                    <option value="Professor">Professor</option>
                    <option value="Coordenador">Coordenador</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn">Cadastrar</button>
                <button type="button" class="btn close-modal-btn">Cancelar</button>
            </div>
        </form>
    `;
    $('#modal-add-funcionario .modal-content').html(originalContent);
    $('#modal-add-funcionario').css('display', 'block');

    $('#form-add-funcionario').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'cadastro_funcionario.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                var modalContent = $('#modal-add-funcionario .modal-content');
                if (response.success) {
                    modalContent.html(`
                        <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Cadastro Concluído</h2>
                        <p class="modal-message">${response.message}</p>
                        <div class="modal-buttons">
                            <button class="btn close-modal-btn">Fechar</button>
                        </div>
                    `);
                    setTimeout(function() {
                        $('#modal-add-funcionario').css('display', 'none');
                    }, 2000);
                } else {
                    modalContent.prepend(`<p class="modal-message error">${response.message}</p>`);
                }
            },
            error: function(xhr) {
                $('#modal-add-funcionario .modal-content').prepend(`<p class="modal-message error">Erro ao comunicar com o servidor: ${xhr.statusText}</p>`);
            }
        });
    });
}