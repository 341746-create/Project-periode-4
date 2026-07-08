/* =====================================================================
   Het Repertoire — Personeelsregistratie
   script.js — form validation, roster rendering, mock "save to database"
   ===================================================================== */

(() => {
  'use strict';

  const form        = document.getElementById('employee-form');
  const statusEl     = document.getElementById('form-status');
  const rosterEl      = document.getElementById('roster');
  const rosterBody    = document.getElementById('roster-body');
  const rosterCount   = document.getElementById('roster-count');
  const curtainCall   = document.getElementById('curtain-call');
  const curtainName   = document.getElementById('curtain-call-name');
  const curtainRole   = document.getElementById('curtain-call-role');

  // Mock data en helperfuncties voor localStorage
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

  let medewerkers = getEmployees();

  const fieldDefs = [
    { name: 'voornaam',         label: 'Voornaam',          required: true },
    { name: 'achternaam',       label: 'Achternaam',        required: true },
    { name: 'personeelsnummer', label: 'Personeelsnummer',  required: true, pattern: /^PN-\d{3,}$/i, patternMsg: 'Gebruik het formaat PN-1004.' },
    { name: 'email',            label: 'E-mailadres',       required: true, type: 'email' },
    { name: 'afdeling',         label: 'Afdeling',          required: true },
    { name: 'functie',          label: 'Functie',           required: true },
    { name: 'datum_in_dienst',  label: 'Datum in dienst',   required: true }
  ];

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

  function clearAllErrors() {
    fieldDefs.forEach(f => setFieldError(f.name, ''));
  }

  function validate(data) {
    medewerkers = getEmployees();
    let firstInvalid = null;
    let isValid = true;

    fieldDefs.forEach(({ name, label, required, type, pattern, patternMsg }) => {
      const value = (data[name] || '').trim();
      let message = '';

      if (required && !value) {
        message = `${label} is verplicht.`;
      } else if (type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        message = 'Voer een geldig e-mailadres in.';
      } else if (pattern && value && !pattern.test(value)) {
        message = patternMsg || `${label} heeft een onverwacht formaat.`;
      }

      // Duplicate personeelsnummer check
      if (!message && name === 'personeelsnummer' && value) {
        const exists = medewerkers.some(
          m => m.personeelsnummer.toLowerCase() === value.toLowerCase()
        );
        if (exists) message = 'Dit personeelsnummer is al in gebruik.';
      }

      setFieldError(name, message);

      if (message) {
        isValid = false;
        if (!firstInvalid) firstInvalid = form.elements[name];
      }
    });

    return { isValid, firstInvalid };
  }

  function renderRoster() {
    medewerkers = getEmployees();
    rosterBody.innerHTML = '';

    medewerkers
      .slice()
      .sort((a, b) => a.achternaam.localeCompare(b.achternaam, 'nl'))
      .forEach((m) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="cell-name">${escapeHtml(m.voornaam)} ${escapeHtml(m.achternaam)}</td>
          <td>${escapeHtml(m.personeelsnummer)}</td>
          <td>${escapeHtml(m.afdeling)}</td>
          <td>${escapeHtml(m.functie)}</td>
          <td>${formatDate(m.datum_in_dienst)}</td>
        `;
        rosterBody.appendChild(tr);
      });

    rosterCount.textContent =
      medewerkers.length === 1 ? '1 medewerker' : `${medewerkers.length} medewerkers`;

    rosterEl.classList.toggle('has-rows', medewerkers.length > 0);
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function showCurtainCall(naam, functie) {
    curtainName.textContent = naam;
    curtainRole.textContent = functie;
    curtainCall.classList.add('is-visible');
    window.clearTimeout(showCurtainCall._t);
    showCurtainCall._t = window.setTimeout(() => {
      curtainCall.classList.remove('is-visible');
    }, 3200);
  }

  function setStatus(message, type) {
    statusEl.textContent = message;
    statusEl.classList.remove('is-error', 'is-success');
    if (type) statusEl.classList.add(`is-${type}`);
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    clearAllErrors();
    setStatus('', null);

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    const { isValid, firstInvalid } = validate(data);

    if (!isValid) {
      setStatus('Controleer de gemarkeerde velden voordat je opslaat.', 'error');
      if (firstInvalid) firstInvalid.focus();
      return;
    }

    const nieuweMedewerker = {
      personeelsnummer: data.personeelsnummer.trim(),
      voornaam: data.voornaam.trim(),
      achternaam: data.achternaam.trim(),
      email: data.email.trim(),
      telefoonnummer: (data.telefoonnummer || '').trim(),
      geboortedatum: data.geboortedatum || null,
      afdeling: data.afdeling,
      functie: data.functie,
      datum_in_dienst: data.datum_in_dienst
    };

    // In production: POST nieuweMedewerker to the backend, which performs
    // INSERT INTO medewerkers (...) VALUES (...) per database.sql.
    medewerkers = getEmployees();
    
    // Dubbel controleer personeelsnummer
    const exists = medewerkers.some(
      m => m.personeelsnummer.toLowerCase() === nieuweMedewerker.personeelsnummer.toLowerCase()
    );
    if (exists) {
      setStatus('Dit personeelsnummer is al in gebruik.', 'error');
      return;
    }

    nieuweMedewerker.status = 'actief';
    medewerkers.push(nieuweMedewerker);
    saveEmployees(medewerkers);
    renderRoster();

    showCurtainCall(
      `${nieuweMedewerker.voornaam} ${nieuweMedewerker.achternaam}`,
      `${nieuweMedewerker.functie} — ${nieuweMedewerker.afdeling}`
    );

    setStatus(
      `${nieuweMedewerker.voornaam} ${nieuweMedewerker.achternaam} is toegevoegd aan de personeelslijst.`,
      'success'
    );

    form.reset();
  });

  form.addEventListener('reset', () => {
    clearAllErrors();
    setStatus('', null);
  });

  // Live-clear a field's error once the person starts correcting it.
  form.addEventListener('input', (event) => {
    const { name } = event.target;
    if (!name) return;
    const fieldWrap = event.target.closest('.field');
    if (fieldWrap && fieldWrap.classList.contains('has-error')) {
      setFieldError(name, '');
    }
  });

  renderRoster();
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
