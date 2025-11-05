document.addEventListener('DOMContentLoaded', () => {
    carregarMaterias();

    document
        .getElementById('create-class')
        .addEventListener('submit', cadastrarProfessor);
});

// Busca as matérias disponíveis na API e popula a lista de checkboxes.
async function carregarMaterias() {
    const container = document.getElementById('subjects-checkbox-list');
    container.innerHTML = '<p>Carregando matérias...</p>'; // Feedback inicial

    try {
        const resposta = await fetch('http://localhost:8000/api/subject/listar');
        if (!resposta.ok) throw new Error('Erro ao carregar matérias');

        const materias = await resposta.json();
        console.log('Matérias carregadas:', materias);
        container.innerHTML = ''; // Limpa o "Carregando..."

        if (!Array.isArray(materias) || materias.length === 0) {
             container.innerHTML = '<p>Nenhuma matéria encontrada.</p>';
             return;
        }

        materias.forEach(subject => {
            const wrapper = document.createElement('div');
            wrapper.classList.add('subject-item'); // Mantém sua classe original

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'subjects[]';
            checkbox.value = subject.id;
            checkbox.id = `subject-${subject.id}`;

            const label = document.createElement('label');
            label.htmlFor = checkbox.id;
            label.textContent = subject.name;

            wrapper.appendChild(checkbox);
            wrapper.appendChild(label);
            container.appendChild(wrapper);
        });
    } catch (erro) {
        console.error('Erro ao carregar matérias:', erro);
        container.innerHTML = '<p>Erro ao carregar matérias.</p>'; // Feedback de erro
        // Usa o modal para notificar o erro ao carregar matérias
        showModal('Erro', 'Não foi possível carregar a lista de matérias disponíveis.', 'error');
    }
}

async function cadastrarProfessor(event) {
    event.preventDefault(); // Impede o envio padrão do formulário

  const name = document.getElementById('name').value;
  const username = document.getElementById('username').value;
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  const password_confirmation = document.getElementById('password_confirmation').value;
  const photo_url = document.getElementById('avatar').value;
  const contact = document.getElementById('whatsapp').value;
  const biography = document.getElementById('bio').value;
  const price = parseFloat(document.getElementById('cost').value);

    // Coleta das matérias selecionadas
    const subjects = Array.from(
        document.querySelectorAll('input[name="subjects[]"]:checked')
    ).map(cb => parseInt(cb.value));

     if (password !== password_confirmation) {
        showModal('Erro de Validação', 'As senhas digitadas não coincidem.', 'error');
        return;
    }
    if (subjects.length === 0) {
        showModal('Erro de Validação', 'Selecione pelo menos uma matéria.', 'error');
        return;
    }

    // Monta o objeto de dados para envio
    const dados = {
        name,
        username,
        email,
        password,
        password_confirmation,
        photo_url,
        contact,
        biography,
        price,
        subjects
    };

    console.log('Dados a serem enviados:', dados);

    try {
        // Envio da requisição para a API
        const resposta = await fetch(
            'http://localhost:8000/api/professor/cadastrar',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(dados)
            }
        );

        // Processa a resposta
        const respostaJson = await resposta.json().catch(() => ({})); // Tenta parsear JSON, retorna {} se falhar

        if (resposta.ok) {
            showModal(
                'Cadastro Realizado!',
                `Professor ${dados.name} cadastrado com sucesso! Você será redirecionado para a página de login.`,
                'success'
            );
            document.getElementById('create-class').reset(); // Limpa o formulário

            // Redireciona para o login do professor após um tempo
            setTimeout(() => {
                window.location.href = 'professor-login.html';
            }, 3000); // Espera 3 segundos

        } else {
            let errorMessage = respostaJson.message || 'Falha ao cadastrar professor';
             // Tenta extrair erros de validação específicos do Laravel
            if (respostaJson.errors) {
                const firstErrorKey = Object.keys(respostaJson.errors)[0];
                errorMessage = respostaJson.errors[firstErrorKey][0];
            }
             console.error('Erro da API:', respostaJson);
            showModal('Erro no Cadastro', errorMessage, 'error');
        }
    } catch (erro) {
        // --- ERRO DE CONEXÃO/REDE ---
        console.error('Erro na requisição:', erro);
        showModal(
            'Erro de Conexão',
            'Não foi possível conectar ao servidor. Verifique sua rede e tente novamente.',
            'error'
        );
    }
}