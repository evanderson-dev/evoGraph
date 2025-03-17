document.getElementById('login-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Impede o envio padrão do formulário

    // Pegar valores dos campos
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const message = document.getElementById('message');

    // Expressão regular para validar e-mail
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Simulação de credenciais válidas (exemplo)
    const validEmail = "professor@escola.com";
    const validPassword = "123456";

    // Validação
    if (email === "" || password === "") {
        message.textContent = "Por favor, preencha todos os campos.";
        message.className = "message error";
    } else if (!emailRegex.test(email)) {
        message.textContent = "Por favor, insira um e-mail válido.";
        message.className = "message error";
    } else if (email === validEmail && password === validPassword) {
        message.textContent = "Login bem-sucedido! Redirecionando...";
        message.className = "message success";
        setTimeout(() => {
            // window.location.href = "dashboard.html"; // Descomente quando o Dashboard existir
            alert("Você seria redirecionado ao Dashboard do evoGraph!");
        }, 2000);
    } else {
        message.textContent = "E-mail ou senha inválidos.";
        message.className = "message error";
    }
});