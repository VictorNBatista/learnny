document.addEventListener('DOMContentLoaded', function() {
    checkProfessorAuth();
    setupLogout();
    // setupCardHover();
});

function checkProfessorAuth() {
    const professorToken = localStorage.getItem('professorToken');
    if (!professorToken) {
        window.location.href = 'professor-login.html';
        return;
    }
    verifyProfessorToken(professorToken);
}

function verifyProfessorToken(token) {
    fetch('http://localhost:8000/api/professor/me', {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            localStorage.removeItem('professorToken');
            window.location.href = 'professor-login.html';
            throw new Error('Token inválido');
        }
        return response.json();
    })
    .then(professor => {
        if (professor && professor.name) {
            const welcomeMessage = document.getElementById('welcome-message');
            if (welcomeMessage) {
                welcomeMessage.textContent = `Bem-vindo(a) de volta, ${professor.name}!`;
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
    const professorToken = localStorage.getItem('professorToken');
    if (professorToken) {
        fetch('http://localhost:8000/api/professor/logout', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${professorToken}` }
        })
        .finally(() => {
            localStorage.removeItem('professorToken');
            localStorage.removeItem('professorId');
            window.location.href = 'index.html';
        });
    } else {
        window.location.href = 'index.html';
    }
}

async function loadAppointments(token) {
    const listContainer = document.getElementById('appointments-list');
    listContainer.innerHTML = '<p>Carregando agendamentos...</p>';

    try {
        const response = await fetch('http://localhost:8000/api/professor/appointments', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!response.ok) throw new Error('Falha ao buscar agendamentos.');

        const appointments = await response.json();
        listContainer.innerHTML = '';
        if (appointments.length === 0) {
            listContainer.innerHTML = '<p>Nenhum agendamento encontrado.</p>';
            return;
        }
        appointments.forEach(app => listContainer.appendChild(createAppointmentCard(app)));
    } catch (error) {
        console.error(error);
        listContainer.innerHTML = '<p>Ocorreu um erro ao carregar os agendamentos.</p>';
    }
}

function createAppointmentCard(app) {
    const card = document.createElement('article');
    card.className = 'appointment-card';
    const { user, subject } = app;
    const dateTime = new Date(app.start_time);
    const date = dateTime.toLocaleDateString('pt-BR', { dateStyle: 'full' });
    const time = dateTime.toLocaleTimeString('pt-BR', { timeStyle: 'short' });

    let actionButtons = `<p>ID do agendamento: ${app.id}</p>`;
    if (app.status === 'pending') {
        actionButtons = `
            <button class="reject-button" data-id="${app.id}">Rejeitar</button>
            <button class="confirm-button" data-id="${app.id}">Confirmar</button>
        `;
    } else if (app.status === 'confirmed') {
        actionButtons = `<button class="cancel-button" data-id="${app.id}">Cancelar</button>`;
    }

    card.innerHTML = `
        <header>
            <div class="profile">
                <img src="${user.photo_url || 'public/images/default-avatar.svg'}" alt="Foto de ${user.name}">
                <div>
                    <strong>Aluno: ${user.name}</strong>
                    <span>${subject.name}</span>
                </div>
            </div>
            <span class="status-badge status-${app.status}">${app.status.replace(/_/g, ' ')}</span>
        </header>
        <div class="details">
            <p><strong>Quando:</strong> ${date} às ${time}</p>
        </div>
        <footer class="action-footer">
            ${actionButtons}
        </footer>
    `;
    
    card.querySelector('.confirm-button')?.addEventListener('click', handleConfirmClick);
    card.querySelector('.reject-button')?.addEventListener('click', handleRejectClick);
    card.querySelector('.cancel-button')?.addEventListener('click', handleProfessorCancelClick);

    return card;
}

async function handleAppointmentAction(appointmentId, action) {
    const token = localStorage.getItem('professorToken');
    if (!confirm(`Tem certeza que deseja "${action}" o agendamento #${appointmentId}?`)) return;

    const url = `http://localhost:8000/api/professor/appointments/${appointmentId}/${action}`;
    
    try {
        const response = await fetch(url, {
            method: 'PUT',
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (response.ok) {
            alert(`Agendamento #${appointmentId} foi atualizado com sucesso!`);
            loadAppointments(token); // Recarrega a lista
        } else {
            const err = await response.json();
            alert(`Erro: ${err.message}`);
        }
    } catch (error) {
        console.error(`Erro ao tentar a ação '${action}':`, error);
        alert('Ocorreu um erro de conexão.');
    }
}

function handleConfirmClick(e) { handleAppointmentAction(e.target.dataset.id, 'confirm'); }
function handleRejectClick(e) { handleAppointmentAction(e.target.dataset.id, 'reject'); }
function handleProfessorCancelClick(e) { handleAppointmentAction(e.target.dataset.id, 'cancel'); }