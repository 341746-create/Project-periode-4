<?php
// ============================================================
//  API: POST /ticket scannen/api/scan_ticket.php
//  Valideert een ticketcode via de database
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Alleen POST toegestaan.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$code = isset($body['code']) ? strtoupper(trim($body['code'])) : '';

if (empty($code)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Geen ticketcode opgegeven.']);
    exit;
}

// Fallback hardcoded codes (als database niet beschikbaar is)
$hardcoded = ['THR12345', 'THR67890', 'VIP2025', 'SHOW001'];

try {
    $pdo = db();

    // Zoek reservering op ticket-code (formaat: AUR-{reservering_id}-{gebruiker_id})
    // bijv. AUR-5-2
    if (preg_match('/^AUR-(\d+)-(\d+)$/', $code, $matches)) {
        $reservering_id = (int)$matches[1];
        $gebruiker_id   = (int)$matches[2];

        $stmt = $pdo->prepare("
            SELECT
                r.id,
                r.status,
                r.aantal_stoelen,
                v.titel     AS voorstelling,
                DATE_FORMAT(v.datum, '%d-%m-%Y') AS datum,
                v.aanvangstijd,
                z.naam      AS zaal,
                g.naam      AS klant
            FROM reserveringen r
            JOIN voorstellingen v ON v.id = r.voorstelling_id
            JOIN zalen z          ON z.id = v.zaal_id
            JOIN gebruikers g     ON g.id = r.gebruiker_id
            WHERE r.id = :rid AND r.gebruiker_id = :uid
        ");
        $stmt->execute([':rid' => $reservering_id, ':uid' => $gebruiker_id]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            echo json_encode(['success' => false, 'error' => 'Ticketcode niet gevonden in de database.']);
            exit;
        }

        if ($ticket['status'] === 'geannuleerd') {
            echo json_encode(['success' => false, 'error' => 'Dit ticket is geannuleerd.']);
            exit;
        }

        // Markeer als voltooid (ingecheckt)
        if ($ticket['status'] === 'bevestigd') {
            $upd = $pdo->prepare("UPDATE reserveringen SET status = 'voltooid' WHERE id = :id");
            $upd->execute([':id' => $reservering_id]);
            $ticket['status'] = 'voltooid';
        }

        echo json_encode([
            'success' => true,
            'ticket'  => [
                'code'         => $code,
                'voorstelling' => $ticket['voorstelling'],
                'datum'        => $ticket['datum'] . ' ' . substr($ticket['aanvangstijd'], 0, 5),
                'zaal'         => $ticket['zaal'],
                'klant'        => $ticket['klant'],
                'stoelen'      => $ticket['aantal_stoelen'],
                'status'       => ucfirst($ticket['status']),
            ],
        ]);

    } elseif (in_array($code, $hardcoded)) {
        // Hardcoded fallback (demo)
        echo json_encode([
            'success' => true,
            'ticket'  => [
                'code'         => $code,
                'voorstelling' => 'Demo Voorstelling',
                'datum'        => date('d-m-Y') . ' 20:00',
                'zaal'         => 'Grote Zaal',
                'klant'        => 'Demo Bezoeker',
                'stoelen'      => 1,
                'status'       => 'Bevestigd',
            ],
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Onbekende ticketcode. Gebruik formaat AUR-{ID}-{GebruikerID}.']);
    }

} catch (Exception $e) {
    // Database niet beschikbaar: check hardcoded
    if (in_array($code, $hardcoded)) {
        echo json_encode([
            'success' => true,
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
        echo json_encode(['success' => false, 'error' => 'Database niet beschikbaar en code niet herkend.']);
    }
}
