document.addEventListener('DOMContentLoaded', function() {
    // Evento para o formulário de login (somente no index.php)
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
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
                        window.location.href = "dashboard.php";
                    }, 2000);
                }
            })
            .catch(error => {
                message.textContent = "Erro ao conectar ao servidor.";
                message.className = "message error";
                console.error('Erro:', error);
            });
        });
    }

    // Mostrar/esconder o formulário de redefinição (somente no index.php)
    const showResetForm = document.getElementById('show-reset-form');
    if (showResetForm) {
        showResetForm.addEventListener('click', function(event) {
            event.preventDefault();
            const resetForm = document.getElementById('reset-form');
            const loginFormInner = document.getElementById('login-form');
            if (resetForm && loginFormInner) {
                resetForm.style.display = resetForm.style.display === 'none' ? 'block' : 'none';
                loginFormInner.style.display = resetForm.style.display === 'none' ? 'block' : 'none';
            }
        });
    }

    // Processar a redefinição de senha (somente no index.php)
    const resetForm = document.getElementById('reset-form');
    if (resetForm) {
        resetForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const email = document.getElementById('reset-email').value;
            const newPassword = document.getElementById('new-password').value;
            const message = document.getElementById('message');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === "" || newPassword === "") {
                message.textContent = "Por favor, preencha todos os campos.";
                message.className = "message error";
                return;
            } else if (!emailRegex.test(email)) {
                message.textContent = "Por favor, insira um e-mail válido.";
                message.className = "message error";
                return;
            }

            const formData = new FormData();
            formData.append('reset-email', email);
            formData.append('new-password', newPassword);

            fetch('reset_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                message.textContent = data.message;
                message.className = `message ${data.status}`;
                if (data.status === "success") {
                    setTimeout(() => {
                        document.getElementById('reset-form').style.display = 'none';
                        document.getElementById('login-form').style.display = 'block';
                    }, 2000);
                }
            })
            .catch(error => {
                message.textContent = "Erro ao conectar ao servidor.";
                message.className = "message error";
                console.error('Erro:', error);
            });
        });
    }
});