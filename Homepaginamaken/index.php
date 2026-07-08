<?php
// ============================================================
//  Aurora Theater — Homepagina
// ============================================================
require_once __DIR__ . '/../config/auth.php';
$gebruiker = getGebruiker();
$isIngelogd = isIngelogd();
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
    <link rel="stylesheet" href="/style/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<header class="header">
    <nav class="nav container">

        <div class="logo logo--home">
            <i class="fa-solid fa-masks-theater"></i>
            Aurora Theater
        </div>

        <ul class="nav-links">
            <li><a href="#shows">Voorstellingen</a></li>
            <li><a href="#stats">Statistieken</a></li>
            <li><a href="#modules">Modules</a></li>
            <li><a href="#about">Over ons</a></li>
        </ul>

        <div class="nav-actions">

            <?php if ($isIngelogd && in_array($gebruiker['rol'] ?? '', ['admin','medewerker'])): ?>
            <!-- ── BEHEER (alleen admin / medewerker) ── -->
            <div class="dropdown" id="beheerDropdown">
                <a href="#" class="btn btn--ghost dropdown-toggle" onclick="toggleDropdown(event)">
                    <i class="fa-solid fa-shield-halved"></i> Beheer <i class="fa-solid fa-chevron-down"></i>
                </a>
                <div class="dropdown-menu" id="beheerMenu">
                    <div style="padding:8px 14px 6px;font-size:.7rem;color:#666;text-transform:uppercase;letter-spacing:1px">Beheer</div>
                    <a href="/overzicht%20medewerkers/index.php" class="dropdown-item"><i class="fa-solid fa-users"></i> Medewerkers</a>
                    <a href="/Medewerker%20beheren/index.php" class="dropdown-item"><i class="fa-solid fa-user-gear"></i> Medewerker beheren</a>
                    <a href="/Nieuwe%20medewerker%20toevoegen/index.php" class="dropdown-item"><i class="fa-solid fa-user-plus"></i> Nieuwe medewerker</a>
                    <a href="/medewerker-wijzigen/index.php" class="dropdown-item"><i class="fa-solid fa-user-pen"></i> Medewerker wijzigen</a>
                    <a href="/medewerker-verwijderen/index.php" class="dropdown-item"><i class="fa-solid fa-user-minus"></i> Medewerker verwijderen</a>
                    <a href="/Overzicht%20voorstellingen/index.php" class="dropdown-item"><i class="fa-solid fa-theater-masks"></i> Voorstellingen</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── MIJN TICKETS (iedereen) ── -->
            <div class="dropdown" id="mijnTicketsDropdown">
                <a href="#" class="btn btn--ghost dropdown-toggle" onclick="toggleDropdown(event)">Mijn Tickets <i class="fa-solid fa-chevron-down"></i></a>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/ticket%20overzicht/index.php" class="dropdown-item"><i class="fa-solid fa-list"></i> Ticket Overzicht</a>
                    <a href="/BestaandeTicketWijzigen/index.php" class="dropdown-item"><i class="fa-solid fa-pen-to-square"></i> Ticket Wijzigen</a>
                    <a href="/NieuweTicketToevoegen/index.php" class="dropdown-item"><i class="fa-solid fa-plus"></i> Nieuwe Ticket</a>
                    <a href="/ticket%20scannen/index.php" class="dropdown-item"><i class="fa-solid fa-qrcode"></i> Ticket Scannen</a>
                </div>
            </div>

            <!-- ── USER / INLOGGEN ── -->
            <?php if ($isIngelogd && $gebruiker): ?>
                <div class="dropdown" id="userDropdown">
                    <a href="#" class="user-widget dropdown-toggle" onclick="toggleDropdown(event)">
                        <div class="user-avatar">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($gebruiker['naam']) ?></span>
                            <span class="user-role-badge user-role--<?= htmlspecialchars($gebruiker['rol']) ?>">
                                <?php
                                    $rolLabels = ['admin'=>'Beheerder','medewerker'=>'Medewerker','klant'=>'Klant'];
                                    echo $rolLabels[$gebruiker['rol']] ?? ucfirst($gebruiker['rol']);
                                ?>
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="font-size:.7rem;color:#aaa"></i>
                    </a>
                    <div class="dropdown-menu" id="userMenu">
                        <div class="user-menu-header">
                            <i class="fa-solid fa-circle-user" style="color:#d4af37;font-size:1.4rem"></i>
                            <div>
                                <div style="font-weight:700;font-size:.95rem"><?= htmlspecialchars($gebruiker['naam']) ?></div>
                                <div style="font-size:.78rem;color:#aaa"><?= htmlspecialchars($gebruiker['rol']) ?></div>
                            </div>
                        </div>
                        <a href="/Project-periode-4-Feature-Uitloggen/index.html" class="dropdown-item" style="color:#ff6b6b">
                            <i class="fa-solid fa-right-from-bracket" style="color:#ff6b6b"></i> Uitloggen
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/Project-periode-4-Feature-Inloggen/index.html" class="btn btn--ghost">
                    <i class="fa-solid fa-right-to-bracket"></i> Inloggen
                </a>
            <?php endif; ?>

            <!-- ── RESERVEREN (iedereen) ── -->
            <a href="/NieuweTicketToevoegen/index.php" class="btn btn--primary">Reserveren</a>
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
                    <a href="/NieuweTicketToevoegen/index.php?voorstelling=<?= (int)$show['id'] ?>" class="btn btn--primary btn--sm">
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

