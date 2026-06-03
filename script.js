const form = document.getElementById("ticketForm");
const melding = document.getElementById("melding");

const datumInput = document.getElementById("datumInput");
const calendar = document.getElementById("calendar");
const ticketsInput = document.getElementById("tickets");

let selectedDate = null;
let ticketCount = 1;

/* OPEN CALENDAR */
datumInput.addEventListener("click", (e) => {
    e.stopPropagation();
    calendar.style.display = "block";
    renderCalendar();
});

/* CLOSE OUTSIDE */
document.addEventListener("click", (e) => {
    if (!calendar.contains(e.target) && e.target !== datumInput) {
        calendar.style.display = "none";
    }
});

/* CALENDAR */
function renderCalendar() {
    calendar.innerHTML = "";

    const now = new Date();

    const months = [
        "Januari","Februari","Maart","April","Mei","Juni",
        "Juli","Augustus","September","Oktober","November","December"
    ];

    const header = document.createElement("div");
    header.className = "calendar-header";
    header.textContent = `${months[now.getMonth()]} ${now.getFullYear()}`;
    calendar.appendChild(header);

    const week = document.createElement("div");
    week.className = "weekdays";

    ["Ma","Di","Wo","Do","Vr","Za","Zo"].forEach(d => {
        const el = document.createElement("div");
        el.textContent = d;
        week.appendChild(el);
    });

    calendar.appendChild(week);

    const grid = document.createElement("div");
    grid.className = "calendar-grid";

    const today = new Date();

    for (let i = 0; i < 14; i++) {
        const date = new Date();
        date.setDate(today.getDate() + i);

        const day = document.createElement("div");
        day.className = "day";
        day.textContent = date.getDate();

        day.addEventListener("click", (e) => {
            e.stopPropagation();

            selectedDate = date.toISOString().split("T")[0];

            datumInput.value =
                `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;

            calendar.style.display = "none";
        });

        grid.appendChild(day);
    }

    calendar.appendChild(grid);
}

/* STEP SYSTEM */
const wrapper = ticketsInput.parentElement;

const stepper = document.createElement("div");
stepper.className = "stepper";

stepper.innerHTML = `
<button type="button" id="minus">−</button>
<span id="count">1</span>
<button type="button" id="plus">+</button>
`;

ticketsInput.style.display = "none";
wrapper.appendChild(stepper);

const countEl = document.getElementById("count");

function showError(msg){
    melding.style.display = "block";
    melding.className = "error";
    melding.textContent = msg;
}

document.getElementById("plus").addEventListener("click", () => {
    if (ticketCount >= 10) {
        showError("⚠️ Maximum 10 tickets per reservering.");
        return;
    }
    ticketCount++;
    countEl.textContent = ticketCount;
});

document.getElementById("minus").addEventListener("click", () => {
    if (ticketCount > 1) {
        ticketCount--;
        countEl.textContent = ticketCount;
    }
});

/* FORM */
form.addEventListener("submit", (e) => {
    e.preventDefault();

    const naam = document.getElementById("naam").value.trim();
    const email = document.getElementById("email").value.trim();
    const voorstelling = document.getElementById("voorstelling").value;

    /* UNHAPPY ENDING */
    if (!naam || !email || !voorstelling || !selectedDate) {
        melding.style.display = "block";
        melding.className = "error";
        melding.textContent = "👎 Onvolledige reservering. Vul alle velden in.";
        return;
    }

    /* HAPPY ENDING */
    melding.style.display = "block";
    melding.className = "success";
    melding.textContent =
        `🎉 Geslaagd! ${naam}, je hebt ${ticketCount} ticket(s) voor ${voorstelling} op ${selectedDate}.`;

    form.reset();
    ticketCount = 1;
    countEl.textContent = ticketCount;
    selectedDate = null;
});