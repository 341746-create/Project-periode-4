// DOM Elementen selecteren
const form = document.getElementById('addEmployeeForm');
const tableBody = document.getElementById('employeeTableBody');
const searchInput = document.getElementById('searchEmployee');
const countSpan = document.getElementById('employeeCount');

// Status indicators voor de scenario's
const errorMessage = document.getElementById('errorMessage');
const successMessage = document.getElementById('successMessage');
const emailInput = document.getElementById('email');

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

// Functie: Update de teller van actieve medewerkers
function updateCount() {
    const rows = tableBody.querySelectorAll('.employee-row').length;
    countSpan.textContent = rows;
}

// Functie: Render de medewerkers uit localStorage
function renderEmployees() {
    const employees = getEmployees();
    tableBody.innerHTML = '';
    
    const searchTerm = searchInput.value.toLowerCase().trim();
    
    employees.forEach(m => {
        // Alleen actieve medewerkers tonen in de hoofdlijst van "Actieve Medewerkers"
        if (m.status !== 'actief') {
            return;
        }

        const fullName = `${m.voornaam} ${m.achternaam}`;
        if (searchTerm && !fullName.toLowerCase().includes(searchTerm) && !m.email.toLowerCase().includes(searchTerm)) {
            return;
        }
        
        const newRow = document.createElement('tr');
        newRow.className = "transition custom-row-hover employee-row";
        newRow.innerHTML = `
            <td class="py-3 px-4 font-medium text-white employee-name">${fullName}</td>
            <td class="py-3 px-4 text-gray-300">${m.email}</td>
            <td class="py-3 px-4">
                <span class="bg-blue-600/10 text-blue-400 border border-blue-500/20 text-xs px-2.5 py-0.5 rounded-full font-medium">Medewerker</span>
            </td>
            <td class="py-3 px-4 text-right space-x-2">
                <button class="text-gray-400 hover:text-white transition text-xs font-medium cursor-pointer edit-btn" data-id="${m.personeelsnummer}">Bewerken</button>
                <button class="text-red-400 hover:text-red-500 transition text-xs font-medium cursor-pointer delete-btn" data-id="${m.personeelsnummer}">Blokkeer</button>
            </td>
        `;
        tableBody.appendChild(newRow);
    });
    updateCount();
}

// Luisteren naar actie-knoppen binnen de tabel (Bewerken / Blokkeer)
tableBody.addEventListener('click', function(e) {
    if (e.target.classList.contains('delete-btn')) {
        const id = e.target.getAttribute('data-id');
        window.location.href = `/medewerker-verwijderen/index.php?select=${id}`;
    }
    if (e.target.classList.contains('edit-btn')) {
        const id = e.target.getAttribute('data-id');
        window.location.href = `/medewerker-wijzigen/index.php?select=${id}`;
    }
});

// Luisteren naar het insturen van het formulier (Happy & Unhappy Paths)
form.addEventListener('submit', function(e) {
    e.preventDefault(); // Voorkom dat de pagina herlaadt

    // Eerst alle eerdere meldingen en rode borders resetten/verbergen
    errorMessage.classList.add('hidden');
    successMessage.classList.add('hidden');
    emailInput.classList.remove('border-red-500', 'focus:border-red-500');

    // Invoerwaardes ophalen en schoonmaken
    const firstName = document.getElementById('firstName').value.trim();
    const lastName = document.getElementById('lastName').value.trim();
    const email = emailInput.value.trim().toLowerCase();

    // ❌ UNHAPPY PATH SCENARIO: E-mailadres eindigt niet op het theaterdomein
    if (!email.endsWith('@auroratheater.nl')) {
        // Toon de rode foutmelding en geef de input een rode rand
        errorMessage.classList.remove('hidden');
        emailInput.classList.add('border-red-500', 'focus:border-red-500');
        return; // Stop de functie direct; medewerker wordt NIET toegevoegd
    }

    // ✅ HAPPY PATH SCENARIO: Alles klopt
    const list = getEmployees();
    
    // Genereer het volgende PN-nummer
    let maxNum = 1005;
    list.forEach(m => {
        const match = m.personeelsnummer.match(/^PN-(\d+)$/i);
        if (match) {
            const num = parseInt(match[1]);
            if (num > maxNum) maxNum = num;
        }
    });
    const newPn = `PN-${maxNum + 1}`;

    const newEmp = {
        personeelsnummer: newPn,
        voornaam: firstName,
        achternaam: lastName,
        email: email,
        afdeling: 'Artistiek', // Standaard afdeling voor nieuwe medewerker
        functie: 'Regisseur',  // Standaard functie voor nieuwe medewerker
        datum_in_dienst: new Date().toISOString().split('T')[0],
        status: 'actief',
        telefoonnummer: ''
    };
    
    list.push(newEmp);
    saveEmployees(list);

    // Formulier leegmaken en lijst herrenderen
    form.reset();
    renderEmployees();

    // Toon de groene succesmelding en laat hem na 4 seconden weer verdwijnen
    successMessage.classList.remove('hidden');
    setTimeout(() => {
        successMessage.classList.add('hidden');
    }, 4000);
});

// Live filteren/zoeken in de tabel op basis van ingetypte naam
searchInput.addEventListener('input', renderEmployees);

// Eerste render bij het laden van de pagina
renderEmployees();
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