<!-- MODULES -->
<section id="modules" class="section module-section container">
    <h2 class="section-title">Aurora Theater Modules</h2>
    <p class="text-muted">Alle onderdelen van de applicatie zijn vanaf de homepage bereikbaar.</p>

    <div class="module-grid">
        <a href="/NieuweTicketToevoegen/index.php" class="module-card">
            <i class="fa-solid fa-ticket"></i>
            <h3>Reserveren</h3>
            <p>Maak nieuwe ticketreserveringen en beheer beschikbare stoelen.</p>
        </a>

        <a href="/ticket%20overzicht/index.php" class="module-card">
            <i class="fa-solid fa-list-check"></i>
            <h3>Ticket Overzicht</h3>
            <p>Bekijk, update en annuleer reserveringen.</p>
        </a>

        <a href="/BestaandeTicketWijzigen/index.php" class="module-card">
            <i class="fa-solid fa-pen-to-square"></i>
            <h3>Ticket Wijzigen</h3>
            <p>Pas bestaande reserveringen aan en bekijk wijzigingen.</p>
        </a>

        <a href="/ticket%20scannen/index.php" class="module-card">
            <i class="fa-solid fa-qrcode"></i>
            <h3>Ticket Scanner</h3>
            <p>Valideer tickets bij binnenkomst met een ticketcode of QR-code.</p>
        </a>

        <?php if ($isIngelogd && in_array($gebruiker['rol'] ?? '', ['admin', 'medewerker'])): ?>
        <!-- ── Administratieve opties (alleen voor admin / medewerker) ── -->
        <a href="/Overzicht%20voorstellingen/index.php" class="module-card">
            <i class="fa-solid fa-theater-masks"></i>
            <h3>Voorstellingen</h3>
            <p>Bekijk en beheer het actuele aanbod van het theater.</p>
        </a>

        <a href="/overzicht%20medewerkers/index.php" class="module-card">
            <i class="fa-solid fa-users"></i>
            <h3>Medewerkers</h3>
            <p>Bekijk de bezetting en filter op afdeling of functie.</p>
        </a>

        <a href="/Medewerker%20beheren/index.php" class="module-card">
            <i class="fa-solid fa-user-gear"></i>
            <h3>Medewerker Beheren</h3>
            <p>Voeg medewerkers toe, bewerk rollen en blokkeer accounts.</p>
        </a>

        <a href="/medewerker-wijzigen/index.php" class="module-card">
            <i class="fa-solid fa-user-pen"></i>
            <h3>Medewerker Wijzigen</h3>
            <p>Cast-gegevens van een lid van het gezelschap bijwerken.</p>
        </a>

        <a href="/medewerker-verwijderen/index.php" class="module-card">
            <i class="fa-solid fa-user-minus"></i>
            <h3>Medewerker Verwijderen</h3>
            <p>Een lid van het gezelschap permanent uit de bezetting verwijderen.</p>
        </a>
        <?php endif; ?>
    </div>
</section>

</main>

<footer class="footer">
    <h3>Aurora Theater</h3>
    <p>Waar emoties, kunst en verhalen samenkomen.</p>
    <p>&copy; <?= date('Y') ?> Aurora Theater</p>
</footer>

<!-- UITLOGGEN MODAL -->
<div id="logoutModal" class="logout-overlay" onclick="closeLogoutModalOutside(event)">
    <div class="logout-card">
        <div class="logout-icon">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        <h2 class="logout-title">Weet je het zeker?</h2>
        <p class="logout-subtitle">Je staat op het punt om je sessie te beëindigen.</p>
        <a href="/logout.php" class="logout-btn-confirm">
            Uitloggen <i class="fa-solid fa-arrow-right"></i>
        </a>
        <button onclick="closeLogoutModal()" class="logout-btn-cancel">Annuleren</button>
    </div>
</div>

<script src="script.js"></script>

</body>
</html>
