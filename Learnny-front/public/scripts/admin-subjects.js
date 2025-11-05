// admin-subjects.js
// Script para gerenciamento de matérias integrado ao backend

let currentSubjectId = null
let subjects = []

// Inicialização
document.addEventListener('DOMContentLoaded', function () {
  checkAdminAuth()
  setupEventListeners()
  loadSubjects()
})

// Verifica se o admin está logado
function checkAdminAuth() {
  const adminToken = localStorage.getItem('adminToken')
  if (!adminToken) {
    window.location.href = 'admin-login.html'
  }
}

// Configura os eventos da página
function setupEventListeners() {
  const addForm = document.getElementById('addSubjectForm')
  if (addForm) addForm.addEventListener('submit', handleAddSubject)

  const editForm = document.getElementById('editSubjectForm')
  if (editForm) editForm.addEventListener('submit', handleEditSubject)

  const searchInput = document.getElementById('searchSubject')
  if (searchInput) searchInput.addEventListener('input', handleSearch)

  setupModals()

  const logoutBtn = document.getElementById('logout-btn')
  if (logoutBtn) logoutBtn.addEventListener('click', logout)
}

// Configura modais
function setupModals() {
  const modals = document.querySelectorAll('.modal')
  const closeButtons = document.querySelectorAll('.close')

  closeButtons.forEach(button => {
    button.addEventListener('click', () => {
      const modal = button.closest('.modal')
      closeModal(modal)
    })
  })

  modals.forEach(modal => {
    modal.addEventListener('click', e => {
      if (e.target === modal) closeModal(modal)
    })
  })
}

function closeModal(modal) {
  modal.style.display = 'none'
  const messages = modal.querySelectorAll('.message')
  messages.forEach(msg => {
    msg.style.display = 'none'
    msg.className = 'message'
  })
}

