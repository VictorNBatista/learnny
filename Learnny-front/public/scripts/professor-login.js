document.addEventListener("DOMContentLoaded", function() {
    console.log("professor-login.js carregado");
    const form = document.getElementById('professorLoginForm');
    const mensagem = document.getElementById('mensagem');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const loginData = {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        };

        fetch('http://localhost:8000/api/professor/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(loginData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                // Armazena token e ID do professor
                localStorage.setItem('professorToken', data.professor.token);
                localStorage.setItem('professorId', data.professor.id);

                mensagem.textContent = `Bem-vindo, ${data.professor.name}! Login realizado com sucesso.`;

                // Redireciona para dashboard do professor
                window.location.href = 'index.html';
            } else {
                mensagem.textContent = 'Erro no login: ' + data.message;
            }
        })
        .catch(error => {
            console.error('Erro ao realizar login:', error);
            mensagem.textContent = 'Erro ao realizar o login. Tente novamente.';
        });
    });
});
