<?php
// ============================================================
//  Aurora Theater — Ticket Overzicht
// ============================================================
require_once __DIR__ . '/../config/database.php';

$gebruiker_id = 2;
if (isset($_GET['gebruiker_id'])) {
    $gebruiker_id = (int)$_GET['gebruiker_id'];
} elseif (isset($_COOKIE['gebruiker_id'])) {
    $gebruiker_id = (int)$_COOKIE['gebruiker_id'];
}

$dbError = null;
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
    $dbError = $e->getMessage();
    $tickets = [
        ['id' => 1, 'voorstelling' => 'Modern Drama',  'datum' => '2026-07-10', 'aanvangstijd' => '20:00', 'zaal' => 'Grote Zaal',  'aantal_stoelen' => 1, 'totaalprijs' => '24.50', 'status' => 'bevestigd',      'betaalmethode' => 'ideal',  'aangemaakt_op' => '2026-06-01 10:00:00'],
        ['id' => 2, 'voorstelling' => 'Broadway Musical Night', 'datum' => '2026-07-11', 'aanvangstijd' => '19:30', 'zaal' => 'Main Stage', 'aantal_stoelen' => 2, 'totaalprijs' => '78.00', 'status' => 'in_behandeling', 'betaalmethode' => 'pin',   'aangemaakt_op' => '2026-06-02 11:00:00'],
        ['id' => 3, 'voorstelling' => 'Klassiek Shakespeare', 'datum' => '2026-07-12', 'aanvangstijd' => '20:00', 'zaal' => 'Kleine Zaal','aantal_stoelen' => 1, 'totaalprijs' => '19.50', 'status' => 'geannuleerd',    'betaalmethode' => null,     'aangemaakt_op' => '2026-06-03 09:30:00'],
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
    <meta name="description" content="Bekijk uw Aurora Theater tickets.">
    <title>Ticket Overzicht — Aurora Theater</title>
    <link rel="stylesheet" href="/style/header.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<header class="header" id="siteHeader">
    <nav class="nav container">
        <a href="/Homepaginamaken/index.php" class="logo">
            <i class="fa-solid fa-masks-theater"></i>
            Aurora Theater
        </a>

        <ul class="nav-links">
            <li><a href="#tickets"><i class="fa-solid fa-ticket"></i> Tickets</a></li>
        </ul>

        <div class="nav-actions">
            <a href="/BestaandeTicketWijzigen/index.php" class="btn btn--primary"><i class="fa-solid fa-pen-to-square"></i> Wijzigen</a>
            <a href="/ticket%20scannen/index.php" class="btn btn--ghost"><i class="fa-solid fa-qrcode"></i> Scanner</a>
        </div>
    </nav>
</header>

<main>
    <section id="tickets" class="section container">
        <h2 class="section-title">Mijn Tickets</h2>

        <?php if ($dbError): ?>
        <div class="notification error">
            <i class="fa-solid fa-triangle-exclamation"></i> Demo-data wordt getoond (database niet beschikbaar).
        </div>
        <?php endif; ?>

        <?php if (empty($tickets)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-ticket"></i>
            <p>U heeft nog geen tickets gereserveerd.</p>
            <a href="/NieuweTicketToevoegen/index.php">Reserveer nu <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <?php else: ?>
        <div class="grid">
            <?php foreach ($tickets as $ticket): ?>
            <?php 
            $ticket_code = sprintf('AUR-%d-%d', (int)$ticket['id'], $gebruiker_id);
            ?>
            <article class="ticket-card" data-id="<?= (int)$ticket['id'] ?>">
                <div class="ticket-header">
                    <span class="ticket-code"><?= htmlspecialchars($ticket_code) ?></span>
                    <span class="status <?= statusClass($ticket['status']) ?>"><?= statusLabel($ticket['status']) ?></span>
                </div>
                <h3><?= htmlspecialchars($ticket['voorstelling']) ?></h3>
                <p class="ticket-details">
                    <span><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($ticket['datum'])) ?></span>
                    <span><i class="fa-regular fa-clock"></i> <?= substr($ticket['aanvangstijd'], 0, 5) ?></span>
                    <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($ticket['zaal']) ?></span>
                </p>
                <div class="ticket-footer">
                    <span><?= (int)$ticket['aantal_stoelen'] ?> stoel(en)</span>
                    <strong style="color:#d4af37;">&euro;<?= number_format((float)$ticket['totaalprijs'], 2, ',', '.') ?></strong>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</main>

<footer class="footer">
    <h3>Aurora Theater</h3>
    <p>Waar emoties, kunst en verhalen samenkomen.</p>
    <p>&copy; <?= date('Y') ?> Aurora Theater</p>
</footer>

<script src="script.js"></script>
</body>
</html>