document.addEventListener('DOMContentLoaded', () => {
    initProfessorProfilePage();
});

async function initProfessorProfilePage() {
    const token = localStorage.getItem('professorToken');
    const professorId = localStorage.getItem('professorId');

    if (!token || !professorId) {
        // Usa modal para mensagem de redirecionamento
        showModal('Sessão Inválida', 'Você precisa estar logado como professor para acessar esta página. Redirecionando...', 'error');
        setTimeout(() => redirectProfessorToLogin(), 2500); // Redireciona após mensagem do modal
        return;
    }

    const form = document.getElementById('professor-profile-form');
    // Não precisamos mais do feedbackEl

    let initialData = null;
    let availableSubjects = [];

    try {
        const [subjects, profile] = await Promise.all([
            fetchSubjects(),
            fetchProfessorProfile(token),
        ]);

        availableSubjects = subjects;
        initialData = normalizeProfessorProfile(profile);

        populateProfessorForm(initialData, availableSubjects);
    } catch (error) {
        console.error('Erro ao carregar dados do professor:', error);
        // Usa modal para erros de carregamento
        showModal('Erro ao Carregar', error.message || 'Não foi possível carregar suas informações.', 'error');
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const payload = buildProfessorPayload(initialData);
        if (Object.keys(payload).length === 0) {
            // Usa modal para mensagem informativa
            showModal('Nenhuma Alteração', 'Nenhuma alteração foi detectada no seu perfil.', 'info');
            return;
        }

        try {
            const response = await fetch(`http://localhost:8000/api/professor/atualizar/${professorId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({})); // Lida com casos onde a resposta pode não ser JSON

            if (response.status === 401) {
                // Modal para sessão expirada durante a atualização
                showModal('Sessão Expirada', 'Sua sessão expirou. Faça login novamente.', 'error');
                setTimeout(() => redirectProfessorToLogin(true), 2500);
                return;
            }

            if (!response.ok) {
                 // Tenta pegar uma mensagem de erro específica da validação do Laravel
                let errorMessage = data.message || 'Erro ao salvar alterações.';
                if (data.errors) {
                    const firstErrorKey = Object.keys(data.errors)[0];
                    errorMessage = data.errors[firstErrorKey][0];
                }
                throw new Error(errorMessage);
            }

            // Atualiza initialData com as alterações salvas com sucesso
            const updatedSubjects = payload.subjects !== undefined
                ? [...payload.subjects]
                : initialData.subjects;

            initialData = {
                ...initialData,
                ...payload, // Aplica apenas as mudanças enviadas
                subjects: updatedSubjects,
            };

            // Lida corretamente com a remoção potencial de photo_url
            if ('photo_url' in payload && !payload.photo_url) {
                initialData.photo_url = '';
            }

            // Usa modal para mensagem de sucesso
            showModal('Sucesso!', 'Seu perfil foi atualizado com sucesso!', 'success');

        } catch (error) {
            console.error('Erro ao salvar perfil:', error);
            // Usa modal para erros ao salvar
            showModal('Erro ao Salvar', error.message || 'Não foi possível atualizar o perfil.', 'error');
        }
    });
}

function redirectProfessorToLogin(clearStorage = false) {
    if (clearStorage) {
        localStorage.removeItem('professorToken');
        localStorage.removeItem('professorId');
    }
    window.location.href = 'professor-login.html';
}

async function fetchProfessorProfile(token) {
    const response = await fetch('http://localhost:8000/api/professor/me', {
        headers: {
            'Authorization': `Bearer ${token}`,
        },
    });

    const payload = await response.json().catch(() => ({}));

    if (response.status === 401) {
        // Nenhum modal necessário aqui, pois initProfessorProfilePage lida com a mensagem de redirecionamento
        redirectProfessorToLogin(true);
        throw new Error('Sessão expirada.'); // Lança erro para parar a execução posterior em init
    }

    if (!response.ok || !payload?.data) {
        throw new Error(payload.message || 'Não foi possível obter os dados do professor.');
    }

    return payload.data;
}

async function fetchSubjects() {
    const response = await fetch('http://localhost:8000/api/subject/listar');
    if (!response.ok) {
        throw new Error('Não foi possível carregar as matérias disponíveis.');
    }
    // Assumindo que a API retorna o array diretamente ou dentro de uma chave 'data'
    const result = await response.json();
    return Array.isArray(result) ? result : result.data || [];
}


function normalizeProfessorProfile(profile) {
    // Garante que subjects seja sempre um array de IDs
    const subjectIds = Array.isArray(profile.subjects)
        ? profile.subjects.map((subject) => subject.id)
        : [];

    return {
        name: profile.name || '',
        email: profile.email || '', // Email geralmente é somente leitura, mas é bom ter
        contact: profile.contact || '',
        photo_url: profile.photo_url || '',
        biography: profile.biography || '',
        price: profile.price !== null && profile.price !== undefined
            ? Number(profile.price)
            : '',
        subjects: subjectIds,
    };
}


function populateProfessorForm(initialData, subjectsList) {
    document.getElementById('name').value = initialData.name;
    document.getElementById('email').value = initialData.email; // Geralmente somente leitura
    document.getElementById('contact').value = initialData.contact;
    document.getElementById('photo_url').value = initialData.photo_url;
    document.getElementById('biography').value = initialData.biography;
    document.getElementById('price').value = initialData.price === '' ? '' : initialData.price;

    renderSubjects(subjectsList, initialData.subjects);
}

function renderSubjects(subjects, selectedIds) {
    const container = document.getElementById('subjects-checkbox-list');
    container.innerHTML = '';

    if (!Array.isArray(subjects) || subjects.length === 0) {
        container.innerHTML = '<p>Nenhuma matéria cadastrada no sistema.</p>';
        return;
    }

    subjects.forEach((subject) => {
        const wrapper = document.createElement('label');
        wrapper.classList.add('subject-item'); // Sua classe existente para estilização
        wrapper.setAttribute('for', `subject-${subject.id}`); // Associação correta

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'subjects[]'; // Usa name para simulação/lógica de envio de formulário
        checkbox.value = subject.id;
        checkbox.id = `subject-${subject.id}`;
        // Garante que selectedIds seja tratado como um array antes de usar includes
        checkbox.checked = Array.isArray(selectedIds) && selectedIds.includes(subject.id);

        const text = document.createElement('span'); // Usa span para o texto
        text.textContent = subject.name;

        wrapper.appendChild(checkbox);
        wrapper.appendChild(text);
        container.appendChild(wrapper);
    });
}


function buildProfessorPayload(initialData) {
    const payload = {};

    const name = document.getElementById('name').value.trim();
    if (name && name !== initialData.name) {
        payload.name = name;
    }

    const contact = document.getElementById('contact').value.trim();
    if (contact && contact !== initialData.contact) {
        payload.contact = contact;
    }

    const photoUrl = document.getElementById('photo_url').value.trim();
    // Permite enviar string vazia para potencialmente limpar a URL da foto
    if (photoUrl !== initialData.photo_url) {
        payload.photo_url = photoUrl === '' ? null : photoUrl; // Envia null se estiver vazio
    }

    const biography = document.getElementById('biography').value.trim();
    if (biography !== initialData.biography) {
        payload.biography = biography;
    }

    const priceField = document.getElementById('price').value.trim();
    if (priceField !== '') {
        const parsedPrice = Number(priceField);
        // Garante que o preço é um número válido e realmente mudou
        if (!Number.isNaN(parsedPrice) && parsedPrice >= 0 && parsedPrice !== initialData.price) {
            payload.price = parsedPrice;
        }
    } else if (initialData.price !== '') {
        // Se o campo for limpo e tinha um valor, envia null ou trata como necessário
        // Dependendo da API: pode precisar enviar um valor específico como 0 ou null
        // payload.price = null; // Ou trate com base nos requisitos da API
    }


    const selectedSubjects = Array.from(
        document.querySelectorAll('input[name="subjects[]"]:checked'),
    ).map((checkbox) => Number(checkbox.value));

    // Compara matérias com precisão
    if (!arraysEqual(selectedSubjects, initialData.subjects)) {
        payload.subjects = selectedSubjects;
    }

    return payload;
}

function arraysEqual(arrayA = [], arrayB = []) {
    // Garante que ambos sejam tratados como arrays
    const arrA = Array.isArray(arrayA) ? arrayA : [];
    const arrB = Array.isArray(arrayB) ? arrayB : [];

    if (arrA.length !== arrB.length) return false;
    const sortedA = [...arrA].sort((a, b) => a - b); // Ordena números corretamente
    const sortedB = [...arrB].sort((a, b) => a - b);
    return sortedA.every((value, index) => value === sortedB[index]);
}

// A função showFeedback não é mais necessária e pode ser removida