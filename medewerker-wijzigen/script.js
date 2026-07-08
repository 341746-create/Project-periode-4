/* =====================================================================
   Het Repertoire — Bestaande medewerker wijzigen
   script.js — medewerker opzoeken, gegevens bijwerken, opslaan
   ===================================================================== */

(() => {
  'use strict';

  const form         = document.getElementById('employee-form');
  const zoekSelect    = document.getElementById('zoek_medewerker');
  const bewerkVelden  = document.getElementById('bewerk-velden');
  const rolVelden      = document.getElementById('rol-velden');
  const submitBtn     = document.getElementById('submit-btn');
  const statusEl      = document.getElementById('form-status');

  const voornaamInput      = document.getElementById('voornaam');
  const achternaamInput    = document.getElementById('achternaam');
  const emailInput         = document.getElementById('email');
  const telefoonInput      = document.getElementById('telefoonnummer');
  const afdelingSelect     = document.getElementById('afdeling');
  const functieSelect      = document.getElementById('functie');
  const statusSelect       = document.getElementById('status');

  const curtainCall = document.getElementById('curtain-call');
  const curtainName = document.getElementById('curtain-call-name');
  const curtainRole = document.getElementById('curtain-call-role');

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

  function getMedewerkersObject() {
    const arr = getEmployees();
    const obj = {};
    arr.forEach(m => {
      obj[m.personeelsnummer] = m;
    });
    return obj;
  }

  const fieldDefs = [
    { name: 'voornaam',    label: 'Voornaam',     required: true },
    { name: 'achternaam',  label: 'Achternaam',   required: true },
    { name: 'email',       label: 'E-mailadres',  required: true, type: 'email' },
    { name: 'afdeling',    label: 'Afdeling',     required: true },
    { name: 'functie',     label: 'Functie',      required: true },
    { name: 'status',      label: 'Status',       required: true }
  ];

  function setFieldError(name, message) {
    const input = form.elements[name];
    const errorEl = form.querySelector(`[data-error-for="${name}"]`);
    const fieldWrap = input ? input.closest('.field') : null;
    if (errorEl) errorEl.textContent = message || '';
    if (fieldWrap) fieldWrap.classList.toggle('has-error', Boolean(message));
  }

  function clearAllErrors() {
    fieldDefs.forEach(f => setFieldError(f.name, ''));
    setFieldError('zoek_medewerker', '');
  }

  function setStatus(message, type) {
    statusEl.textContent = message;
    statusEl.classList.remove('is-error', 'is-success');
    if (type) statusEl.classList.add(`is-${type}`);
  }

  function vulVeldenIn(m) {
    voornaamInput.value = m.voornaam;
    achternaamInput.value = m.achternaam;
    emailInput.value = m.email;
    telefoonInput.value = m.telefoonnummer || '';
    afdelingSelect.value = m.afdeling;
    functieSelect.value = m.functie;
    statusSelect.value = m.status;
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
    const medewerkers = getMedewerkersObject();
    const medewerker = medewerkers[key];

    clearAllErrors();
    setStatus('', null);

    if (!medewerker) {
      bewerkVelden.disabled = true;
      rolVelden.disabled = true;
      submitBtn.disabled = true;
      form.reset();
      return;
    }

    vulVeldenIn(medewerker);
    bewerkVelden.disabled = false;
    rolVelden.disabled = false;
    submitBtn.disabled = false;
    voornaamInput.focus();
  });

  function validate() {
    let firstInvalid = null;
    let isValid = true;

    if (!zoekSelect.value) {
      setFieldError('zoek_medewerker', 'Kies eerst een medewerker om te bewerken.');
      return { isValid: false, firstInvalid: zoekSelect };
    }

    fieldDefs.forEach(({ name, label, required, type }) => {
      const input = form.elements[name];
      const value = (input.value || '').trim();
      let message = '';

      if (required && !value) {
        message = `${label} is verplicht.`;
      } else if (type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        message = 'Voer een geldig e-mailadres in.';
      }

      setFieldError(name, message);

      if (message) {
        isValid = false;
        if (!firstInvalid) firstInvalid = input;
      }
    });

    return { isValid, firstInvalid };
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

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    clearAllErrors();
    setStatus('', null);

    const { isValid, firstInvalid } = validate();

    if (!isValid) {
      setStatus('Controleer de gemarkeerde velden voordat je opslaat.', 'error');
      if (firstInvalid) firstInvalid.focus();
      return;
    }

    const key = zoekSelect.value;
    const list = getEmployees();
    
    // Zoek medewerker index
    const index = list.findIndex(m => m.personeelsnummer === key);
    if (index === -1) {
      setStatus('Fout: medewerker niet gevonden.', 'error');
      return;
    }

    list[index] = {
      personeelsnummer: key,
      voornaam: voornaamInput.value.trim(),
      achternaam: achternaamInput.value.trim(),
      email: emailInput.value.trim(),
      telefoonnummer: telefoonInput.value.trim(),
      afdeling: afdelingSelect.value,
      functie: functieSelect.value,
      status: statusSelect.value,
      geboortedatum: list[index].geboortedatum || null,
      datum_in_dienst: list[index].datum_in_dienst
    };

    saveEmployees(list);

    // Herlaad dropdown om in lijn te zijn met nieuwe naam of status
    populateDropdown();
    zoekSelect.value = key;

    showCurtainCall(
      `${voornaamInput.value.trim()} ${achternaamInput.value.trim()}`,
      `${functieSelect.value} — ${afdelingSelect.value}`
    );

    setStatus(
      `${voornaamInput.value.trim()} ${achternaamInput.value.trim()} is bijgewerkt.`,
      'success'
    );
  });

  form.addEventListener('reset', () => {
    clearAllErrors();
    setStatus('', null);
    bewerkVelden.disabled = true;
    rolVelden.disabled = true;
    submitBtn.disabled = true;
    zoekSelect.value = '';
  });

  form.addEventListener('input', (event) => {
    const { name } = event.target;
    if (!name) return;
    const fieldWrap = event.target.closest('.field');
    if (fieldWrap && fieldWrap.classList.contains('has-error')) {
      setFieldError(name, '');
    }
  });

  form.addEventListener('change', (event) => {
    const { name } = event.target;
    if (!name) return;
    const fieldWrap = event.target.closest('.field');
    if (fieldWrap && fieldWrap.classList.contains('has-error')) {
      setFieldError(name, '');
    }
  });

  // Initialisatie
  populateDropdown();

  // Check URL parameter select
  const urlParams = new URLSearchParams(window.location.search);
  const selectId = urlParams.get('select');
  if (selectId) {
    const medewerkers = getMedewerkersObject();
    if (medewerkers[selectId]) {
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
