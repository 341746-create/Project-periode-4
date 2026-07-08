<?php
// ============================================================
//  API: POST /BestaandeTicketWijzigen/api/cancel_ticket.php
//  Annuleert een reservering, bevrijdt stoelen en logt dit.
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

$body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$reservering_id = isset($body['id']) ? (int)$body['id'] : 0;
$reden = isset($body['reden']) ? trim($body['reden']) : '';

if ($reservering_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ongeldig reservering ID.']);
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Controleer of reservering bestaat en annuleerbaar is
    $stmt = $pdo->prepare("SELECT * FROM reserveringen WHERE id = :id AND status NOT IN ('geannuleerd','voltooid')");
    $stmt->execute([':id' => $reservering_id]);
    $reservering = $stmt->fetch();

    if (!$reservering) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Reservering niet gevonden of al geannuleerd.']);
        exit;
    }

    // Stel reservering in op geannuleerd
    $upd = $pdo->prepare("
        UPDATE reserveringen
        SET status = 'geannuleerd', bijgewerkt_op = NOW()
        WHERE id = :id
    ");
    $upd->execute([':id' => $reservering_id]);

    // Herstel beschikbare stoelen bij de voorstelling
    $herstel = $pdo->prepare("
        UPDATE voorstellingen
        SET beschikbare_stoelen = beschikbare_stoelen + :n
        WHERE id = :vid
    ");
    $herstel->execute([
        ':n'   => (int)$reservering['aantal_stoelen'],
        ':vid' => (int)$reservering['voorstelling_id'],
    ]);

    // Stel gekoppelde stoelen terug op 'vrij'
    $vrij = $pdo->prepare("
        UPDATE stoelen s
        JOIN reservering_stoelen rs ON rs.stoel_id = s.id
        SET s.status = 'vrij'
        WHERE rs.reservering_id = :rid
    ");
    $vrij->execute([':rid' => $reservering_id]);

    // Log de annulering
    $detail = 'Reservering geannuleerd door gebruiker';
    if ($reden !== '') {
        $detail .= ' — reden: ' . $reden;
    }
    logWijziging($pdo, $reservering_id, 'geannuleerd', $detail);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reservering succesvol geannuleerd.',
        'id'      => $reservering_id,
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