// Listar matérias
async function loadSubjects() {
  const adminToken = localStorage.getItem('adminToken')
  const subjectsList = document.getElementById('subjectsList')

  subjectsList.innerHTML = '<div class="loading">Carregando matérias...</div>'

  try {
    const response = await fetch(
      'http://localhost:8000/api/admin/subjects/listar',
      {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${adminToken}`,
          'Content-Type': 'application/json'
        }
      }
    )
    if (!response.ok) throw new Error('Erro ao carregar matérias')

    const data = await response.json()
    subjects = data.data || data || []

    renderSubjects(subjects)
  } catch (error) {
    console.error('Erro ao carregar matérias:', error)
    subjectsList.innerHTML = `
            <div class="empty-state">
                <p>Erro ao carregar matérias. Tente novamente.</p>
            </div>
        `
  }
}

// Renderiza a lista de matérias
function renderSubjects(subjectsToRender) {
  const subjectsList = document.getElementById('subjectsList')

  if (!subjectsToRender.length) {
    subjectsList.innerHTML = `
            <div class="empty-state">
                <p>Nenhuma matéria encontrada.</p>
            </div>
        `
    return
  }

  subjectsList.innerHTML = subjectsToRender
    .map(
      subject => `
        <div class="subject-item" data-id="${subject.id}">
            <div class="subject-name">${subject.name}</div>
            <div class="subject-actions">
                <button class="btn-edit" onclick="openEditModal(${subject.id}, '${subject.name}')" title="Editar">
                    <img src="public/images/icons/edit.svg" alt="Editar">
                </button>
                <button class="btn-delete" onclick="openDeleteModal(${subject.id}, '${subject.name}')" title="Excluir">
                    <img src="public/images/icons/trash.svg" alt="Excluir">
                </button>
            </div>
        </div>
    `
    )
    .join('')
}

// Busca de matérias
function handleSearch(e) {
  const searchTerm = e.target.value.toLowerCase()
  const filteredSubjects = subjects.filter(subject =>
    subject.name.toLowerCase().includes(searchTerm)
  )
  renderSubjects(filteredSubjects)
}

// Adicionar matéria
async function handleAddSubject(e) {
  e.preventDefault()
  const adminToken = localStorage.getItem('adminToken')
  const subjectName = e.target.name.value.trim()
  const messageDiv = document.getElementById('add-message')

  if (!subjectName) {
    showMessage(messageDiv, 'Por favor, digite o nome da matéria.', 'error')
    return
  }

  try {
    const response = await fetch(
      'http://localhost:8000/api/admin/subjects/cadastrar',
      {
        method: 'POST',
        credentials: 'include',
        headers: {
          Authorization: `Bearer ${adminToken}`,
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        body: JSON.stringify({ name: subjectName })
      }
    )

    const data = await response.json()

    if (response.ok) {
      showMessage(messageDiv, 'Matéria adicionada com sucesso!', 'success')
      e.target.reset()
      loadSubjects()
    } else {
      showMessage(
        messageDiv,
        data.message || 'Erro ao adicionar matéria.',
        'error'
      )
    }
  } catch (error) {
    console.error('Erro ao adicionar matéria:', error)
    showMessage(
      messageDiv,
      'Erro ao adicionar matéria. Tente novamente.',
      'error'
    )
  }
}

// Abrir/fechar modais de edição
function openEditModal(subjectId, subjectName) {
  currentSubjectId = subjectId
  document.getElementById('editSubjectId').value = subjectId
  document.getElementById('editSubjectName').value = subjectName
  document.getElementById('editModal').style.display = 'block'
}

function closeEditModal() {
  closeModal(document.getElementById('editModal'))
  currentSubjectId = null
}

// Editar matéria
async function handleEditSubject(e) {
  e.preventDefault()
  const adminToken = localStorage.getItem('adminToken')
  const subjectName = e.target.name.value.trim()
  const messageDiv = document.getElementById('edit-message')

  if (!subjectName) {
    showMessage(messageDiv, 'Por favor, digite o nome da matéria.', 'error')
    return
  }

  if (!currentSubjectId) {
    showMessage(messageDiv, 'Erro: ID da matéria não encontrado.', 'error')
    return
  }

  try {
    const response = await fetch(
      `http://localhost:8000/api/admin/subjects/atualizar/${currentSubjectId}`,
      {
        method: 'PUT',
        headers: {
          Authorization: `Bearer ${adminToken}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name: subjectName })
      }
    )

    const data = await response.json()

    if (response.ok) {
      showMessage(messageDiv, 'Matéria atualizada com sucesso!', 'success')
      setTimeout(() => {
        closeEditModal()
        loadSubjects()
      }, 1500)
    } else {
      showMessage(
        messageDiv,
        data.message || 'Erro ao atualizar matéria.',
        'error'
      )
    }
  } catch (error) {
    console.error('Erro ao atualizar matéria:', error)
    showMessage(
      messageDiv,
      'Erro ao atualizar matéria. Tente novamente.',
      'error'
    )
  }
}

// Abrir/fechar modais de exclusão
function openDeleteModal(subjectId, subjectName) {
  currentSubjectId = subjectId
  document.getElementById('deleteSubjectName').textContent = subjectName
  document.getElementById('deleteModal').style.display = 'block'
}

function closeDeleteModal() {
  closeModal(document.getElementById('deleteModal'))
  currentSubjectId = null
}

// Excluir matéria
async function confirmDelete() {
  const adminToken = localStorage.getItem('adminToken')
  const messageDiv = document.getElementById('delete-message')

  if (!currentSubjectId) {
    showMessage(messageDiv, 'Erro: ID da matéria não encontrado.', 'error')
    return
  }

  try {
    const response = await fetch(
      `http://localhost:8000/api/admin/subjects/deletar/${currentSubjectId}`,
      {
        method: 'DELETE',
        headers: {
          Authorization: `Bearer ${adminToken}`,
          'Content-Type': 'application/json'
        }
      }
    )

    const data = await response.json()

    if (response.ok) {
      showMessage(messageDiv, 'Matéria excluída com sucesso!', 'success')
      setTimeout(() => {
        closeDeleteModal()
        loadSubjects()
      }, 1500)
    } else {
      showMessage(
        messageDiv,
        data.message || 'Erro ao excluir matéria.',
        'error'
      )
    }
  } catch (error) {
    console.error('Erro ao excluir matéria:', error)
    showMessage(
      messageDiv,
      'Erro ao excluir matéria. Tente novamente.',
      'error'
    )
  }
}

// Mostrar mensagens
function showMessage(element, message, type) {
  element.textContent = message
  element.className = `message ${type}`
  element.style.display = 'block'
  if (type === 'success') {
    setTimeout(() => (element.style.display = 'none'), 3000)
  }
}

// Logout
function logout() {
  const adminToken = localStorage.getItem('adminToken')
  if (adminToken) {
    fetch('http://localhost:8000/api/admin/logout', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${adminToken}`,
        'Content-Type': 'application/json'
      }
    }).finally(() => {
      localStorage.removeItem('adminToken')
      window.location.href = 'index.html'
    })
  } else {
    window.location.href = 'index.html'
  }
}

// Tornar funções globais
window.openEditModal = openEditModal
window.closeEditModal = closeEditModal
window.openDeleteModal = openDeleteModal
window.closeDeleteModal = closeDeleteModal
window.confirmDelete = confirmDelete
