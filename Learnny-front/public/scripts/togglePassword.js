document.addEventListener('DOMContentLoaded', function() {
    console.log("togglePassword.js carregado");
    const togglePassword = document.querySelectorAll('.toggle-password');
    console.log(togglePassword); // Verifique se está encontrando os elementos

    togglePassword.forEach(el => {
        el.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('img');
            console.log(input); // Verifique se o input está sendo encontrado corretamente

            if (input.type === 'password') {
                input.type = 'text';
                icon.src = 'public/images/icons/eye-off.svg'; 
            } else {
                input.type = 'password';
                icon.src = 'public/images/icons/eye.svg'; 
            }
        });
    });
});
