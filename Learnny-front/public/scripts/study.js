// Função para listar todos os professores
async function listarProfessores() {
    const token = localStorage.getItem('token'); // Obtém o token do localStorage para autenticação
    console.log("Token:", token);
  
    try {
        if (token) {
            const response = await fetch('http://localhost:8000/api/professor/listar', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });
            
            if (response.ok) {
                const data = await response.json();
                console.log("Dados recebidos:", data); // Verifique o que a API está retornando
                exibirProfessores(data.professores.data);
            } else {
                const errorData = await response.json();
                console.error('Erro na resposta da API:', response.status, errorData);
                alert('Erro ao buscar os professores: ' + (errorData.message || 'Erro desconhecido'));
            }
        } else {
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        alert('Erro ao carregar a lista de professores');
    }
}

// Função para exibir os professores no HTML
function exibirProfessores(professores) {
  const teacherContainer = document.querySelector("main");
  teacherContainer.innerHTML = ''; // Limpa o contêiner de professores

  professores.forEach((professor) => {
      const professorCard = document.createElement("article");
      professorCard.classList.add("teacher-item");

      const price = (typeof professor.price === 'number') ? professor.price.toFixed(2) : '0.00';

      professorCard.innerHTML = `
          <header>
              <img src="${professor.photo_url}" alt="${professor.name}">
              <div>
                  <strong>${professor.name}</strong>
                  <span>${professor.subject}</span>
              </div>
          </header>
          <p>${professor.biography}</p>
          <footer>
              <p>Preço/hora <strong>R$ ${price}</strong></p>
              <a href="https://api.whatsapp.com/send?1=pt_BR&phone=${professor.contact}&text=Tenho interesse na sua aula de ${professor.subject}" target="_blank" class="button">
                  <img src="public/images/icons/whatsapp.svg" alt="Whatsapp">Entrar em contato
              </a>
          </footer>
      `;
      teacherContainer.appendChild(professorCard);
  });
}

// Chama a função para listar todos os professores ao carregar a página
document.addEventListener('DOMContentLoaded', listarProfessores);
