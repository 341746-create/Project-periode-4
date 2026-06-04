let tickets = [
    { code: "THR12345", naam: "Jan Jansen" },
    { code: "VIP2025", naam: "Piet de Vries" }
];

function showMessage(text, type) {
    const message = document.getElementById("message");
    message.textContent = text;
    message.className = type;

    setTimeout(() => {
        message.textContent = "";
    }, 3000);
}

function renderTickets() {
    const table = document.getElementById("ticketTable");
    table.innerHTML = "";

    tickets.forEach((ticket, index) => {
        table.innerHTML += `
            <tr>
                <td>${ticket.code}</td>
                <td>${ticket.naam}</td>
                <td>
                    <button class="delete-btn" onclick="deleteTicket(${index})">
                        Verwijderen
                    </button>
                </td>
            </tr>
        `;
    });
}

function addTicket() {
    const code = document.getElementById("ticketCode").value.trim();
    const naam = document.getElementById("ticketNaam").value.trim();

    // ❌ Unhappy scenario: lege velden
    if (!code || !naam) {
        showMessage("Vul alle velden in.", "error");
        return;
    }

    // ❌ Unhappy scenario: dubbele code
    const exists = tickets.some(t => t.code === code);
    if (exists) {
        showMessage("Ticketcode bestaat al.", "error");
        return;
    }

    // ✅ Happy scenario
    tickets.push({ code, naam });

    document.getElementById("ticketCode").value = "";
    document.getElementById("ticketNaam").value = "";

    renderTickets();
    showMessage("Ticket succesvol toegevoegd!", "success");
}

function deleteTicket(index) {
    if (tickets[index]) {
        tickets.splice(index, 1);
        renderTickets();
        showMessage("Ticket verwijderd.", "success");
    } else {
        showMessage("Ticket niet gevonden.", "error");
    }
}

renderTickets();