<?php
// ============================================================
//  Aurora Theater — Nieuw Ticket Toevoegen (Reservering)
// ============================================================
require_once __DIR__ . '/../config/database.php';

// Haal beschikbare voorstellingen op
$voorstelling_id = isset($_GET['voorstelling']) ? (int)$_GET['voorstelling'] : 0;

try {
    $voorstellingen = db()->query("
        SELECT id, titel, datum, aanvangstijd, prijs_per_stoel, beschikbare_stoelen, categorie
        FROM voorstellingen
        WHERE status IN ('actief','gepland') AND beschikbare_stoelen > 0
        ORDER BY datum ASC
    ")->fetchAll();

    // Geselecteerde voorstelling details
    $geselecteerd = null;
    if ($voorstelling_id > 0) {
        $stmt = db()->prepare("SELECT * FROM voorstellingen WHERE id = :id");
        $stmt->execute([':id' => $voorstelling_id]);
        $geselecteerd = $stmt->fetch();
    }
} catch (Exception $e) {
    $voorstellingen = [];
    $geselecteerd   = null;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reserveer uw Aurora Theater tickets eenvoudig online.">
    <title>Nieuw Ticket Reserveren — Aurora Theater</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="/style/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<header class="header">
    <div class="header-inner">
        <a href="/index.php" class="logo">
            <i class="fa-solid fa-masks-theater"></i>
            Aurora Theater
        </a>
        <nav>
            <a href="/ticket%20overzicht/index.php"><i class="fa-solid fa-list"></i> Mijn Tickets</a>
            <a href="/ticket%20scannen/index.php"><i class="fa-solid fa-qrcode"></i> Scanner</a>
        </nav>
    </div>
</header>

<main class="main-content">
    <div class="shows-section">
        <div class="shows-header">
            <h2><i class="fa-solid fa-masks-theater"></i> Huidige Voorstellingen</h2>
            <p>Bekijk ons actuele aanbod en reserveer direct uw tickets</p>
        </div>

        <div class="shows-grid">
            <?php if (empty($voorstellingen)): ?>
            <div class="shows-empty">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p>Er zijn momenteel geen voorstellingen beschikbaar.</p>
            </div>
            <?php else: ?>
            <?php foreach ($voorstellingen as $show): ?>
            <article class="show-card-large" data-voorstelling="<?= (int)$show['id'] ?>">
                <div class="show-card-image">
                    <?php if (!empty($show['afbeelding_url'])): ?>
                        <img src="<?= htmlspecialchars($show['afbeelding_url']) ?>" alt="<?= htmlspecialchars($show['titel']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="show-card-placeholder">
                            <i class="fa-solid fa-masks-theater"></i>
                        </div>
                    <?php endif; ?>
                    <span class="show-card-badge"><?= htmlspecialchars(ucfirst($show['categorie'])) ?></span>
                </div>
                <div class="show-card-body">
                    <h3><?= htmlspecialchars($show['titel']) ?></h3>
                    <p class="show-card-desc"><?= htmlspecialchars($show['beschrijving'] ?? '') ?></p>
                    <div class="show-card-meta">
                        <span><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($show['datum'])) ?></span>
                        <span><i class="fa-regular fa-clock"></i> <?= substr($show['aanvangstijd'], 0, 5) ?></span>
                        <span><i class="fa-solid fa-chair"></i> <?= (int)$show['beschikbare_stoelen'] ?> vrij</span>
                    </div>
                    <div class="show-card-footer">
                        <strong class="show-card-price">&euro;<?= number_format((float)$show['prijs_per_stoel'], 2, ',', '.') ?></strong>
                        <button type="button" class="btn btn--primary btn--sm reserve-from-show" data-id="<?= (int)$show['id'] ?>">
                            <i class="fa-solid fa-ticket"></i> Reserveer
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-container" id="reserveringFormContainer">
        <div class="form-header">
            <h1><i class="fa-solid fa-ticket"></i> Nieuw Ticket Reserveren</h1>
            <p>Kies een voorstelling en vul uw gegevens in</p>
        </div>

        <!-- Stap indicator -->
        <div class="steps">
            <div class="step active" id="step-1-indicator">
                <span>1</span> Voorstelling
            </div>
            <div class="step-line"></div>
            <div class="step" id="step-2-indicator">
                <span>2</span> Gegevens
            </div>
            <div class="step-line"></div>
            <div class="step" id="step-3-indicator">
                <span>3</span> Betaling
            </div>
        </div>

        <div id="notification" class="notification"></div>

        <form id="reserveringForm" novalidate>

            <!-- STAP 1: Voorstelling kiezen -->
            <div class="form-step" id="step-1">
                <h2>Kies een voorstelling</h2>

                <div class="voorstellingen-grid">
                    <?php if (empty($voorstellingen)): ?>
                    <div class="show-card placeholder-card">
                        <i class="fa-solid fa-masks-theater"></i>
                        <p>Geen voorstellingen beschikbaar.</p>
                        <small>Controleer de database verbinding.</small>
                    </div>
                    <?php else: ?>
                    <?php foreach ($voorstellingen as $show): ?>
                    <label class="show-card <?= $show['id'] == $voorstelling_id ? 'selected' : '' ?>">
                        <input
                            type="radio"
                            name="voorstelling_id"
                            value="<?= (int)$show['id'] ?>"
                            data-prijs="<?= (float)$show['prijs_per_stoel'] ?>"
                            data-beschikbaar="<?= (int)$show['beschikbare_stoelen'] ?>"
                            <?= $show['id'] == $voorstelling_id ? 'checked' : '' ?>
                        >
                        <div class="show-card-body">
                            <span class="badge-categorie"><?= htmlspecialchars(ucfirst($show['categorie'])) ?></span>
                            <h3><?= htmlspecialchars($show['titel']) ?></h3>
                            <p>
                                <i class="fa-regular fa-calendar"></i>
                                <?= date('d M Y', strtotime($show['datum'])) ?> om <?= substr($show['aanvangstijd'], 0, 5) ?>
                            </p>
                            <div class="show-card-footer">
                                <strong>&euro;<?= number_format((float)$show['prijs_per_stoel'], 2, ',', '.') ?></strong>
                                <small><?= (int)$show['beschikbare_stoelen'] ?> plaatsen vrij</small>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button type="button" class="btn btn--primary btn--full" id="nextStep1" onclick="goToStep(2)">
                    Volgende stap &rarr;
                </button>
            </div>

            <!-- STAP 2: Persoonsgegevens + stoelen -->
            <div class="form-step hidden" id="step-2">
                <h2>Uw gegevens</h2>

                <div class="form-group">
                    <label for="naam">Volledige naam</label>
                    <input type="text" id="naam" name="naam" placeholder="Jan de Vries" required>
                </div>

                <div class="form-group">
                    <label for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" placeholder="jan@voorbeeld.nl" required>
                </div>

                <div class="form-group">
                    <label for="aantal_stoelen">Aantal tickets</label>
                    <div class="counter">
                        <button type="button" class="counter-btn" id="minBtn">&#8722;</button>
                        <input type="number" id="aantal_stoelen" name="aantal_stoelen" value="1" min="1" max="10" readonly>
                        <button type="button" class="counter-btn" id="plusBtn">&#43;</button>
                    </div>
                </div>

                <div class="prijs-preview" id="prijsPreview">
                    <span>Totaal:</span>
                    <strong id="totalePrijs">&euro;0,00</strong>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn--ghost" onclick="goToStep(1)">&larr; Terug</button>
                    <button type="button" class="btn btn--primary" onclick="goToStep(3)">Volgende &rarr;</button>
                </div>
            </div>

            <!-- STAP 3: Betaalmethode + bevestigen -->
            <div class="form-step hidden" id="step-3">
                <h2>Betaalmethode</h2>

                <div class="betaal-opties">
                    <label class="betaal-optie">
                        <input type="radio" name="betaalmethode" value="ideal" checked>
                        <span><i class="fa-solid fa-bolt"></i> iDEAL</span>
                    </label>
                    <label class="betaal-optie">
                        <input type="radio" name="betaalmethode" value="creditcard">
                        <span><i class="fa-solid fa-credit-card"></i> Creditcard</span>
                    </label>
                    <label class="betaal-optie">
                        <input type="radio" name="betaalmethode" value="pin">
                        <span><i class="fa-solid fa-mobile-screen"></i> Pin</span>
                    </label>
                    <label class="betaal-optie">
                        <input type="radio" name="betaalmethode" value="contant">
                        <span><i class="fa-solid fa-money-bill-wave"></i> Contant</span>
                    </label>
                </div>

                <div class="reservering-samenvatting" id="samenvatting">
                    <h3>Samenvatting</h3>
                    <div id="samenvattingInhoud"></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn--ghost" onclick="goToStep(2)">&larr; Terug</button>
                    <button type="submit" class="btn btn--primary" id="submitBtn">
                        <i class="fa-solid fa-check"></i> Bevestig Reservering
                    </button>
                </div>
            </div>

            <!-- STAP 4: Bevestiging -->
            <div class="form-step hidden" id="step-4">
                <div class="success-screen">
                    <div class="success-icon">✅</div>
                    <h2>Reservering bevestigd!</h2>
                    <p id="successMessage"></p>
                    <div class="ticket-code-display" id="ticketCodeDisplay"></div>
                    <div class="success-actions">
                        <a href="/ticket%20overzicht/index.php" class="btn btn--primary">Mijn Tickets bekijken</a>
                    </div>
                </div>
            </div>

        </form>
    </div>
</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Aurora Theater
</footer>

<script src="script.js"></script>
</body>
</html>
