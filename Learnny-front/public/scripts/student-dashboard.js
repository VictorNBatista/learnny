document.addEventListener('DOMContentLoaded', function() {
    // Funções de inicialização
    checkStudentAuth();
    setupLogout();
    // setupCardHover(); // Mantive sua chamada original
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
    fetch('http://localhost:8000/api/user/listar', {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
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
        // Token é válido, personaliza a página e carrega os agendamentos
        if (user && user.name) {
            const welcomeMessage = document.getElementById('welcome-message');
            if (welcomeMessage) {
                 welcomeMessage.textContent = `Bem-vindo(a) de volta, ${user.name}!`;
            }
        }
        // >>> NOVA FUNÇÃO CHAMADA AQUI <<<
        loadAppointments(token);
    })
    .catch(error => {
        console.error('Erro de autenticação:', error);
    });
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

async function loadAppointments(token) {
    const listContainer = document.getElementById('appointments-list');
    listContainer.innerHTML = '<p>Carregando seus agendamentos...</p>';
    
    try {
        const response = await fetch('http://localhost:8000/api/appointments/my', {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (!response.ok) throw new Error('Falha ao buscar agendamentos.');

        const appointments = await response.json();
        listContainer.innerHTML = ''; 

        if (appointments.length === 0) {
            listContainer.innerHTML = '<p>Nenhum agendamento encontrado.</p>';
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
    if (!confirm(`Tem certeza que deseja cancelar o agendamento #${appointmentId}?`)) return;

    const token = localStorage.getItem('userToken');
    const cancelUrl = `http://localhost:8000/api/appointments/${appointmentId}/cancel`;

    try {
        const response = await fetch(cancelUrl, {
            method: 'PUT',
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (response.ok) {
            alert('Agendamento cancelado com sucesso!');
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