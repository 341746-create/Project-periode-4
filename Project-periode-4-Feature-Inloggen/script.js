// ── Feature-Inloggen script.js ──
// Het formulier submits naar auth.php (PHP backend in deze map).
// Dit script verzorgt alleen de client-side interacties.

document.addEventListener('DOMContentLoaded', () => {

    /* ── 1. Wachtwoord tonen/verbergen ── */
    const toggleBtn   = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon     = document.getElementById('eyeIcon');

    if (toggleBtn && passwordInput && eyeIcon) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            eyeIcon.classList.toggle('fa-eye',       !isHidden);
            eyeIcon.classList.toggle('fa-eye-slash',  isHidden);
        });
    }

    /* ── 2. Knop feedback bij submit ── */
    const loginForm = document.getElementById('loginForm');
    const submitBtn = loginForm ? loginForm.querySelector('.btn-login') : null;

    if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', () => {
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Bezig...';
            submitBtn.disabled = true;
        });
    }

    /* ── 3. Snel inloggen als beheerder ── */
    const quickAdminBtn = document.getElementById('quickAdminBtn');
    if (quickAdminBtn && loginForm) {
        quickAdminBtn.addEventListener('click', () => {
            document.getElementById('email').value    = 'admin@aurora-theater.nl';
            document.getElementById('password').value = 'Admin@2026';
            loginForm.requestSubmit();
        });
    }

    const params = new URLSearchParams(window.location.search);
    if (params.has('error')) {
        const banner = document.getElementById('loginError');
        const text   = document.getElementById('loginErrorText');
        if (banner && text) {
            if (params.get('error') === '2') {
                text.textContent = 'Er ging iets mis. Probeer het opnieuw.';
            }
            banner.style.display = 'flex';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Sign In <i class="fa-solid fa-arrow-right"></i>';
            }
        }
    }

});