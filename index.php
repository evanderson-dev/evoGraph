<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./css/global.css" rel="stylesheet" />
    <link href="./css/login.css" rel="stylesheet" />
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
            <form id="login-form" class="login-form" method="POST" action="login.php">
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
            <form id="reset-form" class="reset-form hidden" method="POST" action="reset_password.php">
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
    <script src="./js/script.js"></script>
</body>
</html>