let professoresOriginais = []; // Lista original de professores

// Função para listar todos os professores
async function listarProfessores() {
    const token = localStorage.getItem('userToken');
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
                console.log("Dados recebidos:", data);

                professoresOriginais = data.data
                exibirProfessores(professoresOriginais);
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
    teacherContainer.innerHTML = ''; // Limpa o contêiner

    professores.forEach((professor) => {
        const materias = professor.subjects.map(s => s.name).join(', ');

        const professorCard = document.createElement("article");
        professorCard.classList.add("teacher-item");

        professorCard.innerHTML = `
            <header>
                <img src="${professor.photo_url || 'public/images/default-avatar.svg'}" alt="${professor.name}">
                <div>
                    <strong>${professor.name}</strong>
                    <span>${materias}</span>
                </div>
            </header>
            <p>${professor.biography}</p>
            <footer>
                <p>Preço/hora <strong>R$ ${Number(professor.price).toFixed(2)}</strong></p>
                <a href="schedule.html?professorId=${professor.id}" class="button">
                     <img src="public/images/icons/schedule.svg" alt="Agendar">Agendar Aula
                </a>
            </footer>
        `;

        teacherContainer.appendChild(professorCard);
    });
}

// Filtro por matéria no formulário
const formBusca = document.getElementById('search-teachers');
const inputBusca = document.getElementById('searchSubject');

function normalizarTexto(texto) {
  return texto
    .normalize("NFD")               // Decompõe acentos
    .replace(/[\u0300-\u036f]/g, '') // Remove acentos
    .toLowerCase();                 // Deixa tudo minúsculo
}

if (formBusca) {
    formBusca.addEventListener('submit', (event) => {
        event.preventDefault();
        const termo = normalizarTexto(inputBusca.value.trim());

        if (!termo) {
            exibirProfessores(professoresOriginais);
            return;
        }

        const professoresFiltrados = professoresOriginais.filter((professor) =>
            professor.subjects.some((s) => normalizarTexto(s.name).includes(termo))
        );

        exibirProfessores(professoresFiltrados);
    });

    // Resetar a lista quando o campo for limpo
    inputBusca.addEventListener('input', (event) => {
        if (event.target.value.trim() === '') {
            exibirProfessores(professoresOriginais);
        }
    });
}

// Executa a listagem ao carregar a página
document.addEventListener('DOMContentLoaded', listarProfessores);
