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

  // Mock "medewerkers"-tabel — zou in een echte omgeving uit de database
  // worden geladen (tabel `medewerkers` uit database.sql).
  const medewerkers = {
    'PN-1001': {
      voornaam: 'Sanne', achternaam: 'de Vries', email: 's.devries@theater.nl',
      telefoonnummer: '0612345678', afdeling: 'Artistiek', functie: 'Regisseur', status: 'actief'
    },
    'PN-1002': {
      voornaam: 'Daan', achternaam: 'Bakker', email: 'd.bakker@theater.nl',
      telefoonnummer: '0623456789', afdeling: 'Techniek', functie: 'Lichttechnicus', status: 'actief'
    },
    'PN-1003': {
      voornaam: 'Layla', achternaam: 'El Amrani', email: 'l.elamrani@theater.nl',
      telefoonnummer: '0634567890', afdeling: 'Front of House', functie: 'Kassamedewerker', status: 'actief'
    }
  };

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

  zoekSelect.addEventListener('change', () => {
    const key = zoekSelect.value;
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

    // In productie: PUT/PATCH naar de backend, die een
    // UPDATE medewerkers SET ... WHERE personeelsnummer = ... uitvoert.
    medewerkers[key] = {
      voornaam: voornaamInput.value.trim(),
      achternaam: achternaamInput.value.trim(),
      email: emailInput.value.trim(),
      telefoonnummer: telefoonInput.value.trim(),
      afdeling: afdelingSelect.value,
      functie: functieSelect.value,
      status: statusSelect.value
    };

    // Houd het label in de zoekselector in lijn met de nieuwe naam.
    const optie = zoekSelect.querySelector(`option[value="${key}"]`);
    if (optie) optie.textContent = `${key} — ${voornaamInput.value.trim()} ${achternaamInput.value.trim()}`;

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
})();
