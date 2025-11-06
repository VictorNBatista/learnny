document.addEventListener('DOMContentLoaded', () => {
    initStudentProfilePage();
});

async function initStudentProfilePage() {
    const token = localStorage.getItem('userToken');
    const userId = localStorage.getItem('userId');

    if (!token || !userId) {
        // Usa modal para mensagem de redirecionamento
        showModal('Sessão Inválida', 'Você precisa estar logado para acessar esta página. Redirecionando...', 'error');
        setTimeout(() => redirectStudentToLogin(), 2500);
        return;
    }

    const form = document.getElementById('student-profile-form');
    // Não precisamos mais do feedbackEl

    let initialData = null;

    try {
        const profile = await fetchStudentProfile(token, userId);
        initialData = normalizeStudentProfile(profile);
        populateStudentForm(initialData);
    } catch (error) {
        console.error('Erro ao carregar perfil do aluno:', error);
        // Usa modal para erros de carregamento
        showModal('Erro ao Carregar', error.message || 'Não foi possível carregar seus dados.', 'error');
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const { payload, validationError } = buildStudentPayload(initialData);

        if (validationError) {
             // Usa modal para erros de validação do lado do cliente (ex: senha não confere)
            showModal('Erro de Validação', validationError, 'error');
            return;
        }

        if (Object.keys(payload).length === 0) {
            // Usa modal para mensagem informativa
            showModal('Nenhuma Alteração', 'Nenhuma alteração foi detectada no seu perfil.', 'info');
            return;
        }

        try {
            const response = await fetch(`http://localhost:8000/api/user/atualizar/${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));

            if (response.status === 401) {
                // Modal para sessão expirada durante a atualização
                showModal('Sessão Expirada', 'Sua sessão expirou. Faça login novamente.', 'error');
                setTimeout(() => redirectStudentToLogin(true), 2500);
                return;
            }

            if (!response.ok) {
                 // Tenta pegar uma mensagem de erro específica da validação do Laravel
                let errorMessage = data.message || 'Erro ao atualizar o perfil.';
                if (data.errors) {
                    const firstErrorKey = Object.keys(data.errors)[0];
                    errorMessage = data.errors[firstErrorKey][0];
                }
                throw new Error(errorMessage);
            }

            // Atualiza initialData apenas com os campos que foram realmente enviados e salvos
            initialData = {
                ...initialData,
                ...filterPersistedStudentData(payload), // Exclui campos de senha
            };

            clearStudentPasswordFields();
             // Usa modal para mensagem de sucesso
            showModal('Sucesso!', 'Seu perfil foi atualizado com sucesso!', 'success');

        } catch (error) {
            console.error('Erro ao salvar perfil do aluno:', error);
            // Usa modal para erros ao salvar
            showModal('Erro ao Salvar', error.message || 'Não foi possível atualizar o perfil.', 'error');
        }
    });
}

function redirectStudentToLogin(clear = false) {
    if (clear) {
        localStorage.removeItem('userToken');
        localStorage.removeItem('userId');
    }
    window.location.href = 'login.html';
}

async function fetchStudentProfile(token, userId) {
    // Garanta que você tem o endpoint correto da API para buscar o perfil de um usuário específico
    const response = await fetch(`http://localhost:8000/api/user/visualizar/${userId}`, { // Ou talvez apenas /api/user ?
        headers: {
            'Authorization': `Bearer ${token}`,
        },
    });

    const payload = await response.json().catch(() => ({}));

    if (response.status === 401) {
        // Nenhum modal necessário aqui
        redirectStudentToLogin(true);
        throw new Error('Sessão expirada.');
    }
    
    // Ajuste com base na estrutura de resposta da sua API (ex: payload.data ou payload diretamente)
    const userProfile = payload.user || payload.data || payload; 

    if (!response.ok || !userProfile) {
        throw new Error(payload.message || 'Não foi possível obter as informações do aluno.');
    }

    return userProfile;
}


function normalizeStudentProfile(profile) {
    return {
        name: profile.name || '',
        email: (profile.email || '').toLowerCase(), // Normaliza o caso do email
        contact: profile.contact || '',
        photo_url: profile.photo_url || '',
        // Não armazena informações de senha buscadas da API
    };
}

function populateStudentForm(initialData) {
    document.getElementById('name').value = initialData.name;
    document.getElementById('email').value = initialData.email;
    document.getElementById('contact').value = initialData.contact;
    document.getElementById('photo_url').value = initialData.photo_url;
    // Limpa campos de senha ao carregar
    clearStudentPasswordFields();
}

function buildStudentPayload(initialData) {
    const payload = {};
    let validationError = null; // Usa null inicialmente

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim().toLowerCase();
    const contact = document.getElementById('contact').value.trim();
    const photo_url = document.getElementById('photo_url').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;

    // Adiciona campos ao payload apenas se eles mudaram
    if (name && name !== initialData.name) {
        payload.name = name;
    }
    if (email && email !== initialData.email) {
        payload.email = email;
    }
    if (contact && contact !== initialData.contact) {
        payload.contact = contact;
    }
    if (photo_url !== initialData.photo_url) {
        payload.photo_url = photo_url === '' ? null : photo_url; 
    }

    // Lida com a lógica de atualização de senha
    if (password || passwordConfirmation) {
        if (!password || !passwordConfirmation) {
            validationError = 'Preencha e confirme a nova senha para atualizá-la.';
        } else if (password !== passwordConfirmation) {
            validationError = 'As senhas informadas não conferem.';
        } else {
             // Adiciona a verificação de regex da senha aqui, se desejado
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (!passwordRegex.test(password)) {
                 validationError = 'A nova senha não atende aos requisitos de segurança (mínimo 8 caracteres, maiúscula, minúscula, número, símbolo).';
            } else {
                payload.password = password;
                payload.password_confirmation = passwordConfirmation;
            }
        }
    }

    return { payload, validationError }; // Retorna tanto o payload quanto o erro potencial
}

// Auxiliar para excluir campos de senha ao atualizar initialData
function filterPersistedStudentData(payload) {
    const { password, password_confirmation, ...rest } = payload;
    return rest;
}

function clearStudentPasswordFields() {
    document.getElementById('password').value = '';
    document.getElementById('password_confirmation').value = '';
}

// A função showFeedback não é mais necessária e pode ser removida