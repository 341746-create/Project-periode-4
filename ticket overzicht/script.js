// ============================================================
//  Aurora Theater — Ticket Overzicht script
// ============================================================

let currentTicketId = null;

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

window.addEventListener('click', e => {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

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
            showNotification('Ticket succesvol bijgewerkt! Pagina wordt herladen...', 'success');
            closeModal('editModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.error ?? 'Bijwerken mislukt.', 'error');
        }
    } catch {
        showNotification('Verbindingsfout — probeer opnieuw.', 'error');
    }
});

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
            showNotification('Ticket geannuleerd. Pagina wordt herladen...', 'success');
            closeModal('cancelModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.error ?? 'Annuleren mislukt.', 'error');
        }
    } catch {
        showNotification('Verbindingsfout — probeer opnieuw.', 'error');
    }
});

function showNotification(msg, type) {
    const n     = document.getElementById('notification');
    n.innerHTML = msg;
    n.className   = 'notification ' + type;
    n.style.display = 'block';
    setTimeout(() => { n.style.display = 'none'; }, 4000);
}
