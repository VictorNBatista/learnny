let currentProfessorId = null;
let pendingProfessors = [];
let approvedProfessors = [];
let currentAction = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    checkAdminAuth();
    setupEventListeners();
    loadPendingProfessors();
});

function checkAdminAuth() {
    const adminToken = localStorage.getItem('adminToken');
    if (!adminToken) {
        window.location.href = 'admin-login.html';
    }
}

function setupEventListeners() {
    const searchPending = document.getElementById('searchPending');
    const searchApproved = document.getElementById('searchApproved');
    
    if (searchPending) searchPending.addEventListener('input', () => handleSearch('pending'));
    if (searchApproved) searchApproved.addEventListener('input', () => handleSearch('approved'));
    
    setupModals();

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) logoutBtn.addEventListener('click', logout);
}

function setupModals() {
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.close');
    
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });
    
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this);
        });
    });
}

function closeModal(modal) {
    modal.style.display = 'none';
    modal.querySelectorAll('.message').forEach(msg => {
        msg.style.display = 'none';
        msg.className = 'message';
    });
}

function showTab(tabName) {
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    const activeButton = document.querySelector(`[onclick="showTab('${tabName}')"]`);
    if (activeButton) activeButton.classList.add('active');

    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    const activeTab = document.getElementById(`${tabName}-tab`);
    if (activeTab) activeTab.classList.add('active');

    if (tabName === 'pending') loadPendingProfessors();
    else if (tabName === 'approved') loadApprovedProfessors();
}

window.showTab = showTab;

// ---------------------- Listagem de Professores ----------------------

async function loadPendingProfessors() {
    const adminToken = localStorage.getItem('adminToken');
    const listContainer = document.getElementById('pendingProfessorsList');
    listContainer.innerHTML = '<div class="loading">Carregando professores pendentes...</div>';

    try {
        const response = await fetch('http://localhost:8000/api/admin/professores/pendentes', {
            headers: { 'Authorization': `Bearer ${adminToken}` }
        });

        if (!response.ok) throw new Error('Erro ao carregar professores pendentes');

        const result = await response.json();
        console.log("Resposta da API:", result);

        const pendingProfessors = Array.isArray(result.data) ? result.data : [];

        renderProfessors(pendingProfessors, 'pending');
    } catch (error) {
        console.error(error);
        listContainer.innerHTML = `<div class="empty-state"><p>Erro ao carregar professores pendentes. Tente novamente.</p></div>`;
    }
}

async function loadApprovedProfessors() {
    const adminToken = localStorage.getItem('adminToken');
    const listContainer = document.getElementById('approvedProfessorsList');
    listContainer.innerHTML = '<div class="loading">Carregando professores aprovados...</div>';

    try {
        const response = await fetch('http://localhost:8000/api/professor/listar', {
            headers: { 'Authorization': `Bearer ${adminToken}` }
        });

        if (!response.ok) throw new Error('Erro ao carregar professores aprovados');

        const result = await response.json();
        console.log("Resposta da API (aprovados):", result);

        // Garante que é array
        const approvedProfessors = Array.isArray(result.data) ? result.data : [];

        renderProfessors(approvedProfessors, 'approved');
    } catch (error) {
        console.error(error);
        listContainer.innerHTML = `<div class="empty-state"><p>Erro ao carregar professores aprovados. Tente novamente.</p></div>`;
    }
}

