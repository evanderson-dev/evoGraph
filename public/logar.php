<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Login</title>
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #f0f0f0;
        /*background-image: url('./assets/img/background.jpg');
        background-size: cover;
        background-position: center;*/
    }

    .container {
        width: 420px;
        background-color: #fff;
        /* background-color: transparent; */
        border: 2px solid rgba(255, 255, 255, .2);
        border-radius: 10px;
        padding: 30px 40px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .container h1 {
        font-size: 36px;
        text-align: center;
    }

    .input-box {
        position: relative;
        width: 100%;
        height: 50px;
        margin: 30px 0;
    }

    .input-box input {
        width: 100%;
        height: 100%;
        background-color: #f0f0f0;
        border: 1px solid #007bff;
        border-radius: 40px;
        outline: none;
        font-size: 16px;
        color: #333;
        padding: 20px 45px 20px 20px;
    }
    
    .input-box input::placeholder {        
        color: gray;
    }

    .input-box i {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
    }

    .remember-forgot {
        display: flex;
        justify-content: space-between;
        margin: 20px 0;
    }

    .remember-forgot label input{
        accent-color: #007bff;
        margin-right: 5px;
    }

    .remember-forgot a {
        text-decoration: none;
        color: #007bff;
        font-size: 14px;
    }

    .remember-forgot a:hover {
        text-decoration: underline;
    }

    .login {
        width: 100%;
        height: 50px;
        background-color: #007bff;
        border: none;
        border-radius: 40px;
        cursor: pointer;
        font-size: 16px;
        color: #fff;
        font-weight: 500;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .login:hover {
        background-color: #0056b3;
        transition: background-color 0.3s ease;
    }

    .register-link {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
    }

    .register-link a {
        text-decoration: none;
        color: #007bff;
        font-weight: 500;
    }

    .register-link a:hover {
        text-decoration: underline;
    }
</style>
<body>
    <main class="container">
        <form>
            <h1>EvoGraph</h1>
            <div class="input-box">
                <input placeholder="Usuário" type="email">
                <i class='bx bxs-user'></i>
            </div>

            <div class="input-box">
                <input placeholder="Senha" type="password">
                <i class='bx bxs-lock-alt'></i>
            </div>
            
            <button class="login" type="submit">Entrar</button>

            <div class="remember-forgot">
                <label>
                    <input type="checkbox">
                    Lembrar senha
                </label>
                <a href="#">Esqueci minha senha</a>
            </div>

            <div class="register-link">
                <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
            </div>
        </form>
    </main>
</body>
</html>