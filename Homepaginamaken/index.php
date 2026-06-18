<?php
// ============================================================
//  Aurora Theater — Homepagina
// ============================================================
$dbError = null;
try {
    require_once __DIR__ . '/../config/database.php';

    // Haal actieve voorstellingen op uit de database
    try {
        $stmt = db()->query("
            SELECT v.id, v.titel, v.beschrijving, v.categorie, v.datum,
                   v.aanvangstijd, v.prijs_per_stoel, v.afbeelding_url,
                   v.beschikbare_stoelen, v.status, z.naam AS zaal_naam
            FROM voorstellingen v
            JOIN zalen z ON z.id = v.zaal_id
            WHERE v.status IN ('actief','gepland')
            ORDER BY v.datum ASC
            LIMIT 6
        ");
        $voorstellingen = $stmt->fetchAll();
    } catch (Exception $e) {
        $voorstellingen = [];
    }

    // Statistieken
    try {
        $stats = db()->query("
            SELECT
                (SELECT COUNT(*) FROM voorstellingen) AS totaal_voorstellingen,
                (SELECT COUNT(*) FROM gebruikers WHERE rol = 'klant') AS totaal_klanten,
                (SELECT COUNT(*) FROM reserveringen WHERE status = 'bevestigd') AS bevestigde_reserveringen,
                (SELECT SUM(totaalprijs) FROM reserveringen WHERE status IN ('bevestigd','voltooid')) AS totaal_omzet
        ")->fetch();
    } catch (Exception $e) {
        $stats = null;
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
    $voorstellingen = [];
    $stats = null;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aurora Theater — Ontdek indrukwekkende toneelstukken, musicals en klassieke voorstellingen. Reserveer uw tickets online.">
    <title>Aurora Theater — Waar Verhalen Tot Leven Komen</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<header class="header">
    <nav class="nav container">

        <div class="logo">
            <i class="fa-solid fa-masks-theater"></i>
            Aurora Theater
        </div>

        <ul class="nav-links">
            <li><a href="#shows">Voorstellingen</a></li>
            <li><a href="#stats">Statistieken</a></li>
            <li><a href="#about">Over ons</a></li>
        </ul>

        <div class="nav-actions">
            <div class="dropdown" id="mijnTicketsDropdown">
                <a href="#" class="btn btn--ghost dropdown-toggle" onclick="toggleDropdown(event)">Mijn Tickets <i class="fa-solid fa-chevron-down"></i></a>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="ticket_overzicht/index.php" class="dropdown-item"><i class="fa-solid fa-list"></i> Ticket Overzicht</a>
                    <a href="nieuwe_ticket/index.php" class="dropdown-item"><i class="fa-solid fa-plus"></i> Nieuwe Ticket</a>
                    <a href="ticket_scannen/index.php" class="dropdown-item"><i class="fa-solid fa-qrcode"></i> Ticket Scannen</a>
                </div>
            </div>
            <a href="nieuwe_ticket/index.php" class="btn btn--primary">Reserveren</a>
        </div>

    </nav>
</header>

<main>

<!-- HERO -->
<section class="hero">
    <div class="hero-overlay"></div>

    <div class="hero-content container">
        <span class="badge">Aurora Theater Experience</span>

        <h1>
            Waar Verhalen <span>Tot Leven Komen</span>
        </h1>

        <p>
            Ontdek indrukwekkende toneelstukken, musicals en klassieke voorstellingen in een unieke theaterbeleving.
        </p>

        <div class="hero-actions">
            <a href="#shows" class="btn btn--primary">Voorstellingen bekijken</a>
            <a href="#about" class="btn btn--ghost">Meer info</a>
        </div>
    </div>
</section>

<!-- SHOWS -->
<section id="shows" class="section container">
    <h2 class="section-title">Huidige Voorstellingen</h2>

    <div class="grid">
        <?php if (empty($voorstellingen)): ?>
            <!-- Fallback als database niet beschikbaar is -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=800&q=80" loading="lazy" alt="Modern Drama">
                <h3>Modern Drama</h3>
                <p>Emotioneel hedendaags toneelstuk &bull; Grote Zaal</p>
            </article>
            <article class="card">
                <img src="https://images.unsplash.com/photo-1507924538820-ede94a04019d?auto=format&fit=crop&w=800&q=80" loading="lazy" alt="Broadway Musical Night">
                <h3>Broadway Musical Night</h3>
                <p>Live zang &amp; dans productie &bull; Main Stage</p>
            </article>
            <article class="card">
                <img src="https://images.unsplash.com/photo-1518972559570-7cc1309f3229?auto=format&fit=crop&w=800&q=80" loading="lazy" alt="Klassiek Shakespeare">
                <h3>Klassiek Shakespeare</h3>
                <p>Traditioneel toneel &bull; Kleine Zaal</p>
            </article>
        <?php else: ?>
            <?php foreach ($voorstellingen as $show): ?>
            <article class="card" data-id="<?= (int)$show['id'] ?>">
                <?php if ($show['afbeelding_url']): ?>
                <img src="<?= htmlspecialchars($show['afbeelding_url']) ?>" loading="lazy" alt="<?= htmlspecialchars($show['titel']) ?>">
                <?php endif; ?>
                <h3><?= htmlspecialchars($show['titel']) ?></h3>
                <p>
                    <?= htmlspecialchars(ucfirst($show['categorie'])) ?> &bull;
                    <?= htmlspecialchars($show['zaal_naam']) ?> &bull;
                    &euro;<?= number_format((float)$show['prijs_per_stoel'], 2, ',', '.') ?>
                </p>
                <div class="card-footer">
                    <span><?= date('d M Y', strtotime($show['datum'])) ?></span>
                    <a href="NieuweTicketToevoegen/index.php?voorstelling=<?= (int)$show['id'] ?>" class="btn btn--primary btn--sm">
                        Reserveer
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ABOUT -->
<section id="about" class="section section--center">
    <h2 class="section-title">Over Aurora Theater</h2>

    <p class="text-muted">
        Aurora Theater biedt een breed scala aan toneelkunst: van klassieke stukken tot moderne producties en musicals.
        Wij brengen verhalen tot leven met emotie, vakmanschap en passie.
    </p>
</section>

<!-- STATS -->
<section id="stats" class="stats">

    <div class="stat">
        <h3>25+</h3>
        <p>Jaar ervaring</p>
    </div>

    <div class="stat">
        <h3><?= isset($stats) && is_array($stats) && isset($stats['totaal_klanten']) && is_numeric($stats['totaal_klanten'])
            ? number_format((int)$stats['totaal_klanten'])
            : '120K' ?></h3>
        <p>Bezoekers</p>
    </div>

    <div class="stat">
        <h3><?= isset($stats) && is_array($stats) && isset($stats['totaal_voorstellingen']) && is_numeric($stats['totaal_voorstellingen'])
            ? (int)$stats['totaal_voorstellingen']
            : '350+' ?></h3>
        <p>Voorstellingen</p>
    </div>

    <div class="stat">
        <h3>4.9&#9733;</h3>
        <p>Beoordeling</p>
    </div>

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
