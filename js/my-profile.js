/* js/my-profile.js */
$(document).ready(function() {
    const originalValues = {
        nome: $('#nome').val(),
        sobrenome: $('#sobrenome').val(),
        email: $('#email').val(),
        rf: $('#rf').val(),
        data_nascimento: $('#data_nascimento').val(),
        foto: $('#profile-foto-preview').attr('src'),
        current_password: '',
        new_password: '',
        header_photo: $('#header-photo').attr('src')
    };

    // Habilitar edição
    $('#edit-btn').on('click', function() {
        $('#profile-form input:not(#cargo)').prop('disabled', false);
        $('#upload-foto-btn').prop('disabled', false);
        $('#save-btn, #cancel-btn').prop('disabled', false);
        $('#edit-btn').prop('disabled', true);
    });

    // Cancelar edição
    $('#cancel-btn').on('click', function() {
        $('#nome').val(originalValues.nome);
        $('#sobrenome').val(originalValues.sobrenome);
        $('#email').val(originalValues.email);
        $('#rf').val(originalValues.rf);
        $('#data_nascimento').val(originalValues.data_nascimento);
        $('#profile-foto-preview').attr('src', originalValues.foto);
        $('#header-photo').attr('src', originalValues.header_photo);
        $('#foto').val('');
        $('#current_password').val('');
        $('#new_password').val('');

        $('#profile-form input:not(#cargo)').prop('disabled', true);
        $('#upload-foto-btn').prop('disabled', true);
        $('#save-btn, #cancel-btn').prop('disabled', true);
        $('#edit-btn').prop('disabled', false);
        $('#message-box').empty();
    });

    // Abrir seletor de foto
    $('#upload-foto-btn').on('click', function(e) {
        e.preventDefault();
        if (!$(this).prop('disabled')) {
            $('#foto')[0].click();
        }
    });

    // Pré-visualizar foto
    $('#foto').on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function() {
                $('#profile-foto-preview').attr('src', reader.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Enviar formulário via AJAX
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: 'my_profile.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                const res = JSON.parse(response);
                const messageBox = $('#message-box');
                messageBox.empty();

                if (res.success) {
                    messageBox.append(`<p class="success-message">${res.message}</p>`);
                    // Atualizar valores originais
                    originalValues.nome = res.data.nome;
                    originalValues.sobrenome = res.data.sobrenome;
                    originalValues.email = res.data.email;
                    originalValues.rf = res.data.rf;
                    originalValues.data_nascimento = res.data.data_nascimento;
                    originalValues.foto = res.data.foto;
                    originalValues.header_photo = res.data.header_photo;

                    // Atualizar UI
                    $('#nome').val(res.data.nome);
                    $('#sobrenome').val(res.data.sobrenome);
                    $('#email').val(res.data.email);
                    $('#rf').val(res.data.rf);
                    $('#data_nascimento').val(res.data.data_nascimento);
                    $('#profile-foto-preview').attr('src', res.data.foto);
                    $('#header-photo').attr('src', res.data.header_photo);
                    $('#foto').val('');
                    $('#current_password').val('');
                    $('#new_password').val('');

                    // Voltar ao modo somente leitura
                    $('#profile-form input:not(#cargo)').prop('disabled', true);
                    $('#upload-foto-btn').prop('disabled', true);
                    $('#save-btn, #cancel-btn').prop('disabled', true);
                    $('#edit-btn').prop('disabled', false);
                } else {
                    messageBox.append(`<p class="error-message">${res.message}</p>`);
                }
            },
            error: function(xhr, status, error) {
                $('#message-box').empty().append(`<p class="error-message">Erro ao salvar: ${xhr.statusText}</p>`);
            }
        });
    });
});