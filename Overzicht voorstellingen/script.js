// Laad de voorstellingen die momenteel aanwezig zijn uit de database (api.php)
function laadVoorstellingen() {
    const lijst = document.getElementById("voorstellingen-lijst");

    // EXTRA BEVEILIGING: Als het tabel-element ontbreekt, stop zonder te crashen
    if (!lijst) {
        console.error("Fout: HTML-element 'voorstellingen-lijst' niet gevonden! Controleer je HTML.");
        return;
    }

    fetch("api.php")
        .then(antwoord => antwoord.json())
        .then(data => {
            lijst.innerHTML = ""; // Maak de tabel leeg

            // UNHAPPY FLOW: lege lijst
            if (!Array.isArray(data) || data.length === 0) {
                const legeRij = document.createElement("tr");
                legeRij.innerHTML = `
                    <td colspan="5" style="text-align: center; color: #9ca3af; padding: 2.5rem;">
                        Er zijn momenteel geen voorstellingen gepland.
                    </td>
                `;
                lijst.appendChild(legeRij);
                return;
            }

            // HAPPY FLOW: toon de data uit de database
            data.forEach(voorstelling => {
                const rij = document.createElement("tr");

                rij.innerHTML = `
                    <td style="font-weight: 500;">${escapeHtml(voorstelling.titel)}</td>
                    <td style="color: #9ca3af;">${escapeHtml(voorstelling.datum)}</td>
                    <td style="color: #9ca3af;">${escapeHtml(voorstelling.tijd)}</td>
                    <td>${escapeHtml(voorstelling.zaal)}</td>
                    <td class="text-right">
                        <button class="btn-delete" data-id="${voorstelling.id}">Verwijderen</button>
                    </td>
                `;

                lijst.appendChild(rij);
            });

            // Koppel de verwijder-knoppen
            lijst.querySelectorAll(".btn-delete").forEach(knop => {
                knop.addEventListener("click", () => verwijderVoorstelling(knop.dataset.id));
            });
        })
        .catch(fout => {
            console.error(fout);
            lijst.innerHTML = `
                <td colspan="5" style="text-align: center; color: #9ca3af; padding: 2.5rem;">
                    Kon de voorstellingen niet laden.
                </td>
            `;
        });
}

// Verwijder een voorstelling via de API
function verwijderVoorstelling(id) {
    if (!confirm("Weet je zeker dat je deze voorstelling wilt verwijderen?")) {
        return;
    }

    const form = new FormData();
    form.append("verwijder_id", id);

    fetch("api.php", { method: "POST", body: form })
        .then(antwoord => antwoord.json())
        .then(() => laadVoorstellingen()) // Ververs direct het scherm
        .catch(fout => console.error(fout));
}

// Voorkom XSS bij het tonen van database-waarden
function escapeHtml(waarde) {
    const div = document.createElement("div");
    div.textContent = waarde ?? "";
    return div.innerHTML;
}

// Start pas als de volledige pagina (DOM) geladen is
document.addEventListener("DOMContentLoaded", laadVoorstellingen);

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
