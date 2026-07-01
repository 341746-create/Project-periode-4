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

  // Mock "medewerkers"-tabel — zou in een echte omgeving uit de database
  // worden geladen (tabel `medewerkers` uit database.sql).
  let medewerkers = {
    'PN-1001': { voornaam: 'Sanne', achternaam: 'de Vries',  afdeling: 'Artistiek',      functie: 'Regisseur',       datum_in_dienst: '2021-09-01' },
    'PN-1002': { voornaam: 'Daan',  achternaam: 'Bakker',    afdeling: 'Techniek',       functie: 'Lichttechnicus',  datum_in_dienst: '2022-02-15' },
    'PN-1003': { voornaam: 'Layla', achternaam: 'El Amrani', afdeling: 'Front of House', functie: 'Kassamedewerker', datum_in_dienst: '2023-05-10' }
  };

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

  zoekSelect.addEventListener('change', () => {
    const key = zoekSelect.value;
    const medewerker = medewerkers[key];

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
    const medewerker = medewerkers[key];

    // In productie: DELETE FROM medewerkers WHERE personeelsnummer = ...
    delete medewerkers[key];

    const optie = zoekSelect.querySelector(`option[value="${key}"]`);
    if (optie) optie.remove();

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
})();
