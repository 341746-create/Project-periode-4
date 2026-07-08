// ── Feature-Uitloggen script.js ──

// Bevestig uitloggen via de knop
const logoutBtn = document.getElementById('logoutBtn');
const cancelBtn = document.getElementById('cancelBtn');
const card      = document.getElementById('logoutCard');

// Kleine animatie op hover van de confirm-knop
if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
        // Laat de link zijn werk doen (→ /logout.php)
        card.style.opacity = '0.6';
        card.style.transform = 'scale(0.97)';
    });
}

// Annuleren → sluit de kaart (geen navigatie)
if (cancelBtn) {
    cancelBtn.addEventListener('click', function() {
        card.style.animation = 'fadeDown 0.3s ease forwards';
    });
}

// Escape-toets - geen actie (logo is de enige manier naar homepage)
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        card.style.animation = 'fadeDown 0.3s ease forwards';
    }
});
