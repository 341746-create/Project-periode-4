// ============================================================
//  Aurora Theater — BestaandeTicketAnnuleren frontend script
// ============================================================

const header = document.getElementById("siteHeader");
window.addEventListener("scroll", () => {
    header.classList.toggle("scrolled", window.scrollY > 50);
});

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute("href"));
        if (target) {
            target.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    });
});

const API_CANCEL = 'api/cancel_ticket.php';

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openCancelModal(id) {
    document.getElementById('cancelId').value = id;
    openModal('cancelModal');
}

function closeCancelModal() {
    closeModal('cancelModal');
}

function showNotification(msg, type) {
    const n = document.getElementById('notification');
    const iconMap = {
        success: 'fa-circle-check',
        error: 'fa-circle-xmark',
        info: 'fa-circle-info'
    };
    const icon = iconMap[type] || 'fa-circle-info';
    n.innerHTML = `<i class="fa-solid ${icon}"></i> ${msg}`;
    n.className = `notification ${type}`;
    n.style.display = 'flex';
    setTimeout(() => {
        n.style.display = 'none';
    }, 4000);
}

function addChangelogEntry(ticketCode, voorstelling, type, detail) {
    const table = document.getElementById('changelogTable');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const now = new Date();
    const datum = now.toLocaleDateString('nl-NL', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }).replace(',', '');

    const typeClassMap = {
        'geannuleerd': 'change-type--cancel',
        'aangemaakt': 'change-type--create',
    };
    const typeIconMap = {
        'geannuleerd': 'fa-xmark',
        'aangemaakt': 'fa-plus',
    };

    const row = document.createElement('tr');
    row.style.animation = 'fadeInUp .4s ease';
    row.innerHTML = `
        <td>${datum}</td>
        <td><span class="change-ticket-code">${ticketCode}</span></td>
        <td>${voorstelling}</td>
        <td>
            <span class="change-type ${typeClassMap[type] || 'change-type--cancel'}">
                <i class="fa-solid ${typeIconMap[type] || 'fa-xmark'}"></i>
                ${type.charAt(0).toUpperCase() + type.slice(1)}
            </span>
        </td>
        <td>${detail}</td>
    `;

    tbody.insertBefore(row, tbody.firstChild);

    const badge = document.querySelector('.changelog-badge');
    if (badge) {
        const count = tbody.querySelectorAll('tr').length;
        badge.textContent = `${count} wijziging(en)`;
    }
}

document.getElementById('cancelForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = parseInt(document.getElementById('cancelId').value);

    const card = document.querySelector(`.ticket-card[data-id="${id}"]`);
    const ticketCode = card?.querySelector('.ticket-code')?.textContent || `AUR-${id}`;
    const voorstelling = card?.querySelector('h3')?.textContent || '';

    try {
        const res = await fetch(API_CANCEL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id }),
        });
        const data = await res.json();

        if (data.success) {
            addChangelogEntry(ticketCode, voorstelling, 'geannuleerd', 'Reservering geannuleerd door gebruiker');
            showNotification('Reservering succesvol geannuleerd.', 'success');
            closeCancelModal();
            setTimeout(() => location.reload(), 1200);
        } else {
            showNotification(data.error ?? 'Kon reservering niet annuleren.', 'error');
        }
    } catch (err) {
        if (card) {
            const statusEl = card.querySelector('.status');
            if (statusEl) {
                statusEl.className = 'status status-cancelled';
                statusEl.textContent = 'Geannuleerd';
            }
            const actions = card.querySelector('.ticket-actions');
            if (actions) {
                actions.innerHTML = '<span class="ticket-badge-cancelled"><i class="fa-solid fa-ban"></i> Al geannuleerd</span>';
            }
        }

        addChangelogEntry(ticketCode, voorstelling, 'geannuleerd', 'Reservering geannuleerd door gebruiker');
        showNotification('Reservering geannuleerd (demo-modus).', 'success');
        closeCancelModal();
    }
});

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.add('hidden');
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(overlay => {
            overlay.classList.add('hidden');
        });
    }
});

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.fade-in').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    observer.observe(el);
});
