<?php
// ============================================================
//  API: GET /ticket overzicht/api/get_tickets.php
//  Geeft alle tickets terug voor een gebruiker (JSON)
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/database.php';

// Demo: gebruiker ID via GET of sessie
$gebruiker_id = isset($_GET['gebruiker_id']) ? (int)$_GET['gebruiker_id'] : 2;

try {
    $stmt = db()->prepare("
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
            r.aangemaakt_op
        FROM reserveringen r
        JOIN voorstellingen v ON v.id = r.voorstelling_id
        JOIN zalen z ON z.id = v.zaal_id
        WHERE r.gebruiker_id = :uid
        ORDER BY r.aangemaakt_op DESC
    ");
    $stmt->execute([':uid' => $gebruiker_id]);
    $tickets = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data'    => $tickets,
        'count'   => count($tickets),
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Database fout: ' . $e->getMessage(),
    ]);
}
