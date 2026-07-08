<?php
// ============================================================
//  Aurora Theater — Ticket Scanner
// ============================================================
require_once __DIR__ . '/../config/auth.php';
$gebruiker = getGebruiker();
$isIngelogd = isIngelogd();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Scan Aurora Theater tickets via de barcode scanner.">
    <title>Ticket Scanner — Aurora Theater</title>
    <link rel="stylesheet" href="/style/header.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<header class="header">
    <div class="header-inner">
        <a href="/Homepaginamaken/index.php" class="logo">
            <svg class="logo-icon" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" style="display:block;">
                <ellipse cx="8.5" cy="9.5" rx="5.8" ry="6.2" fill="#d4af37"/>
                <ellipse cx="15.5" cy="9.5" rx="5.8" ry="6.2" fill="#d4af37"/>
                <circle cx="6" cy="8.5" r="1.2" fill="#07070a"/>
                <circle cx="11" cy="8.5" r="1.2" fill="#07070a"/>
                <path d="M4.5 13.5 C6 16.5 11 16.5 12.5 13.5" stroke="#07070a" stroke-width="1.2" fill="none"/>
                <path d="M11.5 13.5 C13 16.5 18 16.5 19.5 13.5" stroke="#07070a" stroke-width="1.2" fill="none"/>
            </svg>
            <span>Aurora Theater</span>
        </a>
        <nav>
            <a href="/ticket%20overzicht/index.php"><span style="font-size:1.1em;">≡</span> Mijn Tickets</a>
        </nav>
        <div class="nav-actions">
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
                        <i class="fa-solid fa-chevron-down" style="font-size:.65rem;opacity:.6"></i>
                    </a>
                    <div class="dropdown-menu" id="userMenu">
                        <div class="user-menu-header">
                            <i class="fa-solid fa-circle-user" style="color:#d4af37;font-size:1.3rem"></i>
                            <div>
                                <div style="font-weight:700;font-size:.92rem"><?= htmlspecialchars($gebruiker['naam']) ?></div>
                                <div style="font-size:.76rem;color:#aaa"><?= htmlspecialchars($gebruiker['rol']) ?></div>
                            </div>
                        </div>
                        <a href="/Project-periode-4-Feature-Uitloggen/index.html" class="dropdown-item" style="color:#ff6b6b">
                            <i class="fa-solid fa-right-from-bracket" style="color:#ff6b6b"></i> Uitloggen
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/login.php" class="btn btn--ghost">
                    <span style="margin-right:4px;">&#8594;</span> Inloggen
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

    <div class="scanner-container">
        <div class="scanner-visual">
            <svg width="56" height="56" viewBox="0 0 100 100" aria-hidden="true" style="display:block;color:#d4af37;">
                <rect x="2" y="2" width="26" height="26" rx="3" fill="#d4af37"/>
                <rect x="8" y="8" width="14" height="14" rx="2" fill="#07070a"/>
                <rect x="12" y="12" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="72" y="2" width="26" height="26" rx="3" fill="#d4af37"/>
                <rect x="78" y="8" width="14" height="14" rx="2" fill="#07070a"/>
                <rect x="82" y="12" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="2" y="72" width="26" height="26" rx="3" fill="#d4af37"/>
                <rect x="8" y="78" width="14" height="14" rx="2" fill="#07070a"/>
                <rect x="12" y="82" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="34" y="4" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="46" y="4" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="58" y="4" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="34" y="16" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="46" y="16" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="58" y="16" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="4" y="34" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="16" y="34" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="28" y="34" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="40" y="34" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="52" y="34" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="64" y="34" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="76" y="34" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="88" y="34" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="34" y="46" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="46" y="46" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="58" y="46" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="70" y="46" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="82" y="46" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="34" y="58" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="46" y="58" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="58" y="58" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="70" y="58" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="82" y="58" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="34" y="70" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="46" y="70" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="58" y="70" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="70" y="70" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="82" y="70" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="40" y="82" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="52" y="82" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="64" y="82" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="76" y="82" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="82" y="88" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="34" y="92" width="6" height="6" rx="1" fill="#d4af37"/>
                <rect x="58" y="92" width="6" height="6" rx="1" fill="#d4af37"/>
            </svg>
            <div class="scanner-ring"></div>
            <div class="scanner-ring"></div>
        </div>

        <h1>🎭 Aurora Theater — Ticket Scanner</h1>

        <p class="scanner-subtitle">Scan een ticketcode om toegang te verlenen</p>

        <div class="input-group">
            <div class="input-wrapper">
                <input
                    type="text"
                    id="ticketCode"
                    placeholder="bijv. AUR-3-3"
                    autocomplete="off"
                    autofocus
                >
                <span style="position:absolute;left:16px;color:#888;pointer-events:none;">🎫</span>
            </div>
            <button id="scanBtn">
                <span>&#128269;</span> Scan Ticket
            </button>
        </div>

        <div id="result"></div>

        <div class="scanner-history">
            <h3><span style="margin-right:6px;">&#8635;</span> Recente Scans</h3>
            <ul id="historyList">
                <li class="history-empty">Nog geen scans uitgevoerd.</li>
            </ul>
        </div>

        <div class="scanner-footer">
            <a href="/ticket%20overzicht/index.php">Ticket Overzicht</a>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
