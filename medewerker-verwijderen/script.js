/* =====================================================================
   Het Repertoire — Bestaande medewerker verwijderen
   script.js — medewerker opzoeken, bevestigen, verwijderen
   ===================================================================== */

(() => {
  'use strict';

  const form         = document.getElementById('delete-form');
  const zoekSelect    = document.getElementById('zoek_medewerker');
  const accountCard   = document.getElementById('account-card');
  const warningBox    = document.getElementById('warning-box');
  const bevestigCheck = document.getElementById('bevestig_check');
  const submitBtn     = document.getElementById('submit-btn');
  const statusEl      = document.getElementById('form-status');

  const cardNaam     = document.getElementById('card-naam');
  const cardAfdeling = document.getElementById('card-afdeling');
  const cardFunctie  = document.getElementById('card-functie');
  const cardDatum    = document.getElementById('card-datum');

  const curtainCall = document.getElementById('curtain-call');
  const curtainName = document.getElementById('curtain-call-name');

  // Shared localStorage employee list
  const defaultMedewerkers = [
    { personeelsnummer: 'PN-1001', voornaam: 'Sanne',  achternaam: 'de Vries',  email: 's.devries@auroratheater.nl',  afdeling: 'Artistiek',      functie: 'Regisseur',       datum_in_dienst: '2021-09-01', status: 'actief', telefoonnummer: '0612345678' },
    { personeelsnummer: 'PN-1002', voornaam: 'Daan',   achternaam: 'Bakker',    email: 'd.bakker@auroratheater.nl',   afdeling: 'Techniek',       functie: 'Lichttechnicus',  datum_in_dienst: '2022-02-15', status: 'actief', telefoonnummer: '0623456789' },
    { personeelsnummer: 'PN-1003', voornaam: 'Layla',  achternaam: 'El Amrani', email: 'l.elamrani@auroratheater.nl', afdeling: 'Front of House', functie: 'Kassamedewerker', datum_in_dienst: '2023-05-10', status: 'actief', telefoonnummer: '0634567890' },
    { personeelsnummer: 'PN-1004', voornaam: 'Imraan', achternaam: 'Ghafoori',  email: 'i.ghafoori@auroratheater.nl', afdeling: 'Productie',      functie: 'Producent',       datum_in_dienst: '2026-06-18', status: 'actief', telefoonnummer: '' },
    { personeelsnummer: 'PN-0987', voornaam: 'Tobias', achternaam: 'Hendriks',  email: 't.hendriks@auroratheater.nl', afdeling: 'Techniek',       functie: 'Decorbouwer',     datum_in_dienst: '2019-11-03', status: 'inactief', telefoonnummer: '' },
    { personeelsnummer: 'PN-1005', voornaam: 'Anouk',  achternaam: 'de Jong',   email: 'a.dejong@auroratheater.nl',   afdeling: 'Front of House', functie: 'Kassamedewerker', datum_in_dienst: '2024-01-10', status: 'actief', telefoonnummer: '' }
  ];

  function getEmployees() {
    let list = localStorage.getItem('aurora_theater_medewerkers');
    if (!list) {
      localStorage.setItem('aurora_theater_medewerkers', JSON.stringify(defaultMedewerkers));
      return defaultMedewerkers;
    }
    return JSON.parse(list);
  }

  function saveEmployees(list) {
    localStorage.setItem('aurora_theater_medewerkers', JSON.stringify(list));
  }

  function formatDate(isoDate) {
    if (!isoDate) return '—';
    const d = new Date(isoDate + 'T00:00:00');
    if (Number.isNaN(d.getTime())) return isoDate;
    return d.toLocaleDateString('nl-NL', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function setFieldError(name, message) {
    const input = form.elements[name];
    const errorEl = form.querySelector(`[data-error-for="${name}"]`);
    const fieldWrap = input ? input.closest('.field') : null;
    if (errorEl) errorEl.textContent = message || '';
    if (fieldWrap) fieldWrap.classList.toggle('has-error', Boolean(message));
  }

  function setStatus(message, type) {
    statusEl.textContent = message;
    statusEl.classList.remove('is-error', 'is-success');
    if (type) statusEl.classList.add(`is-${type}`);
  }

  function updateSubmitState() {
    const medewerkerGekozen = Boolean(zoekSelect.value);
    submitBtn.disabled = !(medewerkerGekozen && bevestigCheck.checked);
  }

  function populateDropdown() {
    zoekSelect.innerHTML = '<option value="" disabled selected>Kies een medewerker…</option>';
    const list = getEmployees();
    list.sort((a, b) => a.personeelsnummer.localeCompare(b.personeelsnummer));

    list.forEach(m => {
      const opt = document.createElement('option');
      opt.value = m.personeelsnummer;
      const statusText = m.status === 'inactief' ? ' (inactief)' : '';
      opt.textContent = `${m.personeelsnummer} — ${m.voornaam} ${m.achternaam}${statusText}`;
      zoekSelect.appendChild(opt);
    });
  }

  zoekSelect.addEventListener('change', () => {
    const key = zoekSelect.value;
    const list = getEmployees();
    const medewerker = list.find(m => m.personeelsnummer === key);

    setFieldError('zoek_medewerker', '');
    setStatus('', null);
    bevestigCheck.checked = false;

    if (!medewerker) {
      accountCard.hidden = true;
      warningBox.hidden = true;
      updateSubmitState();
      return;
    }

    cardNaam.textContent = `${medewerker.voornaam} ${medewerker.achternaam}`;
    cardAfdeling.textContent = medewerker.afdeling;
    cardFunctie.textContent = medewerker.functie;
    cardDatum.textContent = formatDate(medewerker.datum_in_dienst);

    accountCard.hidden = false;
    warningBox.hidden = false;
    updateSubmitState();
  });

  bevestigCheck.addEventListener('change', updateSubmitState);

  form.addEventListener('submit', (event) => {
    event.preventDefault();

    if (!zoekSelect.value) {
      setFieldError('zoek_medewerker', 'Kies eerst een medewerker om te verwijderen.');
      zoekSelect.focus();
      return;
    }

    if (!bevestigCheck.checked) {
      setStatus('Bevestig dat je begrijpt dat deze actie permanent is.', 'error');
      bevestigCheck.focus();
      return;
    }

    const key = zoekSelect.value;
    const list = getEmployees();
    const index = list.findIndex(m => m.personeelsnummer === key);

    if (index === -1) {
      setStatus('Fout: medewerker niet gevonden.', 'error');
      return;
    }

    const medewerker = list[index];

    // Verwijder medewerker uit de lijst
    list.splice(index, 1);
    saveEmployees(list);

    // Herlaad dropdown
    populateDropdown();

    curtainName.textContent = `${medewerker.voornaam} ${medewerker.achternaam}`;
    curtainCall.classList.add('is-visible');
    window.clearTimeout(form._curtainTimeout);
    form._curtainTimeout = window.setTimeout(() => {
      curtainCall.classList.remove('is-visible');
    }, 3200);

    setStatus(`${medewerker.voornaam} ${medewerker.achternaam} is verwijderd uit de personeelslijst.`, 'success');

    form.reset();
    accountCard.hidden = true;
    warningBox.hidden = true;
    submitBtn.disabled = true;
  });

  form.addEventListener('reset', () => {
    setFieldError('zoek_medewerker', '');
    setStatus('', null);
    accountCard.hidden = true;
    warningBox.hidden = true;
    submitBtn.disabled = true;
  });

  // Initialisatie
  populateDropdown();

  // Check URL parameter select
  const urlParams = new URLSearchParams(window.location.search);
  const selectId = urlParams.get('select');
  if (selectId) {
    const list = getEmployees();
    const found = list.find(m => m.personeelsnummer === selectId);
    if (found) {
      zoekSelect.value = selectId;
      zoekSelect.dispatchEvent(new Event('change'));
    }
  }
})();

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
