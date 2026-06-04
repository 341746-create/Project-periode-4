// Testdata van de voorstellingen
const voorstellingen = [
    { id: 1, titel: "The Phantom of the Opera", datum: "12-06-2026", tijd: "20:00", zaal: "Grote Zaal" },
    { id: 2, titel: "Het Zwanenmeer", datum: "15-06-2026", tijd: "19:30", zaal: "Rode Zaal" },
    { id: 3, titel: "Soldaat van Oranje", datum: "18-06-2026", tijd: "14:00", zaal: "Theaterzaal 1" },
    { id: 4, titel: "De Klucht van de Molenaar", datum: "22-06-2026", tijd: "20:15", zaal: "Kleine Zaal" }
];

// Functie om de tabel op te bouwen
function laadVoorstellingen() {
    const lijst = document.getElementById("voorstellingen-lijst");
    
    // EXTRA BEVEILIGING: Als JS de tabel niet kan vinden, stopt hij hier zonder te crashen
    if (!lijst) {
        console.error("Fout: HTML-element 'voorstellingen-lijst' niet gevonden! Controleer je HTML.");
        return;
    }

    lijst.innerHTML = ""; // Maak de tabel leeg

    // UNHAPPY FLOW: Wat als de lijst leeg is?
    if (voorstellingen.length === 0) {
        const legeRij = document.createElement("tr");
        legeRij.innerHTML = `
            <td colspan="5" style="text-align: center; color: #9ca3af; padding: 2.5rem;">
                Er zijn momenteel geen voorstellingen gepland.
            </td>
        `;
        lijst.appendChild(legeRij);
        return; 
    }

    // HAPPY FLOW: Toon de data
    voorstellingen.forEach(voorstelling => {
        const rij = document.createElement("tr");

        rij.innerHTML = `
            <td style="font-weight: 500;">${voorstelling.titel}</td>
            <td style="color: #9ca3af;">${voorstelling.datum}</td>
            <td style="color: #9ca3af;">${voorstelling.tijd}</td>
            <td>${voorstelling.zaal}</td>
            <td class="text-right">
                <button class="btn-delete" onclick="verwijderVoorstelling(${voorstelling.id})">Verwijderen</button>
            </td>
        `;

        lijst.appendChild(rij);
    });
}

// Functie om een voorstelling te verwijderen
function verwijderVoorstelling(id) {
    const index = voorstellingen.findIndex(v => v.id === id);

    if (index !== -1) {
        if (confirm(`Weet je zeker dat je "${voorstellingen[index].titel}" wilt verwijderen?`)) {
            voorstellingen.splice(index, 1);
            laadVoorstellingen(); // Update direct het scherm
        }
    } else {
        alert("Fout: Deze voorstelling kon niet worden gevonden.");
    }
}

// Zorg dat de code pas start áls de volledige pagina (DOM) is geladen door de browser
document.addEventListener("DOMContentLoaded", laadVoorstellingen);