document.addEventListener('DOMContentLoaded', function() {
    // Manipular formulário de login
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

            fetch('/login', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                message.textContent = data.message;
                message.className = `message ${data.success ? 'success' : 'error'}`;
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = data.redirect || '/dashboard';
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

    // Mostrar/esconder formulário de redefinição
    const showResetForm = document.getElementById('show-reset-form');
    if (showResetForm) {
        showResetForm.addEventListener('click', function(event) {
            event.preventDefault();
            const resetForm = document.getElementById('reset-form');
            const loginFormInner = document.getElementById('login-form');
            if (resetForm && loginFormInner) {
                resetForm.classList.toggle('hidden');
                loginFormInner.classList.toggle('hidden');
                message.textContent = '';
                message.className = 'message';
            }
        });
    }

    // Manipular formulário de redefinição (placeholder)
    const resetForm = document.getElementById('reset-form');
    if (resetForm) {
        resetForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const message = document.getElementById('message');
            message.textContent = "Funcionalidade de redefinição de senha ainda não implementada.";
            message.className = "message error";
        });
    }
});