function renderProfessors(professors, type) {
    const listId = type === 'pending' ? 'pendingProfessorsList' : 'approvedProfessorsList';
    const container = document.getElementById(listId);

    if (professors.length === 0) {
        const msg = type === 'pending' ? 'Nenhum professor aguardando aprovação.' : 'Nenhum professor aprovado encontrado.';
        container.innerHTML = `<div class="empty-state"><p>${msg}</p></div>`;
        return;
    }

    container.innerHTML = professors.map(p => {
        const photo = p.photo_url || 'public/images/default-avatar.svg';
        const subjectsHTML = (p.subjects || []).map(s => `<span class="subject-tag">${s.name}</span>`).join('');
        const actions = type === 'pending'
            ? `<button class="btn-view" onclick="viewProfessorDetails(${p.id})">Ver Detalhes</button>
               <button class="btn-approve" onclick="openConfirmModal('approve', ${p.id}, '${p.name}')">Aprovar</button>
               <button class="btn-reject" onclick="openConfirmModal('reject', ${p.id}, '${p.name}')">Reprovar</button>`
            : `<button class="btn-view" onclick="viewProfessorDetails(${p.id})">Ver Detalhes</button>`;
        return `
            <div class="professor-item" data-id="${p.id}">
                <div class = "professor-photo-info">
                    <div class="professor-photo"><img src="${photo}" alt="${p.name}" onerror="this.src='public/images/default-avatar.svg'"></div>
                    <div class="professor-info">
                        <div class="professor-name">${p.name}</div>
                        <div class="professor-email">${p.email}</div>
                        <div class="professor-subjects">${subjectsHTML}</div>
                        <div class="professor-price">R$ ${p.price}/hora</div>
                    </div>
                </div>
                <div class="professor-actions">${actions}</div>
            </div>`;
    }).join('');
}

// ---------------------- Busca ----------------------

function handleSearch(type) {
    const searchInput = document.getElementById(type === 'pending' ? 'searchPending' : 'searchApproved');
    const searchTerm = searchInput.value.toLowerCase();
    const professors = type === 'pending' ? pendingProfessors : approvedProfessors;

    const filtered = searchTerm === '' ? professors : professors.filter(p =>
        p.name.toLowerCase().includes(searchTerm) ||
        p.email.toLowerCase().includes(searchTerm) ||
        (p.subjects && p.subjects.some(s => s.name.toLowerCase().includes(searchTerm)))
    );

    renderProfessors(filtered, type);
}

// ---------------------- Modal Detalhes ----------------------

async function viewProfessorDetails(professorId) {
    const adminToken = localStorage.getItem('adminToken');
    try {
        const response = await fetch(`http://localhost:8000/api/admin/professores/visualizar/${professorId}`, {
            headers: { 'Authorization': `Bearer ${adminToken}` }
        });
        if (!response.ok) throw new Error('Erro ao carregar detalhes do professor');

        const professor = (await response.json()).data || {};
        document.getElementById('professorPhoto').src = professor.photo_url || 'public/images/default-avatar.svg';
        document.getElementById('professorName').textContent = professor.name;
        document.getElementById('professorEmail').textContent = professor.email;
        document.getElementById('professorContact').textContent = professor.contact || 'Não informado';
        document.getElementById('professorPrice').textContent = professor.price;
        document.getElementById('professorBiography').textContent = professor.biography || 'Não informada';
        const subjectsContainer = document.getElementById('professorSubjects');
        subjectsContainer.innerHTML = (professor.subjects || []).length > 0
            ? professor.subjects.map(s => `<span class="subject-tag">${s.name}</span>`).join('')
            : '<span class="subject-tag">Nenhuma matéria cadastrada</span>';

        const approveBtn = document.getElementById('approveBtn');
        const rejectBtn = document.getElementById('rejectBtn');
        const isPending = pendingProfessors.some(p => p.id === professorId);

        approveBtn.style.display = isPending ? 'inline-block' : 'none';
        rejectBtn.style.display = isPending ? 'inline-block' : 'none';
        if (isPending) {
            approveBtn.onclick = () => openConfirmModal('approve', professorId, professor.name);
            rejectBtn.onclick = () => openConfirmModal('reject', professorId, professor.name);
        }

        currentProfessorId = professorId;
        document.getElementById('detailsModal').style.display = 'block';
    } catch (error) {
        console.error(error);
        alert('Erro ao carregar detalhes do professor. Tente novamente.');
    }
}

