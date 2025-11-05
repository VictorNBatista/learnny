document.addEventListener('DOMContentLoaded', function() {
    // Funções de inicialização
    checkStudentAuth();
    setupLogout();
});

function checkStudentAuth() {
    const userToken = localStorage.getItem('userToken');
    if (!userToken) {
        window.location.href = 'login.html';
        return;
    }
    // Se o token existe, verifica sua validade e carrega os dados
    verifyStudentToken(userToken);
}

function verifyStudentToken(token) {
    // Usamos uma rota que retorna o usuário logado para verificar o token e pegar os dados
    fetch('http://localhost:8000/api/user/listar', {
        method: 'GET',
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(response => {
        if (!response.ok) {
            localStorage.removeItem('userToken');
            window.location.href = 'login.html';
            throw new Error('Token inválido');
        }
        return response.json();
    })
    .then(user => {
        if (user && user.name) {
            const welcomeMessage = document.getElementById('welcome-message');
            if (welcomeMessage) {
                 welcomeMessage.textContent = `Bem-vindo(a) de volta, ${user.name}!`;
            }
        }
        
        loadAppointments(token, 'all'); // Carrega todos os agendamentos por padrão
        setupFilters(); // Configura os botões de filtro
    })
    .catch(error => {
        console.error('Erro de autenticação:', error.message);
        localStorage.removeItem('userToken');
        window.location.href = 'login.html';
    });
}

function setupFilters() {
    const filterContainer = document.querySelector('.filter-buttons');
    if (!filterContainer) return;

    filterContainer.addEventListener('click', (event) => {
        const targetButton = event.target.closest('.filter-btn');
        if (targetButton) {
            filterContainer.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            targetButton.classList.add('active');

            const filter = targetButton.dataset.filter;
            const token = localStorage.getItem('userToken');
            loadAppointments(token, filter);
        }
    });
}

async function loadAppointments(token, filter = 'all') {
    const listContainer = document.getElementById('appointments-list');
    listContainer.innerHTML = '<p>Carregando seus agendamentos...</p>';
    
    let apiUrl = 'http://localhost:8000/api/appointments/my';

    // Adiciona o parâmetro de filtro à URL, se não for 'all'
    if (filter !== 'all') {
        apiUrl += `?status=${filter}`;
    }
    
    try {
        const response = await fetch(apiUrl, {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (!response.ok) throw new Error('Falha ao buscar agendamentos.');

        const appointments = await response.json();
        listContainer.innerHTML = ''; 

        if (appointments.length === 0) {
            listContainer.innerHTML = '<p>Nenhum agendamento encontrado para este filtro.</p>';
            return;
        }

        appointments.forEach(app => {
            const card = createAppointmentCard(app);
            listContainer.appendChild(card);
        });
    } catch (error) {
        console.error(error);
        listContainer.innerHTML = '<p>Ocorreu um erro ao carregar seus agendamentos.</p>';
    }
}

function createAppointmentCard(app) {
    const card = document.createElement('article');
    card.className = 'appointment-card';
    const { professor, subject } = app;
    const dateTime = new Date(app.start_time);
    const date = dateTime.toLocaleDateString('pt-BR', { dateStyle: 'full' });
    const time = dateTime.toLocaleTimeString('pt-BR', { timeStyle: 'short' });

    card.innerHTML = `
        <header>
            <div class="profile">
                <img src="${professor.photo_url || ''}" alt="Foto de ${professor.name}">
                <div>
                    <strong>${professor.name}</strong>
                    <span>${subject.name}</span>
                </div>
            </div>
            <span class="status-badge status-${app.status}">${app.status.replace(/_/g, ' ')}</span>
        </header>
        <div class="details">
            <p><strong>Quando:</strong> ${date} às ${time}</p>
        </div>
        <footer>
            ${(app.status === 'pending' || app.status === 'confirmed') ? 
               `<button class="cancel-button" data-id="${app.id}">Cancelar</button>` :
               `<p>ID do agendamento: ${app.id}</p>`
            }
        </footer>
    `;

    const cancelButton = card.querySelector('.cancel-button');
    if (cancelButton) {
        cancelButton.addEventListener('click', handleCancelClick);
    }
    return card;
}

async function handleCancelClick(event) {
    const appointmentId = event.target.dataset.id;

    let title = 'Confirmar Ação';
    let message = `Tem certeza que deseja cancelar o agendamento #${appointmentId}?`;

    const token = localStorage.getItem('userToken');
    const cancelUrl = `http://localhost:8000/api/appointments/${appointmentId}/cancel`;

    const didConfirm = await showConfirm(title, message);
    
    
    if (!didConfirm) return;

    try {
        const response = await fetch(cancelUrl, {
            method: 'PUT',
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (response.ok) {
            showModal(
                'Sucesso!', 
                `O agendamento #${appointmentId} foi cancelado com sucesso.`, 
                'success'
            );
            loadAppointments(token); // Recarrega a lista
        } else {
            const errorData = await response.json();
            alert(`Erro ao cancelar: ${errorData.message}`);
        }
    } catch (error) {
        console.error("Erro ao cancelar:", error);
        alert('Ocorreu um erro de conexão ao tentar cancelar.');
    }
}

function setupLogout() {
    const logoutBtn = document.getElementById('logout-button');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }
}

function logout() {
    const userToken = localStorage.getItem('userToken');
    if (userToken) {
        fetch('http://localhost:8000/api/logout', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${userToken}` }
        })
        .finally(() => {
            localStorage.removeItem('userToken');
            localStorage.removeItem('userId');
            window.location.href = 'index.html';
        });
    } else {
        window.location.href = 'index.html';
    }
}