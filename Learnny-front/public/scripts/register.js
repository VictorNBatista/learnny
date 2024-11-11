// Este evento é disparado assim que o conteúdo HTML da página foi completamente carregado e processado.
// Garante que o JavaScript só seja executado após a estrutura da página estar pronta.
document.addEventListener("DOMContentLoaded", function() {
  // Captura o formulário de registro através do seu ID 'registerForm'.
  const form = document.getElementById('registerForm');

  // Captura o elemento onde será exibida a mensagem de sucesso ou erro ao usuário, através do ID 'mensagem'.
  const mensagem = document.getElementById('mensagem');

  // Adiciona um ouvinte de evento que será disparado quando o formulário for enviado (submit).
  form.addEventListener('submit', function(e) {
      e.preventDefault();

      const user = {
          name: document.getElementById('name').value,
          email: document.getElementById('email').value,
          password: document.getElementById('password').value,
          password_confirmation: document.getElementById('password_confirmation').value
      };

      // Validação da senha
      const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

      if (!passwordRegex.test(user.password)) {
          mensagem.textContent = 'A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula, um número e um caractere especial.';
          return;
      }

      // Verifica se a confirmação da senha é igual à senha
      if (user.password !== user.password_confirmation) {
          mensagem.textContent = 'A confirmação da senha não corresponde à senha.';
          return;
      }

      // Realiza uma requisição HTTP para a API que irá processar o cadastro.
      fetch('http://localhost:8000/api/user/cadastrar', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json'
          },
          body: JSON.stringify(user)
      })
      .then(response => response.json())
      .then(data => {
          if (data.status === 200) {
              // Sucesso no cadastro
              mensagem.textContent = `Usuário ${user.name} foi cadastrado com sucesso! Bem-vindo(a)!`;
              form.reset();
          } else {
              // Erro no cadastro
              mensagem.textContent = 'Erro no cadastro: ' + data.message;
          }
      })
      .catch(error => {
          // Erro na comunicação com a API
          mensagem.textContent = 'Erro ao realizar o cadastro. Tente novamente.';
      });
  });
});
