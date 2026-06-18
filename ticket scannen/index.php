<?php
// ============================================================
//  Aurora Theater — Ticket Scanner
// ============================================================
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Scan Aurora Theater tickets via de barcode scanner.">
    <title>Ticket Scanner — Aurora Theater</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="scanner-container">
        <h1>🎭 Aurora Theater — Ticket Scanner</h1>

        <p class="scanner-subtitle">Voer een ticketcode in of scan een QR-code</p>

        <div class="input-group">
            <input
                type="text"
                id="ticketCode"
                placeholder="bijv. THR12345 of AUR-2026-001"
                autocomplete="off"
                autofocus
            >
            <button id="scanBtn">
                <span>&#128269;</span> Scan Ticket
            </button>
        </div>

        <div id="result"></div>

        <div class="scanner-history">
            <h3>Recente Scans</h3>
            <ul id="historyList">
                <li class="history-empty">Nog geen scans uitgevoerd.</li>
            </ul>
        </div>

        <div class="scanner-footer">
            <a href="../index.php">&#8592; Terug naar Home</a>
            &bull;
            <a href="../ticket overzicht/index.php">Ticket Overzicht</a>
        </div>
    </div>

    <script>
        const scanBtn      = document.getElementById('scanBtn');
        const ticketInput  = document.getElementById('ticketCode');
        const resultDiv    = document.getElementById('result');
        const historyList  = document.getElementById('historyList');
        let scanHistory    = [];

        async function scanTicket() {
            const code = ticketInput.value.trim();
            if (!code) {
                showResult('Voer een ticketcode in.', 'warning');
                return;
            }

            scanBtn.disabled = true;
            scanBtn.textContent = 'Bezig...';

            try {
                const response = await fetch('api/scan_ticket.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code }),
                });
                const data = await response.json();

                if (data.success) {
                    showResult(
                        `✅ <strong>Geldig ticket!</strong><br>
                         Voorstelling: ${data.ticket.voorstelling}<br>
                         Datum: ${data.ticket.datum}<br>
                         Zaal: ${data.ticket.zaal}<br>
                         Status: ${data.ticket.status}`,
                        'success'
                    );
                    addToHistory(code, true);
                } else {
                    showResult(`❌ <strong>Ongeldig ticket</strong><br>${data.error}`, 'error');
                    addToHistory(code, false);
                }
            } catch (err) {
                showResult('⚠️ Verbindingsfout — controleer de server.', 'warning');
            } finally {
                scanBtn.disabled = false;
                scanBtn.innerHTML = '<span>🔍</span> Scan Ticket';
                ticketInput.value = '';
                ticketInput.focus();
            }
        }

        function showResult(html, type) {
            resultDiv.innerHTML = html;
            resultDiv.className = type;
            resultDiv.style.display = 'block';
        }

        function addToHistory(code, valid) {
            const time = new Date().toLocaleTimeString('nl-NL');
            scanHistory.unshift({ code, valid, time });
            if (scanHistory.length > 10) scanHistory.pop();

            historyList.innerHTML = scanHistory.map(s =>
                `<li class="${s.valid ? 'hist-valid' : 'hist-invalid'}">
                    <span>${s.valid ? '✅' : '❌'} ${s.code}</span>
                    <small>${s.time}</small>
                </li>`
            ).join('');
        }

        scanBtn.addEventListener('click', scanTicket);
        ticketInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') scanTicket();
        });
    </script>
</body>
</html>
