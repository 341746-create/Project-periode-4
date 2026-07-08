<?php
// ============================================================
//  API: POST /BestaandeTicketWijzigen/api/update_ticket.php
//  Werkt een reservering bij (stoelen, betaalmethode)
//  en logt de wijziging in wijzigingen_log.
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

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

    // Bereken nieuwe waarden
    $nieuw_stoelen = $aantal_stoelen ?? (int)$reservering['aantal_stoelen'];
    if ($nieuw_stoelen < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Aantal stoelen moet minimaal 1 zijn.']);
        exit;
    }

    $prijs_stmt = $pdo->prepare("SELECT prijs_per_stoel, beschikbare_stoelen FROM voorstellingen WHERE id = :id");
    $prijs_stmt->execute([':id' => $reservering['voorstelling_id']]);
    $voorstelling = $prijs_stmt->fetch();
    $prijs_per_stoel = (float)$voorstelling['prijs_per_stoel'];

    // Controleer beschikbaarheid wanneer er stoelen bijkomen
    $verschil = $nieuw_stoelen - (int)$reservering['aantal_stoelen'];
    if ($verschil > 0 && (int)$voorstelling['beschikbare_stoelen'] < $verschil) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Niet genoeg beschikbare stoelen voor deze wijziging.']);
        exit;
    }

    $nieuwe_prijs = $prijs_per_stoel * $nieuw_stoelen;

    $pdo->beginTransaction();

    // Update reservering
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

    // Pas het aantal beschikbare stoelen bij de voorstelling aan
    if ($verschil !== 0) {
        $stoel = $pdo->prepare("
            UPDATE voorstellingen
            SET beschikbare_stoelen = beschikbare_stoelen - :d
            WHERE id = :vid
        ");
        $stoel->execute([
            ':d'   => $verschil,
            ':vid' => (int)$reservering['voorstelling_id'],
        ]);
    }

    // Log de wijziging
    $detail = sprintf(
        'Stoelen: %d → %d | Totaal: €%.2f → €%.2f%s',
        (int)$reservering['aantal_stoelen'],
        $nieuw_stoelen,
        (float)$reservering['totaalprijs'],
        $nieuwe_prijs,
        $betaalmethode ? ' | Betaalmethode: ' . ($betaalmethode ?? $reservering['betaalmethode']) : ''
    );
    logWijziging($pdo, $reservering_id, 'bewerkt', $detail);

    $pdo->commit();

    echo json_encode([
        'success'        => true,
        'message'        => 'Reservering succesvol bijgewerkt.',
        'id'             => $reservering_id,
        'aantal_stoelen' => $nieuw_stoelen,
        'totaalprijs'    => $nieuwe_prijs,
        'betaalmethode'  => $betaalmethode ?? $reservering['betaalmethode'],
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
