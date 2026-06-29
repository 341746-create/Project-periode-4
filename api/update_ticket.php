<?php
// ============================================================
//  API: POST /BestaandeTicketWijzigen/api/update_ticket.php
//  Werkt een reservering bij (stoelen, betaalmethode)
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Alleen POST toegestaan.']);
    exit;
}

// Input ophalen (JSON body of form-data)
$body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$reservering_id  = isset($body['id'])             ? (int)$body['id']                   : 0;
$aantal_stoelen  = isset($body['aantal_stoelen']) ? (int)$body['aantal_stoelen']        : null;
$betaalmethode   = isset($body['betaalmethode'])  ? trim($body['betaalmethode'])        : null;

// Validatie
if ($reservering_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ongeldig reservering ID.']);
    exit;
}

$toegestane_methoden = ['ideal', 'creditcard', 'pin', 'contant'];
if ($betaalmethode && !in_array($betaalmethode, $toegestane_methoden)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ongeldige betaalmethode.']);
    exit;
}

try {
    $pdo = db();

    // Haal reservering op
    $stmt = $pdo->prepare("SELECT * FROM reserveringen WHERE id = :id AND status NOT IN ('geannuleerd', 'voltooid')");
    $stmt->execute([':id' => $reservering_id]);
    $reservering = $stmt->fetch();

    if (!$reservering) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Reservering niet gevonden of kan niet worden bewerkt.']);
        exit;
    }

    // Bereken nieuwe prijs als stoelen veranderen
    $nieuw_stoelen = $aantal_stoelen ?? (int)$reservering['aantal_stoelen'];
    $prijs_stmt = $pdo->prepare("SELECT prijs_per_stoel FROM voorstellingen WHERE id = :id");
    $prijs_stmt->execute([':id' => $reservering['voorstelling_id']]);
    $prijs_per_stoel = (float)$prijs_stmt->fetchColumn();
    $nieuwe_prijs = $prijs_per_stoel * $nieuw_stoelen;

    // Update
    $upd = $pdo->prepare("
        UPDATE reserveringen
        SET aantal_stoelen = :stoelen,
            betaalmethode  = :methode,
            totaalprijs    = :prijs,
            bijgewerkt_op  = NOW()
        WHERE id = :id
    ");
    $upd->execute([
        ':stoelen' => $nieuw_stoelen,
        ':methode' => $betaalmethode ?? $reservering['betaalmethode'],
        ':prijs'   => $nieuwe_prijs,
        ':id'      => $reservering_id,
    ]);

    echo json_encode([
        'success'        => true,
        'message'        => 'Reservering succesvol bijgewerkt.',
        'id'             => $reservering_id,
        'aantal_stoelen' => $nieuw_stoelen,
        'totaalprijs'    => $nieuwe_prijs,
        'betaalmethode'  => $betaalmethode ?? $reservering['betaalmethode'],
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
