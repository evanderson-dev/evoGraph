/* Responsabilidade: Gerencia o modal de cadastro de habilidades BNCC, disciplinas e anos escolares */
$(document).ready(function() {
    $(document).on('click', '#modal-add-bncc .close-modal-btn', function() {
        $('#modal-add-bncc').css('display', 'none');
    });
});

function openAddBnccModal() {
    const originalContent = `
        <h2 class="modal-title">Cadastrar Dados Escolares</h2>
        <form id="cadastro-bncc-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="add-tipo">Tipo:</label>
                <select id="add-tipo" name="tipo" required>
                    <option value="">Selecione o tipo</option>
                    <option value="ano_escolar">Ano Escolar</option>
                    <option value="disciplina">Disciplina</option>
                    <option value="habilidade_bncc">Habilidade BNCC</option>
                </select>
            </div>
            <div class="form-group" id="ano-escolar-fields">
                <label for="add-nome-ano">Nome do Ano Escolar:</label>
                <input type="text" id="add-nome-ano" name="nome_ano" placeholder="Ex.: 6º Ano">
            </div>
            <div class="form-group" id="disciplina-fields">
                <label for="add-nome-disciplina">Nome da Disciplina:</label>
                <input type="text" id="add-nome-disciplina" name="nome_disciplina" placeholder="Ex.: Geografia">
            </div>
            <div class="form-group" id="habilidade-fields">
                <label for="add-codigo-habilidade">Código da Habilidade:</label>
                <input type="text" id="add-codigo-habilidade" name="codigo_habilidade" placeholder="Ex.: EF06GE10">
                <label for="add-descricao-habilidade">Descrição da Habilidade:</label>
                <textarea id="add-descricao-habilidade" name="descricao_habilidade" placeholder="Ex.: Analisar a distribuição da população..." rows="4"></textarea>
                <label for="add-ano-escolar-habilidade">Ano Escolar:</label>
                <select id="add-ano-escolar-habilidade" name="ano_escolar_id"></select>
                <label for="add-disciplina-habilidade">Disciplina:</label>
                <select id="add-disciplina-habilidade" name="disciplina_id"></select>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn">Cadastrar</button>
                <button type="button" class="btn close-modal-btn">Cancelar</button>
            </div>
        </form>
    `;
    $('#modal-add-bncc .modal-content').html(originalContent);
    $('#modal-add-bncc').css('display', 'block');

    // Initially hide all fields
    $('#ano-escolar-fields, #disciplina-fields, #habilidade-fields').hide();

    // Load anos escolares and disciplinas for habilidade_bncc
    $.ajax({
        url: 'fetch_anos_disciplinas.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            let anoOptions = '<option value="">Selecione o ano</option>';
            let disciplinaOptions = '<option value="">Selecione a disciplina</option>';
            if (response.success) {
                if (response.anos_escolares) {
                    response.anos_escolares.forEach(ano => {
                        anoOptions += `<option value="${ano.id}">${ano.nome}</option>`;
                    });
                }
                if (response.disciplinas) {
                    response.disciplinas.forEach(disciplina => {
                        disciplinaOptions += `<option value="${disciplina.id}">${disciplina.nome}</option>`;
                    });
                }
            }
            $('#add-ano-escolar-habilidade').html(anoOptions);
            $('#add-disciplina-habilidade').html(disciplinaOptions);
        },
        error: function(xhr) {
            console.error('Erro ao carregar anos e disciplinas:', xhr.statusText);
        }
    });

    // Show/hide fields based on tipo selection
    $('#add-tipo').on('change', function() {
        const tipo = $(this).val();
        $('#ano-escolar-fields, #disciplina-fields, #habilidade-fields').hide();
        if (tipo === 'ano_escolar') {
            $('#ano-escolar-fields').show();
        } else if (tipo === 'disciplina') {
            $('#disciplina-fields').show();
        } else if (tipo === 'habilidade_bncc') {
            $('#habilidade-fields').show();
        }
    });

    // Form submission
    $('#cadastro-bncc-form').on('submit', function(e) {
        e.preventDefault();
        const modalContent = $('#modal-add-bncc .modal-content');
        const tipo = $('#add-tipo').val();

        // Validation
        if (!tipo) {
            modalContent.prepend(`<p class="modal-message error">Selecione o tipo de dado.</p>`);
            return;
        }
        if (tipo === 'ano_escolar' && !$('#add-nome-ano').val().trim()) {
            modalContent.prepend(`<p class="modal-message error">O nome do ano escolar é obrigatório.</p>`);
            return;
        }
        if (tipo === 'disciplina' && !$('#add-nome-disciplina').val().trim()) {
            modalContent.prepend(`<p class="modal-message error">O nome da disciplina é obrigatório.</p>`);
            return;
        }
        if (tipo === 'habilidade_bncc') {
            if (!$('#add-codigo-habilidade').val().trim()) {
                modalContent.prepend(`<p class="modal-message error">O código da habilidade é obrigatório.</p>`);
                return;
            }
            if (!$('#add-descricao-habilidade').val().trim()) {
                modalContent.prepend(`<p class="modal-message error">A descrição da habilidade é obrigatória.</p>`);
                return;
            }
            if (!$('#add-ano-escolar-habilidade').val()) {
                modalContent.prepend(`<p class="modal-message error">Selecione o ano escolar.</p>`);
                return;
            }
            if (!$('#add-disciplina-habilidade').val()) {
                modalContent.prepend(`<p class="modal-message error">Selecione a disciplina.</p>`);
                return;
            }
        }

        const formData = new FormData(this);
        $.ajax({
            url: 'cadastro_bncc.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    modalContent.html(`
                        <h2 class="modal-title success"><i class="fa-solid fa-check-circle"></i> Cadastro Concluído</h2>
                        <p class="modal-message">${response.message}</p>
                        <div class="modal-buttons">
                            <button class="btn close-modal-btn">Fechar</button>
                        </div>
                    `);
                    // Update dropdowns in relatorio-google.php
                    updateBnccDropdowns();
                    setTimeout(function() {
                        $('#modal-add-bncc').css('display', 'none');
                    }, 2000);
                } else {
                    modalContent.prepend(`<p class="modal-message error">${response.message}</p>`);
                }
            },
            error: function(xhr) {
                modalContent.prepend(`<p class="modal-message error">Erro ao comunicar com o servidor: ${xhr.statusText}</p>`);
            }
        });
    });
}

function updateBnccDropdowns() {
    $.ajax({
        url: 'fetch_anos_disciplinas.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update anos_escolares
                let anoOptions = '<option value="">Selecione o ano</option>';
                if (response.anos_escolares) {
                    response.anos_escolares.forEach(ano => {
                        anoOptions += `<option value="${ano.id}">${ano.nome}</option>`;
                    });
                }
                $('#bnccAno').html(anoOptions);

                // Update disciplinas
                let disciplinaOptions = '<option value="">Selecione a disciplina</option>';
                if (response.disciplinas) {
                    response.disciplinas.forEach(disciplina => {
                        disciplinaOptions += `<option value="${disciplina.id}">${disciplina.nome}</option>`;
                    });
                }
                $('#bnccDisciplina').html(disciplinaOptions).prop('disabled', false);

                // Update habilidades_bncc
                let habilidadeOptions = '<option value="">Selecione a habilidade</option>';
                if (response.habilidades_bncc) {
                    response.habilidades_bncc.forEach(habilidade => {
                        habilidadeOptions += `<option value="${habilidade.id}" data-codigo="${habilidade.codigo}">${habilidade.codigo} - ${habilidade.descricao}</option>`;
                    });
                }
                $('#bnccHabilidade').html(habilidadeOptions);
            }
        },
        error: function(xhr) {
            console.error('Erro ao atualizar dropdowns:', xhr.statusText);
        }
    });
}