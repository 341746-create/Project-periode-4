const validTickets = [
    "THR12345",
    "THR67890",
    "VIP2025",
    "SHOW001"
];

const scanBtn = document.getElementById("scanBtn");
const result = document.getElementById("result");

scanBtn.addEventListener("click", () => {
    const code = document.getElementById("ticketCode").value.trim();

    if (validTickets.includes(code)) {
        result.textContent = "✅ Ticket geldig - Toegang toegestaan";
        result.className = "success";
    } else {
        result.textContent = "❌ Ongeldig ticket";
        result.className = "error";
    }
});