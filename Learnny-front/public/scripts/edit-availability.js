document.addEventListener('DOMContentLoaded', () => {
    initAvailabilityPage();
});

const weekDays = [
    'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 
    'Quinta-feira', 'Sexta-feira', 'Sábado'
];

async function initAvailabilityPage() {
    const token = localStorage.getItem('professorToken');
    if (!token) {
        showModal('Erro', 'Você precisa estar logado como professor.', 'error');
        setTimeout(() => window.location.href = 'professor-login.html', 2000);
        return;
    }

    const scheduleContainer = document.getElementById('availability-schedule');
    const form = document.getElementById('availability-form');

    // 1. Cria a estrutura HTML para os 7 dias
    scheduleContainer.innerHTML = ''; // Limpa o "Carregando..."
    weekDays.forEach((dayName, index) => {
        const item = document.createElement('div');
        item.className = 'availability-item';
        item.innerHTML = `
            <label class="day-label" for="day-${index}-active">
                <input type="checkbox" id="day-${index}-active" data-day="${index}">
                ${dayName}
            </label>
            <input type="time" class="time-input start-time" id="day-${index}-start" data-day="${index}" step="1800" disabled>
            <input type="time" class="time-input end-time" id="day-${index}-end" data-day="${index}" step="1800" disabled>
        `;
        scheduleContainer.appendChild(item);
    });

    // 2. Busca a disponibilidade atual e preenche o formulário
    try {
        const currentAvailability = await fetchAvailability(token);
        populateForm(currentAvailability);
    } catch (error) {
        showModal('Erro ao Carregar', error.message, 'error');
    }

    // 3. Adiciona listeners para habilitar/desabilitar inputs e salvar
    setupEventListeners(token);
}

// Busca a disponibilidade atual do professor
async function fetchAvailability(token) {
    try {
        const response = await fetch(`http://localhost:8000/api/professor/availabilities`, {
            method: 'GET',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json', 
            },
        });
        if (!response.ok) {
            if (response.status === 401) throw new Error('Sessão expirada. Faça login novamente.');
            throw new Error('Não foi possível buscar sua disponibilidade.');
        }
        const data = await response.json();
        // Garante que a resposta seja um array
        return Array.isArray(data) ? data : (data.data || []);
    } catch (error) {
        console.error("Erro em fetchAvailability:", error);
        // Se der 401, redireciona
        if (error.message.includes('Sessão expirada')) {
            setTimeout(() => window.location.href = 'professor-login.html', 2000);
        }
        throw error; // Propaga o erro para ser pego no init
    }
}

// Preenche o formulário com os dados carregados
function populateForm(availabilityData) {

    if (!Array.isArray(availabilityData)) {
        console.error("Erro: Os dados recebidos não são um array!");
        return; 
    }

    availabilityData.forEach(item => {
        const dayIndex = item.day_of_week;

        const checkbox = document.getElementById(`day-${dayIndex}-active`);
        const startTimeInput = document.getElementById(`day-${dayIndex}-start`);
        const endTimeInput = document.getElementById(`day-${dayIndex}-end`);

        if (checkbox && startTimeInput && endTimeInput) {
            checkbox.checked = true;
            startTimeInput.value = item.start_time ? item.start_time.substring(0, 5) : ''; // Pega só HH:MM, com segurança
            endTimeInput.value = item.end_time ? item.end_time.substring(0, 5) : '';     // Pega só HH:MM, com segurança
            startTimeInput.disabled = false;
            endTimeInput.disabled = false;
        } else {
            console.warn(`Elementos não encontrados para o dia ${dayIndex}`);
        }
    });
}

// Configura os listeners de eventos
function setupEventListeners(token) {
    const scheduleContainer = document.getElementById('availability-schedule');
    const form = document.getElementById('availability-form');

    // Listener para checkboxes (habilita/desabilita inputs de hora)
    scheduleContainer.addEventListener('change', (event) => {
        if (event.target.type === 'checkbox') {
            const dayIndex = event.target.dataset.day;
            const isChecked = event.target.checked;
            const startTimeInput = document.getElementById(`day-${dayIndex}-start`);
            const endTimeInput = document.getElementById(`day-${dayIndex}-end`);

            startTimeInput.disabled = !isChecked;
            endTimeInput.disabled = !isChecked;

            // Limpa os valores se o dia for desmarcado
            if (!isChecked) {
                startTimeInput.value = '';
                endTimeInput.value = '';
            }
        }
    });

    // Listener para o submit do formulário
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const saveButton = form.querySelector('button[type="submit"]');
        saveButton.disabled = true; // Desabilita o botão enquanto salva
        saveButton.textContent = 'Salvando...';

        const payload = [];
        let validationError = false;

        // Coleta os dados dos dias marcados
        for (let i = 0; i < 7; i++) {
            const checkbox = document.getElementById(`day-${i}-active`);
            if (checkbox.checked) {
                const startTime = document.getElementById(`day-${i}-start`).value;
                const endTime = document.getElementById(`day-${i}-end`).value;

                // Validação simples
                if (!startTime || !endTime) {
                    showModal('Erro de Validação', `Por favor, preencha os horários de início e fim para ${weekDays[i]}.`, 'error');
                    validationError = true;
                    break; 
                }
                if (startTime >= endTime) {
                    showModal('Erro de Validação', `O horário de início deve ser anterior ao horário de fim para ${weekDays[i]}.`, 'error');
                    validationError = true;
                    break;
                }

                payload.push({
                    day_of_week: i,
                    start_time: startTime,
                    end_time: endTime
                });
            }
        }

        if (validationError) {
            saveButton.disabled = false;
            saveButton.textContent = 'Salvar alterações';
            return; // Interrompe se houver erro de validação
        }
        
        // Envia os dados para a API
        try {
            const response = await fetch('http://localhost:8000/api/professor/availabilities', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ availabilities: payload }) // Envia no formato esperado pela API
            });

            if (!response.ok) {
                 const errorData = await response.json().catch(() => ({}));
                 throw new Error(errorData.message || 'Erro ao salvar disponibilidade.');
            }

            showModal('Sucesso!', 'Sua disponibilidade foi atualizada com sucesso!', 'success');

        } catch (error) {
             showModal('Erro ao Salvar', error.message, 'error');
        } finally {
            // Reabilita o botão após a tentativa
            saveButton.disabled = false;
            saveButton.textContent = 'Salvar alterações';
        }
    });
}