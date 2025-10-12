document.addEventListener('DOMContentLoaded', async () => {
    const professorInfoContainer = document.getElementById('professor-info');
    const subjectSelect = document.getElementById('subject-select');
    const slotsContainer = document.getElementById('slots-container');
    const token = localStorage.getItem('userToken');

    // 1. Pegar o ID do professor da URL
    const params = new URLSearchParams(window.location.search);
    const professorId = params.get('professorId');

    if (!professorId) {
        alert('Professor não especificado.');
        window.location.href = 'study.html';
        return;
    }

    // Função para buscar e exibir os detalhes do professor
    async function loadProfessorDetails() {
      try {
          const response = await fetch(`http://localhost:8000/api/user/professors/${professorId}`, {
              headers: { 'Authorization': `Bearer ${token}` }
          });

          if (!response.ok) {
              console.error("Erro ao buscar detalhes do professor:", response.status);
              alert("Não foi possível carregar os detalhes do professor.");
              return;
          }
          
          // --- CORREÇÃO APLICADA AQUI ---
          const responseData = await response.json(); // Pega a resposta completa da API
          const professor = responseData.data;      // Acessa o objeto 'professor' dentro da chave 'data'
          
          console.log("Detalhes do professor (corrigido):", professor);

          // Agora, o código abaixo vai funcionar corretamente
          professorInfoContainer.innerHTML = `
              <img src="${professor.photo_url}" alt="${professor.name}">
              <strong>${professor.name}</strong>
          `;

          // Preencher o select com as matérias do professor
          professor.subjects.forEach(subject => {
              const option = document.createElement('option');
              option.value = subject.id;
              option.textContent = subject.name;
              subjectSelect.appendChild(option);
          });

      } catch (error) {
          console.error("Falha na requisição de detalhes do professor:", error);
          alert("Ocorreu um erro de conexão ao buscar os detalhes do professor.");
      }
    }

    // Função para buscar e exibir os horários livres
    async function loadAvailableSlots() {
        slotsContainer.innerHTML = '<p>Carregando horários...</p>';
        const response = await fetch(`http://localhost:8000/api/professor/${professorId}/availabilities`);
        const slots = await response.json();

        slotsContainer.innerHTML = ''; // Limpa o container
        if (slots.length === 0) {
            slotsContainer.innerHTML = '<p>Nenhum horário disponível para os próximos 7 dias.</p>';
            return;
        }

        slots.forEach(slot => {
            const date = new Date(slot);
            const formattedDate = date.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: '2-digit' });
            const formattedTime = date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

            const slotButton = document.createElement('button');
            slotButton.className = 'slot-button';
            slotButton.textContent = `${formattedDate} às ${formattedTime}`;
            slotButton.dataset.startTime = slot; // Guarda o valor ISO completo
            
            slotButton.addEventListener('click', handleSlotClick);
            slotsContainer.appendChild(slotButton);
        });
    }

    // Função para lidar com o clique em um horário
    function handleSlotClick(event) {
        const selectedSubjectId = subjectSelect.value;
        if (!selectedSubjectId) {
            alert('Por favor, selecione uma matéria primeiro.');
            return;
        }

        const startTime = event.target.dataset.startTime;
        const confirmation = confirm(`Confirmar agendamento para ${event.target.textContent} na matéria de ${subjectSelect.options[subjectSelect.selectedIndex].text}?`);

        if (confirmation) {
            bookAppointment(startTime, selectedSubjectId);
        }
    }

    // Função para fazer a requisição de agendamento
    async function bookAppointment(startTime, subjectId) {
        try {
            const response = await fetch('http://localhost:8000/api/appointments', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    professor_id: professorId,
                    subject_id: subjectId,
                    start_time: startTime,
                })
            });

            if (response.status === 201) {
                alert('Aula agendada com sucesso! O professor irá confirmar em breve.');
                window.location.href = 'study.html'; // Ou para uma página "meus agendamentos"
            } else {
                const errorData = await response.json();
                alert(`Erro ao agendar: ${errorData.message}`);
            }
        } catch (error) {
            console.error("Erro na requisição de agendamento:", error);
            alert('Ocorreu um erro de conexão. Tente novamente.');
        }
    }

    // Carregar tudo ao iniciar a página
    await loadProfessorDetails();
    await loadAvailableSlots();
});