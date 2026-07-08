<?php
// ============================================================
//  Aurora Theater — Authenticatie helper
// ============================================================

/**
 * Controleer of de bezoeker is ingelogd.
 * Optioneel: eis een bepaalde rol ('admin', 'medewerker', 'klant').
 * Bij geen toegang → redirect naar login met een terugkeer-URL.
 *
 * @param string|array|null $required_rol  Bijv. 'admin' of ['admin','medewerker']
 */
function requireLogin(mixed $required_rol = null): void
{
    $id   = $_COOKIE['gebruiker_id']   ?? null;
    $rol  = $_COOKIE['gebruiker_rol']  ?? null;
    $naam = $_COOKIE['gebruiker_naam'] ?? null;

    if (empty($id) || empty($rol)) {
        $terug = '/';
        if (isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI'])) {
            $terug = $_SERVER['REQUEST_URI'];
        }
        $terug = urlencode($terug);
        header("Location: /login.php?redirect=$terug");
        exit;
    }

    if ($required_rol !== null) {
        $toegestaan = (array) $required_rol;
        if (!in_array($rol, $toegestaan, true)) {
            header('Location: /Homepaginamaken/index.php?fout=geen_toegang');
            exit;
        }
    }
}

/**
 * Haal de ingelogde gebruiker uit cookies.
 * Geeft null terug als niet ingelogd.
 */
function getGebruiker(): ?array
{
    $id = $_COOKIE['gebruiker_id'] ?? null;
    if (!$id) return null;

    return [
        'id'   => (int) $id,
        'naam' => $_COOKIE['gebruiker_naam'] ?? '',
        'rol'  => $_COOKIE['gebruiker_rol']  ?? '',
    ];
}

/**
 * Is de huidige bezoeker ingelogd?
 */
function isIngelogd(): bool
{
    return !empty($_COOKIE['gebruiker_id']);
}
