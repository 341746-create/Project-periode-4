// ============================================================
//  Aurora Theater — Ticket Scanner (Professional)
//  Expliciete happy / unhappy scenario's
// ============================================================

const scanBtn = document.getElementById('scanBtn');
const ticketInput = document.getElementById('ticketCode');
const resultDiv = document.getElementById('result');
const historyList = document.getElementById('historyList');
let scanHistory = [];
let isScanning = false;

const SCENARIOS = {
    // Happy
    ok: {
        theme: 'success',
        icon: 'fa-circle-check',
        title: 'Ticket Geldig — Welkom!',
    },
    // Unhappy
    already_checked: {
        theme: 'warning',
        icon: 'fa-circle-xmark',
        title: 'Al Ingecheckt',
    },
    cancelled: {
        theme: 'error',
        icon: 'fa-ban',
        title: 'Ticket Geannuleerd',
    },
    not_found: {
        theme: 'error',
        icon: 'fa-circle-question',
        title: 'Niet Gevonden',
    },
    invalid_format: {
        theme: 'warning',
        icon: 'fa-triangle-exclamation',
        title: 'Ongeldige Code',
    },
    past_show: {
        theme: 'warning',
        icon: 'fa-clock',
        title: 'Voorstelling Voorbij',
    },
    db_error: {
        theme: 'warning',
        icon: 'fa-server',
        title: 'Serverfout',
    },
    method_not_allowed: {
        theme: 'error',
        icon: 'fa-ban',
        title: 'Ongeldig Verzoek',
    },
    missing_code: {
        theme: 'warning',
        icon: 'fa-circle-info',
        title: 'Leeg veld',
    },
};

async function scanTicket() {
    if (isScanning) return;

    const code = ticketInput.value.trim();
    if (!code) {
        renderScenario('missing_code', 'Voer een ticketcode in.');
        ticketInput.focus();
        return;
    }

    isScanning = true;
    scanBtn.disabled = true;
    scanBtn.classList.add('scanning');
    scanBtn.innerHTML = '<span class="scan-dot"></span> Scanning...';
    showScanningState();

    try {
        const response = await fetch('api/scan_ticket.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code }),
        });

        const data = await response.json();
        const reason = data.reason || (data.success ? 'ok' : 'unknown');
        const scenario = SCENARIOS[reason] || SCENARIOS.not_found;

        if (data.success) {
            renderSuccess(scenario, data.ticket);
            addToHistory(code, true, reason);
        } else {
            renderError(scenario, data.error, data.ticket);
            addToHistory(code, false, reason);
        }
        resultDiv.style.display = 'block';
    } catch (err) {
        renderError(SCENARIOS.db_error, 'Verbindingsfout. Controleer of de server actief is en probeer opnieuw.');
        addToHistory(code, false, 'db_error');
    } finally {
        isScanning = false;
        scanBtn.disabled = false;
        scanBtn.classList.remove('scanning');
        scanBtn.innerHTML = '<span>&#128269;</span> Scan Ticket';
        ticketInput.value = '';
        ticketInput.focus();
    }
}

function showScanningState() {
    resultDiv.className = 'scanning-state';
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = `
        <div class="scan-animation">
            <div class="scan-line"></div>
            <i class="fa-solid fa-barcode"></i>
        </div>
        <p>Bezig met scannen...</p>
    `;
}

function ticketImageHtml(ticket) {
    if (!ticket || !ticket.afbeelding_url) return '';
    const alt = escapeHtml(ticket.voorstelling || 'Voorstelling');
    const title = escapeHtml(ticket.voorstelling || 'Aurora Theater');
    return `
        <div class="ticket-image">
            <img src="${escapeHtml(ticket.afbeelding_url)}" alt="${alt}" loading="lazy"
                 onerror="ticketImageFallback(this, '${title.replace(/'/g, '&#039;')}')"
                 onload="ticketImageLoaded(this)">
        </div>
    `;
}

function ticketImageLoaded(img) {
    img.closest('.ticket-image').classList.add('loaded');
}

function ticketImageFallback(img, title) {
    const ph = document.createElement('div');
    ph.className = 'ticket-image-fallback';
    ph.innerHTML = `<span class="tif-emoji">🎭</span><span class="tif-title">${title}</span>`;
    if (img.parentNode) img.parentNode.replaceChild(ph, img);
}

