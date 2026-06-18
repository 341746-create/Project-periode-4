<?php
// ============================================================
//  Aurora Theater — Ticket Overzicht
// ============================================================
require_once __DIR__ . '/../config/database.php';

// Sessie: huidig ingelogd gebruiker (demo: gebruiker ID 2)
$gebruiker_id = 2; // In productie: uit sessie halen

// Haal tickets op uit de database
$error = null;
try {
    $stmt = db()->prepare("
        SELECT
            r.id,
            v.titel AS voorstelling,
            v.datum,
            v.aanvangstijd,
            z.naam AS zaal,
            r.aantal_stoelen,
            r.totaalprijs,
            r.status,
            r.betaalmethode,
            r.aangemaakt_op
        FROM reserveringen r
        JOIN voorstellingen v ON v.id = r.voorstelling_id
        JOIN zalen z ON z.id = v.zaal_id
        WHERE r.gebruiker_id = :uid
        ORDER BY r.aangemaakt_op DESC
    ");
    $stmt->execute([':uid' => $gebruiker_id]);
    $tickets = $stmt->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    // Fallback demo-data
    $tickets = [
        ['id' => 1, 'voorstelling' => 'Hamlet',  'datum' => '2026-07-10', 'aanvangstijd' => '20:00', 'zaal' => 'Grote Zaal',  'aantal_stoelen' => 1, 'totaalprijs' => '24.50', 'status' => 'bevestigd',      'betaalmethode' => 'ideal',  'aangemaakt_op' => '2026-06-01 10:00:00'],
        ['id' => 2, 'voorstelling' => 'Macbeth', 'datum' => '2026-07-11', 'aanvangstijd' => '19:30', 'zaal' => 'Main Stage', 'aantal_stoelen' => 2, 'totaalprijs' => '78.00', 'status' => 'in_behandeling', 'betaalmethode' => 'pin',   'aangemaakt_op' => '2026-06-02 11:00:00'],
        ['id' => 3, 'voorstelling' => 'Othello', 'datum' => '2026-07-12', 'aanvangstijd' => '20:00', 'zaal' => 'Kleine Zaal','aantal_stoelen' => 1, 'totaalprijs' => '19.50', 'status' => 'geannuleerd',    'betaalmethode' => null,     'aangemaakt_op' => '2026-06-03 09:30:00'],
    ];
}

function statusLabel(string $status): string {
    return match($status) {
        'bevestigd'      => 'Bevestigd',
        'in_behandeling' => 'In behandeling',
        'geannuleerd'    => 'Geannuleerd',
        'voltooid'       => 'Voltooid',
        default          => ucfirst($status),
    };
}
function statusClass(string $status): string {
    return match($status) {
        'bevestigd'      => 'status-confirmed',
        'in_behandeling' => 'status-pending',
        'geannuleerd'    => 'status-cancelled',
        'voltooid'       => 'status-confirmed',
        default          => '',
    };
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bekijk en beheer uw Aurora Theater tickets.">
    <title>Ticket Overzicht — Aurora Theater</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <h1>&#127914; Ticket Overzicht</h1>
        <nav>
            <a href="../index.php">&#8592; Home</a>
            <a href="../NieuweTicketToevoegen/index.php">&#43; Nieuw Ticket</a>
            <a href="../ticket scannen/index.php">&#128269; Scanner</a>
        </nav>
    </header>

    <main>
        <?php if ($error): ?>
        <div class="notification error" style="display:block;position:relative;top:auto;right:auto;margin-bottom:1rem;">
            ⚠️ Database niet verbonden — demo-data wordt getoond.
        </div>
        <?php endif; ?>

        <section class="tickets-container">
            <h2>Uw Tickets</h2>

            <?php if (empty($tickets)): ?>
                <p style="color:#888;text-align:center;padding:2rem;">
                    U heeft nog geen tickets gereserveerd.
                    <a href="../NieuweTicketToevoegen/index.php">Reserveer nu &rarr;</a>
                </p>
            <?php else: ?>
            <table id="ticket-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Voorstelling</th>
                        <th>Datum</th>
                        <th>Zaal</th>
                        <th>Stoelen</th>
                        <th>Prijs</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr id="row-<?= (int)$ticket['id'] ?>">
                        <td><?= (int)$ticket['id'] ?></td>
                        <td><?= htmlspecialchars($ticket['voorstelling']) ?></td>
                        <td>
                            <?= date('d M Y', strtotime($ticket['datum'])) ?>
                            <small><?= substr($ticket['aanvangstijd'], 0, 5) ?></small>
                        </td>
                        <td><?= htmlspecialchars($ticket['zaal']) ?></td>
                        <td><?= (int)$ticket['aantal_stoelen'] ?></td>
                        <td>&euro;<?= number_format((float)$ticket['totaalprijs'], 2, ',', '.') ?></td>
                        <td class="status <?= statusClass($ticket['status']) ?>">
                            <?= statusLabel($ticket['status']) ?>
                        </td>
                        <td>
                            <?php if ($ticket['status'] !== 'geannuleerd'): ?>
                            <button class="edit"   onclick="openEditModal(<?= (int)$ticket['id'] ?>)">Bewerk</button>
                            <button class="cancel" onclick="openCancelModal(<?= (int)$ticket['id'] ?>)">Annuleer</button>
                            <?php else: ?>
                            <span style="color:#999;font-size:.85rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
    </main>

    <!-- Modal: Bewerken -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h3>Ticket Bewerken</h3>
            <form id="editForm">
                <input type="hidden" id="editTicketId">
                <label for="editStoelen">Aantal stoelen:</label>
                <input type="number" id="editStoelen" min="1" max="10" required>
                <label for="editBetaalmethode">Betaalmethode:</label>
                <select id="editBetaalmethode">
                    <option value="ideal">iDEAL</option>
                    <option value="creditcard">Creditcard</option>
                    <option value="pin">Pin</option>
                    <option value="contant">Contant</option>
                </select>
                <button type="submit">Opslaan</button>
            </form>
        </div>
    </div>

    <!-- Modal: Annuleren -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('cancelModal')">&times;</span>
            <h3>Annuleer Ticket</h3>
            <p>Weet je zeker dat je dit ticket wilt annuleren?</p>
            <input type="hidden" id="cancelTicketId">
            <button id="confirmCancel">Ja, annuleer</button>
            <button onclick="closeModal('cancelModal')">Nee</button>
        </div>
    </div>

    <div id="notification" class="notification"></div>

    <footer>&copy; <?= date('Y') ?> Aurora Theater</footer>

    <script src="script.js"></script>
</body>
</html>
