// ============================================================
//  Aurora Theater — Ticket Overzicht script
//  Communiceert met de PHP API endpoints
// ============================================================

let currentTicketId = null;

// --- Modal helpers ---
function openEditModal(id) {
    currentTicketId = id;
    document.getElementById('editTicketId').value = id;
    document.getElementById('editModal').style.display = 'block';
}

function openCancelModal(id) {
    currentTicketId = id;
    document.getElementById('cancelTicketId').value = id;
    document.getElementById('cancelModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Sluit modal bij klik buiten de modal-content
window.addEventListener('click', e => {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

// --- Ticket bewerken ---
document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const id            = parseInt(document.getElementById('editTicketId').value);
    const aantal        = parseInt(document.getElementById('editStoelen').value);
    const betaalmethode = document.getElementById('editBetaalmethode').value;

    try {
        const res  = await fetch('api/update_ticket.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id, aantal_stoelen: aantal, betaalmethode }),
        });
        const data = await res.json();

        if (data.success) {
            showNotification('Ticket succesvol bijgewerkt! Pagina wordt herladen...', true);
            closeModal('editModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.error ?? 'Bijwerken mislukt.', false);
        }
    } catch {
        showNotification('Verbindingsfout — probeer opnieuw.', false);
    }
});

// --- Ticket annuleren ---
document.getElementById('confirmCancel').addEventListener('click', async function() {
    const id = parseInt(document.getElementById('cancelTicketId').value);

    try {
        const res  = await fetch('api/cancel_ticket.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id }),
        });
        const data = await res.json();

        if (data.success) {
            showNotification('Ticket geannuleerd. Pagina wordt herladen...', true);
            closeModal('cancelModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.error ?? 'Annuleren mislukt.', false);
        }
    } catch {
        showNotification('Verbindingsfout — probeer opnieuw.', false);
    }
});

// --- Notificatie ---
function showNotification(msg, success) {
    const n     = document.getElementById('notification');
    n.textContent = msg;
    n.className   = success ? 'notification' : 'notification error';
    n.style.display = 'block';
    setTimeout(() => { n.style.display = 'none'; }, 4000);
}