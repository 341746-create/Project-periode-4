<?php
// ============================================================
//  API: POST /ticket scannen/api/scan_ticket.php
//  Retourneert expliciete scenario-code zodat de frontend
//  iedere situatie professioneel kan tonen.
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'reason' => 'method_not_allowed', 'error' => 'Alleen POST toegestaan.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$code = isset($body['code']) ? strtoupper(trim($body['code'])) : '';

if ($code === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'reason' => 'missing_code', 'error' => 'Voer een ticketcode in.']);
    exit;
}

$hardcoded = ['THR12345', 'THR67890', 'VIP2025', 'SHOW001'];

try {
    $pdo = db();
    $now = new DateTime();

    // --- Demo / hardcoded codes (database onbeschikbaar fallback) ---
    if (in_array($code, $hardcoded)) {
        echo json_encode([
            'success' => true,
            'reason'  => 'ok',
            'ticket'  => [
                'code'         => $code,
                'voorstelling' => 'Demo Voorstelling',
                'datum'        => date('d-m-Y') . ' 20:00',
                'zaal'         => 'Grote Zaal',
                'klant'        => 'Demo Bezoeker',
                'stoelen'      => 1,
                'status'       => 'Bevestigd',
                'afbeelding_url' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=400&q=80',
            ],
        ]);
        exit;
    }

    // --- Formaat AUR-{reservering_id}-{gebruiker_id} ---
    if (preg_match('/^AUR-(\d+)-(\d+)$/', $code, $matches)) {
        $reservering_id = (int)$matches[1];
        $gebruiker_id   = (int)$matches[2];

        // ── READ (happy scenario) ─────────────────────────────────────
        // Lees één ticket (reservering + voorstelling + zaal + klant) uit
        // de database op basis van de gescande code.
        $stmt = $pdo->prepare("
            SELECT
                r.id,
                r.status,
                r.aantal_stoelen,
                r.aangemaakt_op,
                v.titel         AS voorstelling,
                v.afbeelding_url AS afbeelding_url,
                DATE_FORMAT(v.datum, '%d-%m-%Y') AS datum,
                v.aanvangstijd,
                v.datum         AS datum_raw,
                z.naam          AS zaal,
                g.naam          AS klant
            FROM reserveringen r
            JOIN voorstellingen v ON v.id = r.voorstelling_id
            JOIN zalen z          ON z.id = v.zaal_id
            JOIN gebruikers g     ON g.id = r.gebruiker_id
            WHERE r.id = :rid AND r.gebruiker_id = :uid
            LIMIT 1
        ");
        $stmt->execute([':rid' => $reservering_id, ':uid' => $gebruiker_id]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            // ── READ (unhappy scenario) ──────────────────────────────
            // Ticket niet gevonden in de database → nette 404 melding.
            echo json_encode([
                'success' => false,
                'reason'  => 'not_found',
                'error'   => 'Ticketcode niet gevonden in de database. Controleer de code en probeer opnieuw.',
            ]);
            exit;
        }

        // Voorstelling in het verleden?
        $showDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $ticket['datum_raw'] . ' ' . $ticket['aanvangstijd']);
        if ($showDateTime < $now) {
            echo json_encode([
                'success' => false,
                'reason'  => 'past_show',
                'error'   => 'Deze voorstelling is al geweest (' . $ticket['datum'] . ' om ' . substr($ticket['aanvangstijd'], 0, 5) . ' uur).',
                'ticket'  => [
                    'code'         => $code,
                    'voorstelling' => $ticket['voorstelling'],
                    'datum'        => $ticket['datum'] . ' ' . substr($ticket['aanvangstijd'], 0, 5),
                    'zaal'         => $ticket['zaal'],
                    'klant'        => $ticket['klant'],
                    'stoelen'      => $ticket['aantal_stoelen'],
                    'status'       => 'Voorbij',
                    'afbeelding_url' => $ticket['afbeelding_url'] ?: null,
                ],
            ]);
            exit;
        }

        if ($ticket['status'] === 'geannuleerd') {
            echo json_encode([
                'success' => false,
                'reason'  => 'cancelled',
                'error'   => 'Dit ticket is geannuleerd. Ga naar de kassa voor hulp of een nieuwe code.',
                'ticket'  => [
                    'code'         => $code,
                    'voorstelling' => $ticket['voorstelling'],
                    'datum'        => $ticket['datum'] . ' ' . substr($ticket['aanvangstijd'], 0, 5),
                    'zaal'         => $ticket['zaal'],
                    'klant'        => $ticket['klant'],
                    'stoelen'      => $ticket['aantal_stoelen'],
                    'status'       => 'Geannuleerd',
                    'afbeelding_url' => $ticket['afbeelding_url'] ?: null,
                ],
            ]);
            exit;
        }

        if ($ticket['status'] === 'voltooid') {
            echo json_encode([
                'success' => false,
                'reason'  => 'already_checked',
                'error'   => 'Dit ticket is al ingecheckt. De toegang is eenmalig.',
                'ticket'  => [
                    'code'         => $code,
                    'voorstelling' => $ticket['voorstelling'],
                    'datum'        => $ticket['datum'] . ' ' . substr($ticket['aanvangstijd'], 0, 5),
                    'zaal'         => $ticket['zaal'],
                    'klant'        => $ticket['klant'],
                    'stoelen'      => $ticket['aantal_stoelen'],
                    'status'       => 'Al Ingecheckt',
                    'afbeelding_url' => $ticket['afbeelding_url'] ?: null,
                ],
            ]);
            exit;
        }

        // Bevestigd -> markeer als voltooid
         if ($ticket['status'] === 'bevestigd') {
             $upd = $pdo->prepare("UPDATE reserveringen SET status = 'voltooid' WHERE id = :id");
             $upd->execute([':id' => $reservering_id]);
             $ticket['status'] = 'voltooid';
         }
 
         echo json_encode([
             'success' => true,
             'reason'  => 'ok',
             'ticket'  => [
                 'code'         => $code,
                 'voorstelling' => $ticket['voorstelling'],
                 'datum'        => $ticket['datum'] . ' ' . substr($ticket['aanvangstijd'], 0, 5),
                 'zaal'         => $ticket['zaal'],
                 'klant'        => $ticket['klant'],
                 'stoelen'      => $ticket['aantal_stoelen'],
                 'status'       => 'Ingecheckt',
                 'afbeelding_url' => $ticket['afbeelding_url'] ?: 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=400&q=80',
             ],
         ]);
         exit;
     }

    // Geen herkend formaat
    echo json_encode([
        'success' => false,
        'reason'  => 'invalid_format',
        'error'   => 'Ongeldige codeformaat. Verwacht formaat: AUR-{ID}-{GebruikerID}.',
    ]);
} catch (Exception $e) {
    // Database niet beschikbaar: hardcoded fallback voor demo
    if (in_array($code, $hardcoded)) {
        echo json_encode([
            'success' => true,
            'reason'  => 'ok',
            'ticket'  => [
                'code'         => $code,
                'voorstelling' => 'Demo Voorstelling',
                'datum'        => date('d-m-Y') . ' 20:00',
                'zaal'         => 'Grote Zaal',
                'klant'        => 'Demo Bezoeker',
                'stoelen'      => 1,
                'status'       => 'Bevestigd (offline)',
            ],
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'reason'  => 'db_error',
            'error'   => 'Database niet beschikbaar. Controleer de verbinding en probeer opnieuw.',
        ]);
    }
}
