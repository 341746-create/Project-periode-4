let tickets = [
    { voorstelling: "Hamlet", datum: "2026-07-10", rij: "A", stoel: "12", status: "Bevestigd" },
    { voorstelling: "Macbeth", datum: "2026-07-11", rij: "B", stoel: "7", status: "In afwachting" },
    { voorstelling: "Othello", datum: "2026-07-12", rij: "C", stoel: "4", status: "Geannuleerd" }
];

const tableBody = document.querySelector("#ticket-table tbody");
let currentEditIndex = null;
let currentCancelIndex = null;

// Tickets weergeven
function renderTickets() {
    tableBody.innerHTML = "";
    tickets.forEach((ticket, index) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${ticket.voorstelling}</td>
            <td>${ticket.datum}</td>
            <td>${ticket.rij}</td>
            <td>${ticket.stoel}</td>
            <td class="status ${getStatusClass(ticket.status)}">${ticket.status}</td>
            <td>
                <button class="edit" onclick="openEditModal(${index})">Bewerk</button>
                <button class="cancel" onclick="openCancelModal(${index})">Annuleer</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });
}

// Status classes
function getStatusClass(status) {
    if(status.toLowerCase() === "bevestigd") return "status-confirmed";
    if(status.toLowerCase() === "in afwachting") return "status-pending";
    if(status.toLowerCase() === "geannuleerd") return "status-cancelled";
    return "";
}

// Open modals
function openEditModal(index) {
    currentEditIndex = index;
    document.getElementById("editRij").value = tickets[index].rij;
    document.getElementById("editStoel").value = tickets[index].stoel;
    document.getElementById("editModal").style.display = "block";
}

function openCancelModal(index) {
    currentCancelIndex = index;
    document.getElementById("cancelModal").style.display = "block";
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

// Bewerken
document.getElementById("editForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const rij = document.getElementById("editRij").value.trim();
    const stoel = document.getElementById("editStoel").value.trim();
    if(rij && stoel) {
        tickets[currentEditIndex].rij = rij;
        tickets[currentEditIndex].stoel = stoel;
        renderTickets();
        showNotification("Ticket succesvol bijgewerkt!", true);
        closeModal('editModal');
    } else {
        showNotification("Ticketbewerking geannuleerd.", false);
    }
});

// Annul