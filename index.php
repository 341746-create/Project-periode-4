<?php
// ============================================================
//  Aurora Theater — Bestaande Ticket Annuleren
// ============================================================

$gebruiker_id = 2;
$dbError = null;
$tickets = [];
$changelog = [];

$demoTickets = [
    ['id' => 1, 'voorstelling' => 'The Phantom of the Opera', 'datum' => '2026-07-10', 'aanvangstijd' => '20:00', 'zaal' => 'Grote Zaal', 'aantal_stoelen' => 2, 'totaalprijs' => '49.00', 'status' => 'bevestigd', 'betaalmethode' => 'ideal', 'aangemaakt_op' => '2026-06-01 10:00:00', 'beschikbare_stoelen' => 480, 'prijs_per_stoel' => '24.50'],
    ['id' => 2, 'voorstelling' => 'Soldaat van Oranje', 'datum' => '2026-07-15', 'aanvangstijd' => '19:30', 'zaal' => 'Theaterzaal 2', 'aantal_stoelen' => 3, 'totaalprijs' => '117.00', 'status' => 'in_behandeling', 'betaalmethode' => 'pin', 'aangemaakt_op' => '2026-06-05 14:30:00', 'beschikbare_stoelen' => 250, 'prijs_per_stoel' => '39.00'],
    ['id' => 3, 'voorstelling' => 'Het Zwanenmeer (Ballet)', 'datum' => '2026-07-20', 'aanvangstijd' => '14:00', 'zaal' => 'Blauwe Zaal', 'aantal_stoelen' => 1, 'totaalprijs' => '32.00', 'status' => 'geannuleerd', 'betaalmethode' => 'creditcard', 'aangemaakt_op' => '2026-06-08 09:00:00', 'beschikbare_stoelen' => 120, 'prijs_per_stoel' => '32.00'],
];

$demoChangelog = [
    ['datum' => '2026-06-25 09:10:00', 'ticket_code' => 'AUR-3-2', 'voorstelling' => 'Het Zwanenmeer (Ballet)', 'type' => 'geannuleerd', 'detail' => 'Reservering geannuleerd door gebruiker'],
    ['datum' => '2026-06-20 10:00:00', 'ticket_code' => 'AUR-1-2', 'voorstelling' => 'The Phantom of the Opera', 'type' => 'aangemaakt', 'detail' => '2 stoelen gereserveerd | €49,00'],
    ['datum' => '2026-06-18 14:30:00', 'ticket_code' => 'AUR-2-2', 'voorstelling' => 'Soldaat van Oranje', 'type' => 'aangemaakt', 'detail' => '3 stoelen gereserveerd | €117,00'],
];

