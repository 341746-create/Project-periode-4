// DOM Elementen selecteren
const form = document.getElementById('addEmployeeForm');
const tableBody = document.getElementById('employeeTableBody');
const searchInput = document.getElementById('searchEmployee');
const countSpan = document.getElementById('employeeCount');

// Status indicators voor de scenario's
const errorMessage = document.getElementById('errorMessage');
const successMessage = document.getElementById('successMessage');
const emailInput = document.getElementById('email');

// Functie: Update de teller van actieve medewerkers
function updateCount() {
    const rows = tableBody.querySelectorAll('.employee-row').length;
    countSpan.textContent = rows;
}

// Functie: Medewerker uit de lijst verwijderen (Blokkeren)
function deleteEmployee(button) {
    if (confirm("Weet je zeker dat je deze medewerker wilt blokkeren/verwijderen?")) {
        const row = button.closest('tr');
        row.remove();
        updateCount();
    }
}

// Luisteren naar actie-knoppen binnen de tabel (Bewerken / Blokkeren)
tableBody.addEventListener('click', function(e) {
    if (e.target.classList.contains('delete-btn')) {
        deleteEmployee(e.target);
    }
    if (e.target.classList.contains('edit-btn')) {
        alert('Bewerken-functionaliteit vereist op dit moment een back-end databasekoppeling.');
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
    // Nieuwe rij genereren voor de medewerkerstabel
    const newRow = document.createElement('tr');
    newRow.className = "transition custom-row-hover employee-row";
    newRow.innerHTML = `
        <td class="py-3 px-4 font-medium text-white employee-name">${firstName} ${lastName}</td>
        <td class="py-3 px-4 text-gray-300">${email}</td>
        <td class="py-3 px-4">
            <span class="bg-blue-600/10 text-blue-400 border border-blue-500/20 text-xs px-2.5 py-0.5 rounded-full font-medium">Medewerker</span>
        </td>
        <td class="py-3 px-4 text-right space-x-2">
            <button class="text-gray-400 hover:text-white transition text-xs font-medium cursor-pointer edit-btn">Bewerken</button>
            <button class="text-red-400 hover:text-red-500 transition text-xs font-medium cursor-pointer delete-btn">Blokkeer</button>
        </td>
    `;

    // Rij in de tabel schieten, formulier leegmaken en teller updaten
    tableBody.appendChild(newRow);
    form.reset();
    updateCount();

    // Toon de groene succesmelding en laat hem na 4 seconden weer verdwijnen
    successMessage.classList.remove('hidden');
    setTimeout(() => {
        successMessage.classList.add('hidden');
    }, 4000);
});

// Live filteren/zoeken in de tabel op basis van ingetypte naam
searchInput.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = tableBody.querySelectorAll('.employee-row');

    rows.forEach(row => {
        const name = row.querySelector('.employee-name').textContent.toLowerCase();
        if (name.includes(searchTerm)) {
            row.style.display = ""; // Toon rij
        } else {
            row.style.display = "none"; // Verberg rij
        }
    });
});