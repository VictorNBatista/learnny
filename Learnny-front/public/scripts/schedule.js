document.addEventListener('DOMContentLoaded', async () => {
    const professorInfoContainer = document.getElementById('professor-info');
    const subjectSelect = document.getElementById('subject-select');
    const slotsContainer = document.getElementById('slots-container');
    const token = localStorage.getItem('userToken'); 

    const params = new URLSearchParams(window.location.search);
    const professorId = params.get('professorId');

    if (!professorId) {
        showModal(
            'Erro',
            'Professor não especificado. Você será redirecionado.',
            'error'
        );
        setTimeout(() => { window.location.href = 'study.html'; }, 2500);
        return;
    }

    // buscar e exibir os detalhes do professor
    async function loadProfessorDetails() {
      try {
          // Garanta que este endpoint retorna os detalhes de UM professor e suas matérias
          const response = await fetch(`http://localhost:8000/api/user/professors/${professorId}`, {
               headers: { 'Authorization': `Bearer ${token}` }
          });

          if (!response.ok) {
              console.error("Erro ao buscar detalhes do professor:", response.status);
              showModal('Erro ao Carregar', 'Não foi possível carregar os detalhes do professor.', 'error');
              return; // Interrompe a execução se não conseguir carregar o professor
          }

          const responseData = await response.json();
          const professor = responseData.data;

          if (!professor) {
             throw new Error("Dados do professor não encontrados na resposta da API.");
          }

          console.log("Detalhes do professor:", professor);

          professorInfoContainer.innerHTML = `
              <img src="${professor.photo_url || 'public/images/default-avatar.svg'}" alt="${professor.name}">
              <strong>${professor.name}</strong>
          `;

          // Limpa opções anteriores e preenche o select com as matérias
          subjectSelect.innerHTML = '<option value="" disabled selected>Selecione uma matéria</option>'; // Reset com placeholder
          if (professor.subjects && professor.subjects.length > 0) {
              professor.subjects.forEach(subject => {
                  const option = document.createElement('option');
                  option.value = subject.id;
                  option.textContent = subject.name;
                  subjectSelect.appendChild(option);
              });
          } else {
              // Informa se o professor não tem matérias cadastradas
              const option = document.createElement('option');
              option.value = '';
              option.textContent = 'Nenhuma matéria disponível';
              option.disabled = true;
              subjectSelect.appendChild(option);
          }

      } catch (error) {
          console.error("Falha na requisição de detalhes do professor:", error);
          showModal(
              'Erro de Conexão',
              'Ocorreu um erro ao buscar os detalhes do professor. Tente recarregar a página.',
              'error'
          );
      }
    }

    // Função para buscar e exibir os horários livres
    async function loadAvailableSlots() {
        slotsContainer.innerHTML = '<p>Carregando horários...</p>';
        try {
            // Garanta que este endpoint retorna o array de strings ISO
            const response = await fetch(`http://localhost:8000/api/professor/${professorId}/availabilities`);
            if (!response.ok) {
                 throw new Error(`Erro ${response.status} ao buscar horários.`);
            }
            const slots = await response.json();

            slotsContainer.innerHTML = '';
            if (!Array.isArray(slots) || slots.length === 0) {
                slotsContainer.innerHTML = '<p>Nenhum horário disponível para os próximos 7 dias.</p>';
                return;
            }

            slots.forEach(slot => {
                const date = new Date(slot);
                // Validação simples da data
                if (isNaN(date.getTime())) {
                    console.warn("Slot inválido recebido:", slot);
                    return; // Pula slots inválidos
                }
                const formattedDate = date.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: '2-digit' });
                const formattedTime = date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

                const slotButton = document.createElement('button');
                slotButton.className = 'slot-button';
                slotButton.textContent = `${formattedDate} às ${formattedTime}`;
                slotButton.dataset.startTime = slot;

                slotButton.addEventListener('click', handleSlotClick);
                slotsContainer.appendChild(slotButton);
            });
        } catch(error) {
            console.error("Erro ao carregar slots:", error);
            showModal('Erro', 'Não foi possível carregar os horários disponíveis.', 'error');
        }
    }

    // Função para lidar com o clique em um horário
    async function handleSlotClick(event) {
        const selectedSubjectId = subjectSelect.value;
        if (!selectedSubjectId) {
            showModal('Atenção', 'Por favor, selecione uma matéria primeiro.', 'info');
            return;
        }

        const startTime = event.target.dataset.startTime;
        const selectedSubjectText = subjectSelect.options[subjectSelect.selectedIndex].text;
        const selectedTimeText = event.target.textContent;

        const didConfirm = await showConfirm(
            'Confirmar Agendamento',
            `Deseja realmente agendar uma aula de ${selectedSubjectText} para ${selectedTimeText}?`
        );

        if (didConfirm) {
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
                    start_time: startTime, // Envia a string ISO 8601 completa
                })
            });

            if (response.status === 201) {
                showModal(
                    'Agendamento Solicitado!',
                    'Sua solicitação foi enviada com sucesso! O professor irá confirmar em breve.',
                    'success'
                );
                setTimeout(() => {
                     window.location.href = 'student-dashboard.html';
                }, 3000);

            } else {
                const errorData = await response.json().catch(() => ({ message: 'Erro desconhecido ao processar a resposta.' }));
                showModal(
                    'Erro ao Agendar',
                    errorData.message || `Ocorreu um erro (${response.status}). Tente outro horário.`,
                    'error'
                );
            }
        } catch (error) {
            console.error("Erro na requisição de agendamento:", error);
            showModal(
                'Erro de Conexão',
                'Não foi possível conectar ao servidor para agendar a aula. Tente novamente.',
                'error'
            );
        }
    }

    // Carregar tudo ao iniciar a página
    // Chama em sequência para garantir que o professor carregue antes dos slots
    await loadProfessorDetails();
    // Só carrega os slots se o professor foi carregado com sucesso (evita erros se loadProfessorDetails falhar)
    if (document.getElementById('professor-info').innerHTML !== '') {
         await loadAvailableSlots();
    }
});