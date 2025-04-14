<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/assets/css/global.css" rel="stylesheet" />
    <link href="/assets/css/login.css" rel="stylesheet" />
    <title>evoGraph Login</title>
</head>
<body>
    <div class="login-container">
        <h1 class="title">evoGraph</h1>
        <div class="login-box">
            <h2 class="login-title">Login</h2>
            <p class="login-subtitle">Insira seu e-mail e senha para entrar</p>
            <div id="message" class="message"></div>
            <!-- Formulário de Login -->
            <form id="login-form" class="login-form" method="POST">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                </div>
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
                </div>
                <button type="submit" class="login-button">Entrar</button>
            </form>
            <p class="forgot-password"><a href="#" id="show-reset-form">Esqueceu a senha?</a></p>
            <!-- Formulário de Redefinição de Senha (oculto por padrão) -->
            <form id="reset-form" class="reset-form hidden" method="POST">
                <div class="form-group">
                    <label for="reset-email">E-mail</label>
                    <input type="email" id="reset-email" name="reset-email" placeholder="Digite seu e-mail" required>
                </div>
                <div class="form-group">
                    <label for="new-password">Nova Senha</label>
                    <input type="password" id="new-password" name="new-password" placeholder="Digite a nova senha" required>
                </div>
                <button type="submit" class="login-button">Alterar Senha</button>
            </form>
        </div>
    </div>
    <script src="/assets/js/script.js"></script>
    <script>
        $(document).ready(function() {
            // Manipular formulário de login
            $('#login-form').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                $.ajax({
                    url: '/login',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        } else {
                            $('#message').text(response.message).removeClass('success').addClass('error').show();
                        }
                    },
                    error: function(xhr) {
                        $('#message').text('Erro ao comunicar com o servidor: ' + xhr.statusText).removeClass('success').addClass('error').show();
                    }
                });
            });

            // Mostrar/esconder formulário de redefinição
            $('#show-reset-form').on('click', function(e) {
                e.preventDefault();
                $('#login-form').addClass('hidden');
                $('#reset-form').removeClass('hidden');
                $('#message').hide();
            });

            // Manipular formulário de redefinição (placeholder, será implementado depois)
            $('#reset-form').on('submit', function(e) {
                e.preventDefault();
                $('#message').text('Funcionalidade de redefinição de senha ainda não implementada.').removeClass('success').addClass('error').show();
            });
        });
    </script>
</body>
</html>