function closeDetailsModal() {
    closeModal(document.getElementById('detailsModal'));
    currentProfessorId = null;
}

// ---------------------- Aprovar / Reprovar ----------------------

function openConfirmModal(action, professorId, professorName) {
    currentAction = action;
    currentProfessorId = professorId;

    document.getElementById('confirmTitle').textContent = action === 'approve' ? 'Confirmar Aprovação' : 'Confirmar Reprovação';
    document.getElementById('confirmMessage').textContent = action === 'approve'
        ? `Tem certeza que deseja aprovar o professor ${professorName}?`
        : `Tem certeza que deseja reprovar o professor ${professorName}?`;

    const btn = document.getElementById('confirmActionBtn');
    btn.textContent = action === 'approve' ? 'Aprovar' : 'Reprovar';
    btn.className = action === 'approve' ? 'button' : 'button-danger';
    btn.onclick = executeAction;

    document.getElementById('confirmModal').style.display = 'block';
}

function closeConfirmModal() {
    closeModal(document.getElementById('confirmModal'));
    currentAction = null;
    currentProfessorId = null;
}

async function executeAction() {
    const adminToken = localStorage.getItem('adminToken');
    const messageDiv = document.getElementById('confirm-message');
    if (!currentAction || !currentProfessorId) return showMessage(messageDiv, 'Erro: ação ou professor não identificado.', 'error');

    const endpoint = currentAction === 'approve' ? 'aprovar' : 'reprovar';
    const successMsg = currentAction === 'approve' ? 'Professor aprovado com sucesso!' : 'Professor reprovado com sucesso!';

    try {
        const response = await fetch(`http://localhost:8000/api/admin/professores/${endpoint}/${currentProfessorId}`, {
            method: 'PUT',
            headers: { 'Authorization': `Bearer ${adminToken}`, 'Content-Type': 'application/json' }
        });

        const data = await response.json();

        if (response.ok) {
            showMessage(messageDiv, successMsg, 'success');
            setTimeout(() => {
                closeConfirmModal();
                closeDetailsModal();
                loadPendingProfessors();
                if (document.getElementById('approved-tab').classList.contains('active')) loadApprovedProfessors();
            }, 1500);
        } else {
            showMessage(messageDiv, data.message || `Erro ao ${currentAction} professor.`, 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage(messageDiv, `Erro ao ${currentAction === 'approve' ? 'aprovar' : 'reprovar'} professor. Tente novamente.`, 'error');
    }
}

// ---------------------- Botões inline ----------------------

function approveProfessor() {
    if (currentProfessorId) openConfirmModal('approve', currentProfessorId, [...pendingProfessors, ...approvedProfessors].find(p => p.id === currentProfessorId).name);
}

function rejectProfessor() {
    if (currentProfessorId) openConfirmModal('reject', currentProfessorId, [...pendingProfessors, ...approvedProfessors].find(p => p.id === currentProfessorId).name);
}

// ---------------------- Funções auxiliares ----------------------

function showMessage(element, message, type) {
    element.textContent = message;
    element.className = `message ${type}`;
    element.style.display = 'block';
    if (type === 'success') setTimeout(() => element.style.display = 'none', 3000);
}

function logout() {
    const adminToken = localStorage.getItem('adminToken');
    if (adminToken) {
        fetch('http://localhost:8000/api/admin/logout', { method: 'POST', headers: { 'Authorization': `Bearer ${adminToken}` } })
        .finally(() => {
            localStorage.removeItem('adminToken');
            window.location.href = 'index.html';
        });
    } else {
        window.location.href = 'index.html';
    }
}

// ---------------------- Globais ----------------------

window.viewProfessorDetails = viewProfessorDetails;
window.closeDetailsModal = closeDetailsModal;
window.openConfirmModal = openConfirmModal;
window.closeConfirmModal = closeConfirmModal;
window.approveProfessor = approveProfessor;
window.rejectProfessor = rejectProfessor;
