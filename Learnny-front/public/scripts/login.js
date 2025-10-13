document.addEventListener('DOMContentLoaded', function () {
  console.log("login.js carregado");
  const form = document.getElementById('loginForm')
  const mensagem = document.getElementById('mensagem')

  form.addEventListener('submit', function (e) {
    e.preventDefault()

    const loginData = {
      email: document.getElementById('email').value,
      password: document.getElementById('password').value
    }

    // Enviar os dados de login via API
    fetch('http://localhost:8000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(loginData)
    })
      .then(response => response.json())
      .then(data => {
        if (data.status === 200) {
          // Armazenar o token e o ID do usuário no localStorage
          localStorage.setItem('userToken', data.usuario.token)
          localStorage.setItem('userId', data.usuario.id)

          // Login bem-sucedido
          mensagem.textContent = `Bem-vindo, ${data.usuario.name}! Login realizado com sucesso.`

          window.location.href = 'student-dashboard.html'
        } else {
          // Erro no login
          mensagem.textContent = 'Erro no login: ' + data.message
        }
      })
      .catch(error => {
        // Erro na comunicação com a API
        console.log('🚀 ~ form.addEventListener ~ error:', error)
        mensagem.textContent = 'Erro ao realizar o login. Tente novamente.'
      })
  })
})
