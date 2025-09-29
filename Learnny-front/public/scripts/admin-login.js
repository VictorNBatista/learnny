document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('adminLoginForm');
    const mensagem = document.getElementById('mensagem');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const loginData = {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        };

        fetch('http://localhost:8000/api/admin/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(loginData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                // Armazena token e ID do admin no localStorage
                localStorage.setItem('adminToken', data.admin.token);
                localStorage.setItem('adminId', data.admin.id);

                mensagem.textContent = `Bem-vindo, ${data.admin.name}! Login realizado com sucesso.`;

                // Redireciona para o dashboard
                window.location.href = 'admin.html';
            } else {
                mensagem.textContent = 'Erro no login: ' + (data.mensagem || 'Erro desconhecido');
            }
        })
        .catch(error => {
            console.error("Erro ao realizar login:", error);
            mensagem.textContent = 'Erro ao realizar o login. Tente novamente.';
        });
    });
});