function renderSuccess(scenario, ticket) {
    resultDiv.className = `result-card ${scenario.theme}`;
    resultDiv.innerHTML = `
        ${ticketImageHtml(ticket)}
        <div class="result-content">
            <div class="result-icon"><i class="fa-solid ${scenario.icon}"></i></div>
            <div class="result-header">
                <h2>${scenario.title}</h2>
                <span class="badge badge-ok">Ingang Vrij</span>
            </div>
            <div class="result-body">
                <div class="result-row"><span>Ticketcode</span><span>${escapeHtml(ticket.code)}</span></div>
                <div class="result-row"><span>Voorstelling</span><span>${escapeHtml(ticket.voorstelling)}</span></div>
                <div class="result-row"><span>Datum & Tijd</span><span>${escapeHtml(ticket.datum)}</span></div>
                <div class="result-row"><span>Zaal</span><span>${escapeHtml(ticket.zaal)}</span></div>
                <div class="result-row"><span>Klant</span><span>${escapeHtml(ticket.klant)}</span></div>
                <div class="result-row"><span>Aantal plaatsen</span><span>${escapeHtml(ticket.stoelen)}</span></div>
                <div class="result-row"><span>Status</span><span class="badge badge-ok">${escapeHtml(ticket.status)}</span></div>
            </div>
            <div class="result-footer">
                <p class="success-message"><i class="fa-solid fa-door-open"></i> Geniet van de voorstelling!</p>
            </div>
        </div>
    `;
}

function renderError(scenario, message, ticket) {
    resultDiv.className = `result-card ${scenario.theme}`;
    resultDiv.innerHTML = `
        ${ticketImageHtml(ticket)}
        <div class="result-icon"><i class="fa-solid ${scenario.icon}"></i></div>
        <div class="result-header">
            <h2>${scenario.title}</h2>
        </div>
        <div class="result-body">
            <p class="error-message">${escapeHtml(message)}</p>
        </div>
        <div class="result-footer">
            <p class="hint"><i class="fa-solid fa-circle-info"></i> Neem contact op met de kassa of probeer een andere code.</p>
            </div>
    `;
}

function addToHistory(code, valid, reason) {
    const time = new Date().toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    scanHistory.unshift({ code, valid, time, reason });
    if (scanHistory.length > 10) scanHistory.pop();

    const iconMap = {
        ok: 'fa-circle-check',
        already_checked: 'fa-circle-xmark',
        cancelled: 'fa-ban',
        not_found: 'fa-circle-question',
        invalid_format: 'fa-triangle-exclamation',
        past_show: 'fa-clock',
        db_error: 'fa-server',
    };
    const cssClass = valid ? 'hist-valid' : 'hist-invalid';
    const icon = iconMap[reason] || (valid ? 'fa-circle-check' : 'fa-circle-xmark');

    historyList.innerHTML = scanHistory.map(s =>
        `<li class="${cssClass}">
            <span><i class="fa-solid ${icon}"></i> ${escapeHtml(s.code)}</span>
            <small>${s.time}</small>
        </li>`
    ).join('');

    if (!scanHistory.length) {
        historyList.innerHTML = '<li class="history-empty">Nog geen scans uitgevoerd.</li>';
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Event listeners
scanBtn.addEventListener('click', scanTicket);
ticketInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') scanTicket();
});
ticketInput.addEventListener('input', () => {
    if (resultDiv.classList.contains('scanning-state')) {
        resultDiv.style.display = 'none';
        resultDiv.className = '';
        resultDiv.innerHTML = '';
    }
});

// --- Dropdown toggle (header) ---
function toggleDropdown(e) {
    e.preventDefault();
    const dropdown = e.currentTarget.closest('.dropdown');
    if (!dropdown) return;

    const menu = dropdown.querySelector('.dropdown-menu');
    const toggle = dropdown.querySelector('.dropdown-toggle');
    if (!menu) return;

    document.querySelectorAll('.dropdown-menu.open').forEach(openMenu => {
        if (openMenu !== menu) {
            openMenu.classList.remove('open');
            openMenu.closest('.dropdown')?.querySelector('.dropdown-toggle')?.classList.remove('active');
        }
    });

    menu.classList.toggle('open');
    toggle?.classList.toggle('active');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.open').forEach(menu => menu.classList.remove('open'));
        document.querySelectorAll('.dropdown-toggle.active').forEach(toggle => toggle.classList.remove('active'));
    }
});
