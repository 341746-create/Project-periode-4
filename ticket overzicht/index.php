<?php
// ============================================================
//  Aurora Theater — Ticket Overzicht
// ============================================================
require_once __DIR__ . '/../config/database.php';

$gebruiker_id = 2;

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<header class="header">
    <div class="header-inner">
        <a href="../index.php" class="logo">
            <i class="fa-solid fa-masks-theater"></i>
            Aurora Theater
        </a>
        <nav>
            <a href="../nieuwe_ticket/index.php"><i class="fa-solid fa-plus"></i> Nieuw Ticket</a>
            <a href="../ticket_scannen/index.php"><i class="fa-solid fa-qrcode"></i> Scanner</a>
            <a href="../index.php"><i class="fa-solid fa-house"></i> Home</a>
        </nav>
    </div>
</header>

<main>
    <div class="container-main">
        <?php if ($error): ?>
        <div class="notification error" style="display:block;position:relative;top:auto;right:auto;margin-bottom:1.5rem;">
            <i class="fa-solid fa-triangle-exclamation"></i> Database niet verbonden — demo-data wordt getoond.
        </div>
        <?php endif; ?>

        <section class="tickets-container">
            <h2><i class="fa-solid fa-ticket"></i> 🎭 Uw Tickets</h2>

            <?php if (empty($tickets)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-ticket"></i>
                <p>U heeft nog geen tickets gereserveerd.</p>
                <a href="../nieuwe_ticket/index.php">Reserveer nu <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ticketcode</th>
                        <th>Voorstelling</th>
                        <th>Datum & Tijd</th>
                        <th>Zaal</th>
                        <th>Stoelen</th>
                        <th>Prijs</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <?php 
                    $ticket_code = sprintf('AUR-%d-%d', (int)$ticket['id'], $gebruiker_id);
                    ?>
                    <tr id="row-<?= (int)$ticket['id'] ?>">
                        <td><?= (int)$ticket['id'] ?></td>
                        <td><code class="ticket-code"><?= htmlspecialchars($ticket_code) ?></code></td>
                        <td><strong><?= htmlspecialchars($ticket['voorstelling']) ?></strong></td>
                        <td>
                            <?= date('d M Y', strtotime($ticket['datum'])) ?>
                            <small><?= substr($ticket['aanvangstijd'], 0, 5) ?></small>
                        </td>
                        <td><?= htmlspecialchars($ticket['zaal']) ?></td>
                        <td><?= (int)$ticket['aantal_stoelen'] ?></td>
                        <td><strong style="color:#d4af37;">&euro;<?= number_format((float)$ticket['totaalprijs'], 2, ',', '.') ?></strong></td>
                        <td>
                            <span class="status <?= statusClass($ticket['status']) ?>">
                                <?= statusLabel($ticket['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($ticket['status'] !== 'geannuleerd'): ?>
                            <button class="edit" onclick="openEditModal(<?= (int)$ticket['id'] ?>)">
                                <i class="fa-solid fa-pen"></i> Bewerk
                            </button>
                            <button class="cancel" onclick="openCancelModal(<?= (int)$ticket['id'] ?>)">
                                <i class="fa-solid fa-xmark"></i> Annuleer
                            </button>
                            <?php else: ?>
                            <span style="color:#555;font-size:.85rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- Modal: Bewerken -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editModal')">&times;</span>
        <h3><i class="fa-solid fa-pen"></i> Ticket Bewerken</h3>
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
            <button type="submit"><i class="fa-solid fa-check"></i> Opslaan</button>
        </form>
    </div>
</div>

<!-- Modal: Annuleren -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('cancelModal')">&times;</span>
        <h3><i class="fa-solid fa-triangle-exclamation"></i> Annuleer Ticket</h3>
        <p style="color:#aaa;margin-bottom:20px;">Weet je zeker dat je dit ticket wilt annuleren?</p>
        <input type="hidden" id="cancelTicketId">
        <button id="confirmCancel" class="cancel" style="padding:12px 24px;">
            <i class="fa-solid fa-xmark"></i> Ja, annuleer
        </button>
        <button type="button" onclick="closeModal('cancelModal')" style="background:rgba(255,255,255,0.08);color:#fff;padding:10px 20px;margin-top:8px;">
            Nee
        </button>
    </div>
</div>

<div id="notification" class="notification"></div>

<footer>
    <strong>Aurora Theater</strong> &copy; <?= date('Y') ?>
</footer>

<script src="script.js"></script>
</body>
</html>
