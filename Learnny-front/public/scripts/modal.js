const modal = document.getElementById('global-modal');
const modalTitle = document.getElementById('modal-title');
const modalMessage = document.getElementById('modal-message');
const modalBtnConfirm = document.getElementById('modal-btn-confirm');
const modalBtnCancel = document.getElementById('modal-btn-cancel');
const modalContent = modal.querySelector('.modal-content');

// Variável para guardar a "promessa" da confirmação
let resolveConfirm;

/**
 * Exibe um modal de alerta simples (com um só botão "OK").
 * @param {string} title - O título do modal.
 * @param {string} message - A mensagem a ser exibida.
 * @param {string} type - 'info' (default), 'success', ou 'error' para estilização.
 */
function showModal(title, message, type = 'info') {
    modalTitle.textContent = title;
    modalMessage.textContent = message;

    // Remove classes de tipo antigas e adiciona a nova
    modalContent.classList.remove('modal-success', 'modal-error');
    if (type === 'success') {
        modalContent.classList.add('modal-success');
    } else if (type === 'error') {
        modalContent.classList.add('modal-error');
    }

    // Configura botões: só o "OK" aparece
    modalBtnConfirm.textContent = 'OK';
    modalBtnConfirm.style.display = 'block';
    modalBtnCancel.style.display = 'none'; // Esconde o "Cancelar"

    modal.classList.remove('hidden');

    // Quando o "OK" for clicado, apenas fecha o modal
    modalBtnConfirm.onclick = () => {
        modal.classList.add('hidden');
    };
    // Limpa qualquer clique anterior
    modalBtnCancel.onclick = null;
}

/**
 * Exibe um modal de confirmação (com "Confirmar" e "Cancelar").
 * Retorna uma Promessa que resolve para 'true' (Confirmar) ou 'false' (Cancelar).
 * @param {string} title - O título da confirmação.
 * @param {string} message - A pergunta de confirmação.
 * @returns {Promise<boolean>}
 */
function showConfirm(title, message) {
    modalTitle.textContent = title;
    modalMessage.textContent = message;

    // Garante que não há classes de estilo (sucesso/erro)
    modalContent.classList.remove('modal-success', 'modal-error');

    // Configura botões: "Confirmar" e "Cancelar" aparecem
    modalBtnConfirm.textContent = 'Confirmar';
    modalBtnConfirm.style.display = 'block';
    modalBtnCancel.style.display = 'block';

    modal.classList.remove('hidden');

    // Retorna uma promessa que espera a decisão do usuário
    return new Promise((resolve) => {
        resolveConfirm = resolve; // Salva a função 'resolve' globalmente
    });
}

// Adiciona os cliques principais aos botões
if (modal) {
    modalBtnConfirm.addEventListener('click', () => {
        modal.classList.add('hidden');
        if (resolveConfirm) {
            resolveConfirm(true); // Resolve a promessa com 'true'
            resolveConfirm = null; // Limpa a promessa
        }
    });

    modalBtnCancel.addEventListener('click', () => {
        modal.classList.add('hidden');
        if (resolveConfirm) {
            resolveConfirm(false); // Resolve a promessa com 'false'
            resolveConfirm = null; // Limpa a promessa
        }
    });
}