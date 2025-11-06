async function cadastrarUsuario(event) {
  event.preventDefault()

    // Obtém os valores dos campos do formulário
    const name = document.getElementById('name').value;
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const contact = document.getElementById('contact').value;
    const photo_url = document.getElementById('photo_url').value;
    const password = document.getElementById('password').value;
    const password_confirmation = document.getElementById('password_confirmation').value;

    // Validação da senha
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
      showModal(
        'Senha Inválida',
        'A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e um símbolo.',
        'error'
      )
      return
    }   

  // --- Validação 2 (Confirmação) ---
  if (password !== password_confirmation) {
    showModal(
      'Senhas Não Coincidem',
      'Os campos de senha e confirmação de senha não são iguais. Por favor, verifique.',
      'error'
    )
    return
  }

  // Dados do usuário para o cadastro
  const dados = {
    name,
    username,
    email,
    contact,
    password,
    password_confirmation,
    photo_url
  }

  try {
    // Faz a requisição POST para o servidor
    const resposta = await fetch('http://localhost:8000/api/cadastrar', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      },
      body: JSON.stringify(dados)
    })

    // Processa a resposta da API
    const respostaJson = await resposta.json()

    if (resposta.ok) {
      // --- SUCESSO ---
      showModal(
        'Cadastro Realizado!',
        `Usuário ${dados.name} cadastrado com sucesso! Você já pode fazer o login.`,
        'success'
      )
      document.getElementById('registerForm').reset()
      setTimeout(() => {
        window.location.href = 'login.html'
      }, 5000)
    } else {
      // --- ERRO DA API (Ex: Validação) ---
      let errorMessage = respostaJson.message || 'Falha ao cadastrar usuário'

      if (respostaJson.errors) {
        const firstErrorKey = Object.keys(respostaJson.errors)[0] // Pega a chave do primeiro erro (ex: 'email')
        errorMessage = respostaJson.errors[firstErrorKey][0] // Pega a primeira mensagem de erro
      }

      showModal('Erro no Cadastro', errorMessage, 'error')
    }
  } catch (erro) {
    // --- ERRO DE CONEXÃO ---
    showModal(
      'Erro de Conexão',
      'Não foi possível conectar ao servidor. Verifique sua rede e tente novamente.',
      'error'
    )
    console.error('Erro na requisição:', erro)
  }
}

document
  .getElementById('registerForm')
  .addEventListener('submit', cadastrarUsuario)
