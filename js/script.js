document.getElementById('login-form').addEventListener('submit', function(event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const message = document.getElementById('message');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email === "" || password === "") {
        message.textContent = "Por favor, preencha todos os campos.";
        message.className = "message error";
        return;
    } else if (!emailRegex.test(email)) {
        message.textContent = "Por favor, insira um e-mail válido.";
        message.className = "message error";
        return;
    }

    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        message.textContent = data.message;
        message.className = `message ${data.status}`;
        if (data.status === "success") {
            setTimeout(() => {
                alert("Você seria redirecionado ao Dashboard do evoGraph!");
            }, 2000);
        }
    })
    .catch(error => {
        message.textContent = "Erro ao conectar ao servidor.";
        message.className = "message error";
        console.error('Erro:', error);
    });
});