try {
    require_once __DIR__ . '/config/database.php';
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT
            r.id,
            v.titel        AS voorstelling,
            v.datum,
            v.aanvangstijd,
            z.naam         AS zaal,
            r.aantal_stoelen,
            r.totaalprijs,
            r.status,
            r.betaalmethode,
            r.aangemaakt_op,
            v.beschikbare_stoelen,
            v.prijs_per_stoel
        FROM reserveringen r
        JOIN voorstellingen v ON v.id = r.voorstelling_id
        JOIN zalen z ON z.id = v.zaal_id
        WHERE r.gebruiker_id = :uid
        ORDER BY r.aangemaakt_op DESC
    ");
    $stmt->execute([':uid' => $gebruiker_id]);
    $tickets = $stmt->fetchAll();

    try {
        $logStmt = $pdo->prepare("
            SELECT
                wl.gewijzigd_op AS datum,
                CONCAT('AUR-', r.id, '-', r.gebruiker_id) AS ticket_code,
                v.titel AS voorstelling,
                wl.type,
                wl.detail
            FROM wijzigingen_log wl
            JOIN reserveringen r ON r.id = wl.reservering_id
            JOIN voorstellingen v ON v.id = r.voorstelling_id
            WHERE r.gebruiker_id = :uid
            ORDER BY wl.gewijzigd_op DESC
            LIMIT 20
        ");
        $logStmt->execute([':uid' => $gebruiker_id]);
        $changelog = $logStmt->fetchAll();
    } catch (Exception $e) {
        $changelog = $demoChangelog;
    }

} catch (Exception $e) {
    $dbError = $e->getMessage();
    $tickets = $demoTickets;
    $changelog = $demoChangelog;
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
function changeTypeClass(string $type): string {
    return match($type) {
        'geannuleerd' => 'change-type--cancel',
        'aangemaakt'  => 'change-type--create',
        default       => 'change-type--cancel',
    };
}
function changeTypeIcon(string $type): string {
    return match($type) {
        'geannuleerd' => 'fa-xmark',
        'aangemaakt'  => 'fa-plus',
        default       => 'fa-circle-info',
    };
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Annuleer uw bestaande Aurora Theater reserveringen eenvoudig online.">
    <title>Bestaande Ticket Annuleren — Aurora Theater</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<header class="header" id="siteHeader">
    <nav class="nav container">
        <div class="logo">
            <i class="fa-solid fa-masks-theater"></i>
            Aurora Theater
        </div>

        <ul class="nav-links">
            <li><a href="#tickets"><i class="fa-solid fa-ticket"></i> Tickets</a></li>
            <li><a href="#overzicht"><i class="fa-solid fa-clock-rotate-left"></i> Annuleringen</a></li>
        </ul>

        <div class="nav-actions">
            <a href="/Homepaginamaken/index.php" class="btn btn--ghost"><i class="fa-solid fa-house"></i> Home</a>
            <a href="#tickets" class="btn btn--ghost"><i class="fa-solid fa-ticket"></i> Mijn Tickets</a>
            <a href="#overzicht" class="btn btn--primary"><i class="fa-solid fa-clock-rotate-left"></i> Overzicht</a>
        </div>
    </nav>
</header>

<section class="hero-section container">
    <div class="hero-badge"><i class="fa-solid fa-ban"></i> Ticket Annuleren</div>
    <h1>Bestaande Ticket <span>Annuleren</span></h1>
    <p>Selecteer een reservering om te annuleren. U kunt de annulering eenvoudig terugzien in het overzicht.</p>
</section>

<main>
    <section id="tickets" class="section container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa-solid fa-ticket"></i>
                Mijn Tickets
            </h2>
            <span class="ticket-count"><?= count($tickets) ?> ticket(s)</span>
        </div>

        <?php if ($dbError): ?>
        <div class="inline-notification error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Demo-data wordt getoond (database niet beschikbaar).
        </div>
        <?php endif; ?>

        <?php if (empty($tickets)): ?>
        <div class="empty-state fade-in">
            <i class="fa-solid fa-ticket"></i>
            <p>U heeft nog geen tickets gereserveerd.</p>
            <p>Er zijn momenteel geen tickets beschikbaar om te annuleren.</p>
        </div>
        <?php else: ?>
        <div class="grid">
            <?php foreach ($tickets as $i => $ticket): ?>
            <?php
                $ticket_code = sprintf('AUR-%d-%d', (int)$ticket['id'], $gebruiker_id);
                $kanAnnuleren = in_array($ticket['status'], ['bevestigd', 'in_behandeling']);
            ?>
            <article class="ticket-card fade-in fade-in-delay-<?= min($i + 1, 4) ?>" data-id="<?= (int)$ticket['id'] ?>">
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
                    <span id="seats-display-<?= (int)$ticket['id'] ?>"><?= (int)$ticket['aantal_stoelen'] ?> stoel(en)</span>
                    <strong id="price-display-<?= (int)$ticket['id'] ?>" style="color:#d4af37;">&euro;<?= number_format((float)$ticket['totaalprijs'], 2, ',', '.') ?></strong>
                </div>

                <?php if ($kanAnnuleren): ?>
                <div class="ticket-actions">
                    <button class="btn btn--danger btn--sm" onclick="openCancelModal(<?= (int)$ticket['id'] ?>)">
                        <i class="fa-solid fa-xmark"></i> Annuleren
                    </button>
                </div>
                <?php else: ?>
                <div class="ticket-actions">
                    <span class="ticket-badge-cancelled"><i class="fa-solid fa-ban"></i> Al geannuleerd</span>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div id="overzicht" class="changelog-section">
            <div class="changelog-header">
                <h2 class="changelog-title">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Annuleringsoverzicht
                </h2>
                <?php if (!empty($changelog)): ?>
                <span class="changelog-badge"><?= count($changelog) ?> wijziging(en)</span>
                <?php endif; ?>
            </div>

            <?php if (empty($changelog)): ?>
            <div class="changelog-empty">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <p>Er zijn nog geen annuleringen vastgelegd.</p>
            </div>
            <?php else: ?>
            <div class="changelog-table-wrap fade-in">
                <table class="changelog-table" id="changelogTable">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Ticket</th>
                            <th>Voorstelling</th>
                            <th>Type</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($changelog as $log): ?>
                        <tr>
                            <td><?= date('d-m-Y H:i', strtotime($log['datum'])) ?></td>
                            <td><span class="change-ticket-code"><?= htmlspecialchars($log['ticket_code']) ?></span></td>
                            <td><?= htmlspecialchars($log['voorstelling']) ?></td>
                            <td>
                                <span class="change-type <?= changeTypeClass($log['type']) ?>">
                                    <i class="fa-solid <?= changeTypeIcon($log['type']) ?>"></i>
                                    <?= ucfirst($log['type']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars($log['detail']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<div class="modal-overlay hidden" id="cancelModal">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h3><i class="fa-solid fa-triangle-exclamation"></i> Reservering Annuleren</h3>
            <button class="modal-close" onclick="closeCancelModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Weet u zeker dat u deze reservering wilt annuleren? Dit kan niet ongedaan worden gemaakt.</p>
            <form id="cancelForm">
                <input type="hidden" id="cancelId">
                <div class="form-actions">
                    <button type="button" class="btn btn--ghost" onclick="closeCancelModal()">Terug</button>
                    <button type="submit" class="btn btn--danger">Ja, annuleren</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="notification" class="notification"></div>

<footer class="footer">
    <h3>Aurora Theater</h3>
    <p>Waar emoties, kunst en verhalen samenkomen.</p>
    <p>&copy; <?= date('Y') ?> Aurora Theater</p>
</footer>

<script src="script.js"></script>
</body>
</html>
