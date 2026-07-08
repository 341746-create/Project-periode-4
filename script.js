// ============================================================
//  Aurora Theater — BestaandeTicketWijzigen frontend script
// ============================================================

// --- Header scroll effect ---
const header = document.getElementById("siteHeader");
window.addEventListener("scroll", () => {
    header.classList.toggle("scrolled", window.scrollY > 50);
});

// --- Smooth scrolling for anchor links ---
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute("href"));
        if (target) {
            target.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    });
});

// --- Dropdown menu ---
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        const dropdown = this.closest('.dropdown');
        if (!dropdown) return;

        const menu = dropdown.querySelector('.dropdown-menu');
        if (!menu) return;

        // Sluit andere dropdowns
        document.querySelectorAll('.dropdown-menu.open').forEach(openMenu => {
            if (openMenu !== menu) openMenu.classList.remove('open');
        });
        document.querySelectorAll('.dropdown.open').forEach(d => {
            if (d !== dropdown) d.classList.remove('open');
        });

        menu.classList.toggle('open');
        dropdown.classList.toggle('open');
    });
});

// Sluit dropdown bij klik buiten
document.addEventListener("click", function(e) {
    document.querySelectorAll(".dropdown-menu.open").forEach(menu => {
        const dropdown = menu.closest(".dropdown");
        if (!dropdown || !dropdown.contains(e.target)) {
            menu.classList.remove("open");
            dropdown?.classList.remove("open");
        }
    });
});

// --- API endpoints ---
const API_UPDATE = 'api/update_ticket.php';
const API_CANCEL = 'api/cancel_ticket.php';

let huidigeEditMax = 10;

// --- Modal helpers ---
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// --- Edit modal ---
function openEditModal(id, stoelen, prijsPerStoel, maxStoelen, betaalmethode) {
    document.getElementById('editId').value = id;
    document.getElementById('editPrijsPerStoel').value = prijsPerStoel;
    document.getElementById('editStoelen').value = stoelen;
    document.getElementById('editStoelen').max = Math.min(maxStoelen, 10);
    huidigeEditMax = Math.min(maxStoelen, 10);

    const methode = betaalmethode || 'ideal';
    const radio = document.querySelector(`#editForm input[name="betaalmethode"][value="${methode}"]`);
    if (radio) radio.checked = true;

    editUpdatePrijs(0);
    openModal('editModal');
}

function closeEditModal() {
    closeModal('editModal');
}

function editUpdatePrijs(delta) {
    const inp = document.getElementById('editStoelen');
    const prijs = parseFloat(document.getElementById('editPrijsPerStoel').value) || 0;
    let nieuw = parseInt(inp.value) + delta;
    nieuw = Math.max(1, Math.min(nieuw, huidigeEditMax));
    inp.value = nieuw;
    const totaal = (prijs * nieuw).toFixed(2).replace('.', ',');
    document.getElementById('editNieuwTotaal').textContent = `€${totaal}`;
}

// --- Cancel modal ---
function openCancelModal(id) {
    document.getElementById('cancelId').value = id;
    openModal('cancelModal');
}

function closeCancelModal() {
    const r = document.getElementById('cancelReden');
    if (r) r.value = '';
    closeModal('cancelModal');
}

// --- Notification ---
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

// --- Add entry to the changelog table (client-side) ---
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
        'bewerkt': 'change-type--edit',
        'geannuleerd': 'change-type--cancel',
    };
    const typeIconMap = {
        'bewerkt': 'fa-pen',
        'geannuleerd': 'fa-xmark',
    };

    const row = document.createElement('tr');
    row.style.animation = 'fadeInUp .4s ease';
    row.innerHTML = `
        <td>${datum}</td>
        <td><span class="change-ticket-code">${ticketCode}</span></td>
        <td>${voorstelling}</td>
        <td>
            <span class="change-type ${typeClassMap[type] || 'change-type--edit'}">
                <i class="fa-solid ${typeIconMap[type] || 'fa-pen'}"></i>
                ${type.charAt(0).toUpperCase() + type.slice(1)}
            </span>
        </td>
        <td>${detail}</td>
    `;

    tbody.insertBefore(row, tbody.firstChild);

    // Update badge count
    const badge = document.querySelector('.changelog-badge');
    if (badge) {
        const count = tbody.querySelectorAll('tr').length;
        badge.textContent = `${count} wijziging(en)`;
    }
}

