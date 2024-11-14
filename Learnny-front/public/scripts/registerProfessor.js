async function cadastrarProfessor(event) {
  event.preventDefault(); // Impede o envio padrão do formulário

  // Obtém os valores dos campos do formulário
  const name = document.getElementById('name').value;
  const photo_url = document.getElementById('avatar').value;
  const contact = document.getElementById('whatsapp').value;
  const biography = document.getElementById('bio').value;
  const subject = document.getElementById('subject').value;
  const price = document.getElementById('cost').value;

  // Dados do professor para o cadastro
  const dados = {
      name,
      photo_url,
      contact,
      biography,
      subject,
      price
  };

  try {
      // Faz a requisição POST para o servidor
      const resposta = await fetch('http://localhost:8000/api/professor/cadastrar', {
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
          alert(`Erro inesperado: ${textoErro}`);
          return;
      }

      // Processa a resposta da API
      const respostaJson = await resposta.json();
      if (resposta.ok) {
          alert(`Professor ${dados.name} cadastrado com sucesso!`);
          document.getElementById('create-class').reset();
      } else {
          alert(`Erro: ${respostaJson.message || 'Falha ao cadastrar professor'}`);
      }
  } catch (erro) {
      alert(`Erro: ${erro.message}`);
  }
}

// Adiciona um ouvinte para o envio do formulário
document.getElementById('create-class').addEventListener('submit', cadastrarProfessor);
