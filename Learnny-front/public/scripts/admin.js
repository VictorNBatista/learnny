document.addEventListener('DOMContentLoaded', function() {
    checkAdminAuth();  // Verifica autenticação ao carregar a página
    setupLogout();     // Configura botão de logout
    setupCardHover();  // Efeitos de hover nos cards
});

function checkAdminAuth() {
    const adminToken = localStorage.getItem('adminToken');

    if (!adminToken) {
        // Redireciona para login se não houver token
        window.location.href = 'admin-login.html';
        return;
    }

    // Verifica se o token ainda é válido
    verifyAdminToken(adminToken);
}

function verifyAdminToken(token) {
    fetch('http://localhost:8000/api/admin/listar', {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            // Token inválido ou expirado
            localStorage.removeItem('adminToken');
            localStorage.removeItem('adminId');
            window.location.href = 'admin-login.html';
        }
    })
    .catch(error => {
        console.error('Erro ao verificar token:', error);
        localStorage.removeItem('adminToken');
        localStorage.removeItem('adminId');
        window.location.href = 'admin-login.html';
    });
}

function setupLogout() {
    const logoutBtn = document.getElementById('logout-btn');
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }
}

function logout() {
    const adminToken = localStorage.getItem('adminToken');
    
    if (adminToken) {
        fetch('http://localhost:8000/api/admin/logout', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json'
            }
        })
        .finally(() => {
            // Remove token e redireciona mesmo que dê erro
            localStorage.removeItem('adminToken');
            localStorage.removeItem('adminId');
            window.location.href = 'admin-login.html';
        });
    } else {
        window.location.href = 'admin-login.html';
    }
}

function setupCardHover() {
    const adminCards = document.querySelectorAll('.admin-card');
    
    adminCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-0.4rem)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}
