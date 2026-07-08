// ============================================================
//  Aurora Theater — NieuweTicketToevoegen frontend script
// ============================================================

let huidigStap     = 1;
let geselecteerdShow = null;
let prijsPerStoel    = 0;

// --- Stap navigatie ---
function goToStep(stap) {
    if (stap === 2 && !validateStep1()) return;
    if (stap === 3 && !validateStep2()) return;

    document.querySelectorAll('.form-step').forEach(el => el.classList.add('hidden'));
    document.getElementById(`step-${stap}`).classList.remove('hidden');

    document.querySelectorAll('.step').forEach((el, i) => {
        el.classList.remove('active', 'done');
        if (i + 1 < stap) el.classList.add('done');
        if (i + 1 === stap) el.classList.add('active');
    });

    huidigStap = stap;
    if (stap === 3) buildSamenvatting();
}

// --- Reserveer from show card ---
document.querySelectorAll('.reserve-from-show').forEach(btn => {
    btn.addEventListener('click', function() {
        const showId = this.dataset.id;
        const radio = document.querySelector(`input[name="voorstelling_id"][value="${showId}"]`);
        if (!radio) return;

        radio.checked = true;
        geselecteerdShow = radio;
        prijsPerStoel = parseFloat(radio.dataset.prijs) || 0;
        updatePrijs();

        // Highlight selected show card in step 1
        document.querySelectorAll('.show-card').forEach(c => c.classList.remove('selected'));
        const card = radio.closest('.show-card');
        if (card) card.classList.add('selected');

        // Scroll to form and go to step 2
        const formContainer = document.getElementById('reserveringFormContainer');
        if (formContainer) {
            formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        goToStep(2);
    });
});

// --- Validatie Stap 1 ---
function validateStep1() {
    const selected = document.querySelector('input[name="voorstelling_id"]:checked');
    if (!selected) {
        showNotification('Kies eerst een voorstelling.', 'error');
        return false;
    }
    geselecteerdShow = selected;
    prijsPerStoel    = parseFloat(selected.dataset.prijs) || 0;
    updatePrijs();
    hideNotification();
    return true;
}

// --- Validatie Stap 2 ---
function validateStep2() {
    const naam  = document.getElementById('naam').value.trim();
    const email = document.getElementById('email').value.trim();
    if (!naam) {
        showNotification('Vul uw naam in.', 'error');
        return false;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showNotification('Vul een geldig e-mailadres in.', 'error');
        return false;
    }
    hideNotification();
    return true;
}

// --- Prijs bijwerken ---
function updatePrijs() {
    const aantal = parseInt(document.getElementById('aantal_stoelen').value) || 1;
    const totaal = (prijsPerStoel * aantal).toFixed(2).replace('.', ',');
    document.getElementById('totalePrijs').textContent = `€${totaal}`;
}

// --- Counter knoppen ---
document.getElementById('plusBtn').addEventListener('click', () => {
    const inp = document.getElementById('aantal_stoelen');
    const max = geselecteerdShow ? parseInt(geselecteerdShow.dataset.beschikbaar) : 10;
    if (parseInt(inp.value) < Math.min(max, 10)) {
        inp.value = parseInt(inp.value) + 1;
        updatePrijs();
    }
});
document.getElementById('minBtn').addEventListener('click', () => {
    const inp = document.getElementById('aantal_stoelen');
    if (parseInt(inp.value) > 1) {
        inp.value = parseInt(inp.value) - 1;
        updatePrijs();
    }
});

// --- Show cards klikbaar maken ---
document.querySelectorAll('.show-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.show-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        const radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    });
});

