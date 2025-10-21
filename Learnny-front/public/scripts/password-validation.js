document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const ruleLength = document.getElementById('rule-length');
    const ruleUppercase = document.getElementById('rule-uppercase');
    const ruleLowercase = document.getElementById('rule-lowercase');
    const ruleNumber = document.getElementById('rule-number');
    const ruleSymbol = document.getElementById('rule-symbol');

    // Garante que todos os elementos existem antes de continuar
    if (!passwordInput || !ruleLength || !ruleUppercase || !ruleLowercase || !ruleNumber || !ruleSymbol) {
        return;
    }

    // Adiciona um listener que dispara a cada tecla digitada no campo de senha
    passwordInput.addEventListener('input', () => {
        const password = passwordInput.value;

        // 1. Validação de Comprimento (mínimo de 8 caracteres)
        // A classe 'valid' é adicionada se a condição for verdadeira, e removida se for falsa
        ruleLength.classList.toggle('valid', password.length >= 8);

        // 2. Validação de Letra Maiúscula
        // /[A-Z]/ é uma expressão regular que busca por qualquer letra de A a Z
        ruleUppercase.classList.toggle('valid', /[A-Z]/.test(password));

        // 3. Validação de Letra Minúscula
        // /[a-z]/ busca por qualquer letra de a a z
        ruleLowercase.classList.toggle('valid', /[a-z]/.test(password));

        // 4. Validação de Número
        // /\d/ busca por qualquer dígito (0-9)
        ruleNumber.classList.toggle('valid', /\d/.test(password));

        // 5. Validação de Símbolo
        // /[\W_]/ busca por qualquer caractere que NÃO seja letra ou número (inclui _ e espaços)
        ruleSymbol.classList.toggle('valid', /[\W_]/.test(password));
    });
});