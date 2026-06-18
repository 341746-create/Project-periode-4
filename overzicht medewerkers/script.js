/* =====================================================================
   Het Repertoire — Overzicht medewerkers
   script-overzicht.js — rendering, zoeken/filteren, foutsimulatie
   ===================================================================== */

(() => {
  'use strict';

  // Elementen ophalen
  const toolbar        = document.getElementById('toolbar');
  const zoekInput      = document.getElementById('zoeken');
  const afdelingSelect = document.getElementById('afdeling');
  const resetBtn       = document.getElementById('reset-filters');

  const rosterEl    = document.getElementById('roster');
  const rosterBody  = document.getElementById('roster-body');
  const rosterCount = document.getElementById('roster-count');

  // Aangepast aan jouw HTML-structuur (met fallback naar klasses als ID ontbreekt)
  const playbillOk    = document.getElementById('playbill');
  const playbillError = document.getElementById('playbill-error') || document.querySelector('.curtain-call');
  const simulateBtn   = document.getElementById('simulate-error-btn');
  const retryBtn      = document.getElementById('retry-btn');

  // Mock "personeelslijst"
  const medewerkers = [
    { personeelsnummer: 'PN-1001', voornaam: 'Sanne',  achternaam: 'de Vries',  email: 's.devries@theater.nl',  afdeling: 'Artistiek',      functie: 'Regisseur',       datum_in_dienst: '2021-09-01', status: 'actief' },
    { personeelsnummer: 'PN-1002', voornaam: 'Daan',   achternaam: 'Bakker',    email: 'd.bakker@theater.nl',   afdeling: 'Techniek',       functie: 'Lichttechnicus',  datum_in_dienst: '2022-02-15', status: 'actief' },
    { personeelsnummer: 'PN-1003', voornaam: 'Layla',  achternaam: 'El Amrani', email: 'l.elamrani@theater.nl', afdeling: 'Front of House', functie: 'Kassamedewerker', datum_in_dienst: '2023-05-10', status: 'actief' },
    { personeelsnummer: 'PN-1004', voornaam: 'Imraan', achternaam: 'Ghafoori',  email: 'i.ghafoori@theater.nl', afdeling: 'Productie',      functie: 'Producent',       datum_in_dienst: '2026-06-18', status: 'actief' },
    { personeelsnummer: 'PN-0987', voornaam: 'Tobias', achternaam: 'Hendriks',  email: 't.hendriks@theater.nl', afdeling: 'Techniek',       functie: 'Decorbouwer',     datum_in_dienst: '2019-11-03', status: 'inactief' }
  ];

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function formatDate(isoDate) {
    if (!isoDate) return '—';
    const d = new Date(isoDate + 'T00:00:00');
    if (Number.isNaN(d.getTime())) return isoDate;
    return d.toLocaleDateString('nl-NL', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function getFilteredMedewerkers() {
    // Veiligheidscheck voor als invoervelden niet op de huidige pagina staan
    const zoekterm = zoekInput ? (zoekInput.value || '').trim().toLowerCase() : '';
    const afdeling = afdelingSelect ? afdelingSelect.value : '';

    return medewerkers.filter((m) => {
      const matchesZoek = !zoekterm ||
        m.voornaam.toLowerCase().includes(zoekterm) ||
        m.achternaam.toLowerCase().includes(zoekterm) ||
        m.personeelsnummer.toLowerCase().includes(zoekterm);

      const matchesAfdeling = !afdeling || m.afdeling === afdeling;

      return matchesZoek && matchesAfdeling;
    });
  }

  function renderRoster() {
    if (!rosterBody || !rosterEl) return; // Stop als de tabel niet op de pagina staat

    const resultaten = getFilteredMedewerkers()
      .slice()
      .sort((a, b) => a.achternaam.localeCompare(b.achternaam, 'nl'));

    rosterBody.innerHTML = '';

    resultaten.forEach((m) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="cell-name">${escapeHtml(m.voornaam)} ${escapeHtml(m.achternaam)}</td>
        <td>${escapeHtml(m.personeelsnummer)}</td>
        <td>${escapeHtml(m.afdeling)}</td>
        <td>${escapeHtml(m.functie)}</td>
        <td><a class="cell-email" href="mailto:${escapeHtml(m.email)}">${escapeHtml(m.email)}</a></td>
        <td>${formatDate(m.datum_in_dienst)}</td>
        <td><span class="status-pill status-pill--${escapeHtml(m.status)}">${escapeHtml(m.status)}</span></td>
      `;
      rosterBody.appendChild(tr);
    });

    if (rosterCount) {
      rosterCount.textContent =
        resultaten.length === 1 ? '1 medewerker' : `${resultaten.length} medewerkers`;
    }

    // Gekoppeld aan de nieuwe CSS logica: voeg '.is-empty' toe als er 0 resultaten zijn
    rosterEl.classList.toggle('is-empty', resultaten.length === 0);
  }

  // Event Listeners met ingebouwde null-checks (voorkomt JavaScript crashes)
  if (toolbar) {
    toolbar.addEventListener('submit', (event) => {
      event.preventDefault();
      renderRoster();
    });
  }

  if (zoekInput) {
    zoekInput.addEventListener('input', renderRoster);
  }

  if (afdelingSelect) {
    afdelingSelect.addEventListener('change', renderRoster);
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      if (zoekInput) zoekInput.value = '';
      if (afdelingSelect) afdelingSelect.value = '';
      renderRoster();
    });
  }

  // ---------------------------------------------------------------------
  // Scenario 2: Foutafhandeling / Schermen wisselen
  // ---------------------------------------------------------------------
  function showError() {
    if (playbillOk) playbillOk.style.display = 'none';
    if (playbillError) {
      playbillError.style.display = 'block';
      playbillError.removeAttribute('hidden'); // Zorg dat eventuele HTML-hidden attributen weggaan
    }
  }

  function showOverview() {
    if (playbillError) playbillError.style.display = 'none';
    if (playbillOk) playbillOk.style.display = 'block';
  }

  if (simulateBtn) simulateBtn.addEventListener('click', showError);
  if (retryBtn) retryBtn.addEventListener('click', showOverview);

  // Eerste render bij het laden van de pagina
  renderRoster();
})();