// --- Samenvatting bouwen ---
function buildSamenvatting() {
    const voorstelling = document.querySelector('input[name="voorstelling_id"]:checked');
    const naam         = document.getElementById('naam').value.trim();
    const email        = document.getElementById('email').value.trim();
    const aantal       = parseInt(document.getElementById('aantal_stoelen').value);
    const methode      = document.querySelector('input[name="betaalmethode"]:checked')?.value ?? 'ideal';
    const totaal       = (prijsPerStoel * aantal).toFixed(2).replace('.', ',');

    // Haal titel van geselecteerde show-card
    const titel = voorstelling
        ? voorstelling.closest('.show-card')?.querySelector('h3')?.textContent ?? 'Onbekend'
        : 'Onbekend';

    document.getElementById('samenvattingInhoud').innerHTML = `
        <div class="samenvatting-rij"><span>Naam</span><span>${escapeHtml(naam)}</span></div>
        <div class="samenvatting-rij"><span>E-mail</span><span>${escapeHtml(email)}</span></div>
        <div class="samenvatting-rij"><span>Voorstelling</span><span>${escapeHtml(titel)}</span></div>
        <div class="samenvatting-rij"><span>Aantal stoelen</span><span>${aantal}</span></div>
        <div class="samenvatting-rij"><span>Betaalmethode</span><span>${escapeHtml(methode)}</span></div>
        <div class="samenvatting-rij"><span>Totaal</span><span>€${totaal}</span></div>
    `;
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// --- Formulier verzenden ---
document.getElementById('reserveringForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Bezig met reserveren...';

    const voorstelling = document.querySelector('input[name="voorstelling_id"]:checked');
    const methode      = document.querySelector('input[name="betaalmethode"]:checked');

    const payload = {
        naam:            document.getElementById('naam').value.trim(),
        email:           document.getElementById('email').value.trim(),
        voorstelling_id: voorstelling ? parseInt(voorstelling.value) : null,
        aantal_stoelen:  parseInt(document.getElementById('aantal_stoelen').value),
        betaalmethode:   methode ? methode.value : 'ideal',
    };

    try {
        const res  = await fetch('api/create_ticket.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('successMessage').textContent =
                `Uw reservering voor ${data.reservering?.voorstelling ?? 'de voorstelling'} is bevestigd!`;
            document.getElementById('ticketCodeDisplay').textContent =
                data.reservering?.ticket_code ?? 'AUR-DEMO';

            if (data.reservering && data.reservering.ticket_code) {
                const parts = data.reservering.ticket_code.split('-');
                if (parts.length === 3) {
                    const gid = parseInt(parts[2]);
                    if (!isNaN(gid)) {
                        document.cookie = `gebruiker_id=${gid}; path=/; max-age=31536000`;
                    }
                }
            }

            document.querySelectorAll('.form-step').forEach(el => el.classList.add('hidden'));
            document.getElementById('step-4').classList.remove('hidden');
        } else {
            showNotification(data.error ?? 'Er is een fout opgetreden.', 'error');
        }
    } catch (err) {
        showNotification('Verbindingsfout — probeer het later opnieuw.', 'error');
    } finally {
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Bevestig Reservering';
    }
});

// --- Notificaties ---
function showNotification(msg, type) {
    const n = document.getElementById('notification');
    n.textContent  = msg;
    n.className    = `notification ${type}`;
    n.style.display = 'block';
    n.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
function hideNotification() {
    const n = document.getElementById('notification');
    n.style.display = 'none';
}

// Dropdown toggle function
function toggleDropdown(btn) {
    const menu = btn.nextElementSibling;
    if (menu && menu.classList.contains('dropdown-menu')) {
        menu.classList.toggle('open');
        btn.classList.toggle('active');
    }
    // Close other dropdowns
    document.querySelectorAll('.dropdown-menu.open').forEach(m => {
        if (m !== menu) {
            m.classList.remove('open');
            m.previousElementSibling.classList.remove('active');
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.open').forEach(m => {
            m.classList.remove('open');
        });
        document.querySelectorAll('.dropdown-toggle.active').forEach(b => {
            b.classList.remove('active');
        });
    }
});
