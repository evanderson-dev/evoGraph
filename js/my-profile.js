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
        new_password: ''
    };

    $('#edit-btn').on('click', function() {
        $('#profile-form input:not(#cargo)').prop('disabled', false);
        $('#upload-foto-btn').prop('disabled', false);
        $('#save-btn, #cancel-btn').prop('disabled', false);
        $('#edit-btn').prop('disabled', true);
    });

    $('#cancel-btn').on('click', function() {
        $('#nome').val(originalValues.nome);
        $('#sobrenome').val(originalValues.sobrenome);
        $('#email').val(originalValues.email);
        $('#rf').val(originalValues.rf);
        $('#data_nascimento').val(originalValues.data_nascimento);
        $('#profile-foto-preview').attr('src', originalValues.foto);
        $('#foto').val('');
        $('#current_password').val('');
        $('#new_password').val('');

        $('#profile-form input:not(#cargo)').prop('disabled', true);
        $('#upload-foto-btn').prop('disabled', true);
        $('#save-btn, #cancel-btn').prop('disabled', true);
        $('#edit-btn').prop('disabled', false);
    });

    $('#upload-foto-btn').on('click', function(e) {
        e.preventDefault();
        if (!$(this).prop('disabled')) {
            $('#foto')[0].click();
        }
    });

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
});