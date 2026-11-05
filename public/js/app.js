// Plain Vanilla JavaScript Application Helper
document.addEventListener('DOMContentLoaded', () => {
    // Quick-fill login helper chips
    const chips = document.querySelectorAll('.chip[data-login]');
    const loginInput = document.getElementById('login');
    const passwordInput = document.getElementById('password');

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            const login = chip.getAttribute('data-login');
            const pass = chip.getAttribute('data-password') || 'password123';
            if (loginInput) loginInput.value = login;
            if (passwordInput) passwordInput.value = pass;
        });
    });

    // Auto-dismiss alerts after 5 seconds
    const autoAlerts = document.querySelectorAll('.alert-dismissible');
    autoAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
