async function cadastrarUsuario(event) {
    event.preventDefault(); // Impede o envio padrão do formulário

    // Obtém os valores dos campos do formulário
    const name = document.getElementById('name').value;
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const contact = document.getElementById('contact').value;
    const password = document.getElementById('password').value;
    const password_confirmation = document.getElementById('password_confirmation').value;

    // Validação da senha
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
        document.getElementById('mensagem').textContent = 'A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula, um número e um caractere especial.';
        document.getElementById('mensagem').style.color = 'red';
        return;
    }

    // Verifica se a confirmação da senha é igual à senha
    if (password !== password_confirmation) {
        document.getElementById('mensagem').textContent = 'As senhas não coincidem!';
        document.getElementById('mensagem').style.color = 'red';
        return;
    }

    // Dados do usuário para o cadastro
    const dados = {
        name,
        username,
        email,
        contact,
        password,
        password_confirmation
    };

    try {
        // Faz a requisição POST para o servidor
        const resposta = await fetch('http://localhost:8000/api/cadastrar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados) // Envia os dados como JSON
        });

        // Verifica se a resposta é do tipo JSON
        const tipoResposta = resposta.headers.get('Content-Type');
        if (!tipoResposta || !tipoResposta.includes('application/json')) {
            const textoErro = await resposta.text();
            document.getElementById('mensagem').textContent = `Erro inesperado: ${textoErro}`;
            document.getElementById('mensagem').style.color = 'red';
            return;
        }

        // Processa a resposta da API
        const respostaJson = await resposta.json();
        if (resposta.ok) {
            alert(`Usuário ${dados.name} cadastrado com sucesso!`);
            document.getElementById('registerForm').reset();
        } else {
            alert(`Erro: ${respostaJson.message || 'Falha ao cadastrar usuário'}`);
        }
    } catch (erro) {
        alert(`Erro: ${erro.message}`);
    }
}

// Adiciona um ouvinte para o envio do formulário
document.getElementById('registerForm').addEventListener('submit', cadastrarUsuario);
