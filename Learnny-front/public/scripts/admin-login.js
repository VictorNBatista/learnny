document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('adminLoginForm');

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

                showModal(
                    'Login Bem-Sucedido!', 
                    `Bem-vindo, ${data.admin.name}! Você será redirecionado.`, 
                    'success'
                );

                // Espera 2 segundos para o usuário ler a mensagem e redireciona
                setTimeout(() => {
                    window.location.href = 'admin.html';
                }, 2000);
            } else {
                showModal('Erro no Login', data.message || 'Credenciais inválidas.', 'error');
            }
        })
        .catch(error => {
            console.error('🚀 ~ Erro na comunicação:', error);
            showModal('Erro de Conexão', 'Não foi possível conectar ao servidor. Tente novamente.', 'error');
        });
    });
});