// --- Edit form submit ---
document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = parseInt(document.getElementById('editId').value);
    const nieuweStoelen = parseInt(document.getElementById('editStoelen').value);
    const methode = document.querySelector('#editForm input[name="betaalmethode"]:checked')?.value ?? 'ideal';

    // Get current values for changelog
    const card = document.querySelector(`.ticket-card[data-id="${id}"]`);
    const ticketCode = card?.querySelector('.ticket-code')?.textContent || `AUR-${id}`;
    const voorstelling = card?.querySelector('h3')?.textContent || '';
    const oudeStoelen = card?.querySelector(`#seats-display-${id}`)?.textContent || '';
    const oudeKosten = card?.querySelector(`#price-display-${id}`)?.textContent || '';

    const payload = {
        id: id,
        aantal_stoelen: nieuweStoelen,
        betaalmethode: methode,
    };

    try {
        const res = await fetch(API_UPDATE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            const prijs = parseFloat(data.totaalprijs).toFixed(2).replace('.', ',');
            document.getElementById('price-display-' + id).innerHTML = `&euro;${prijs}`;
            document.getElementById('seats-display-' + id).textContent = `${data.aantal_stoelen} stoel(en)`;

            // Add to changelog
            addChangelogEntry(ticketCode, voorstelling, 'bewerkt',
                `Stoelen: ${oudeStoelen} → ${data.aantal_stoelen} stoel(en) | Totaal: ${oudeKosten} → €${prijs}`);

            showNotification('Reservering succesvol bijgewerkt!', 'success');
            closeEditModal();
        } else {
            showNotification(data.error ?? 'Kon reservering niet bijwerken.', 'error');
        }
    } catch (err) {
        // Demo mode — simulate success
        const prijsPerStoel = parseFloat(document.getElementById('editPrijsPerStoel').value) || 0;
        const nieuwePrijs = (prijsPerStoel * nieuweStoelen).toFixed(2).replace('.', ',');

        document.getElementById('price-display-' + id).innerHTML = `&euro;${nieuwePrijs}`;
        document.getElementById('seats-display-' + id).textContent = `${nieuweStoelen} stoel(en)`;

        addChangelogEntry(ticketCode, voorstelling, 'bewerkt',
            `Stoelen: ${oudeStoelen} → ${nieuweStoelen} stoel(en) | Totaal: ${oudeKosten} → €${nieuwePrijs}`);

        showNotification('Reservering bijgewerkt (demo-modus).', 'success');
        closeEditModal();
    }
});

// --- Cancel form submit ---
document.getElementById('cancelForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = parseInt(document.getElementById('cancelId').value);

    const card = document.querySelector(`.ticket-card[data-id="${id}"]`);
    const ticketCode = card?.querySelector('.ticket-code')?.textContent || `AUR-${id}`;
    const voorstelling = card?.querySelector('h3')?.textContent || '';

    try {
        const reden = document.getElementById('cancelReden')?.value?.trim() ?? '';
        const res = await fetch(API_CANCEL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, reden: reden }),
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
        // Demo mode — simulate cancel
        if (card) {
            const statusEl = card.querySelector('.status');
            if (statusEl) {
                statusEl.className = 'status status-cancelled';
                statusEl.textContent = 'Geannuleerd';
            }
            const actions = card.querySelector('.ticket-actions');
            if (actions) {
                actions.innerHTML = '<span class="ticket-badge-cancelled"><i class="fa-solid fa-ban"></i> Niet bewerkbaar</span>';
            }
        }

        addChangelogEntry(ticketCode, voorstelling, 'geannuleerd', 'Reservering geannuleerd door gebruiker');
        showNotification('Reservering geannuleerd (demo-modus).', 'success');
        closeCancelModal();
    }
});

// --- Close modals on overlay click ---
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.add('hidden');
        }
    });
});

// --- Close modals on Escape key ---
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(overlay => {
            overlay.classList.add('hidden');
        });
    }
});

// --- Intersection observer for fade-in animations ---
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
