<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./assets/css/login.css" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Login</title>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-title">EvoGraph</h1>
            <div id="message" class="message"></div>
            <!-- Formulário de Login -->
            <form id="login-form" class="login-form" method="POST" action="login.php">

                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                    <i class='bx bx-user'></i>
                </div>

                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
                    <i class='bx bx-lock-alt'></i>
                </div>

                <button type="submit" class="login-button">Entrar</button>
            </form>
            
            <div class=forgot-password>
                <label>
                    <input type="checkbox" id="remember-me" name="remember-me">
                    Lembrar senha
                </label>
                <a href="#" id="show-reset-form">Esqueceu a senha?</a>
            </div>

            <div class="register-link">
                <p>Não tem uma conta? <a href="#">Solicitar acesso</a></p>
            </div>

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
    <script src="./assets//js/script.js"></script>
</body>
</html>