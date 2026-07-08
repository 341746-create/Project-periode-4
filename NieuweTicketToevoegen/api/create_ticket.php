<?php
// ============================================================
//  API: POST /NieuweTicketToevoegen/api/create_ticket.php
//  Maakt een nieuwe reservering aan in de database
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

// Input ophalen
$body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$naam            = isset($body['naam'])            ? trim($body['naam'])            : '';
$email           = isset($body['email'])           ? trim($body['email'])           : '';
$voorstelling_id = isset($body['voorstelling_id']) ? (int)$body['voorstelling_id'] : 0;
$aantal_stoelen  = isset($body['aantal_stoelen'])  ? (int)$body['aantal_stoelen']  : 1;
$betaalmethode   = isset($body['betaalmethode'])   ? trim($body['betaalmethode'])  : 'ideal';

// === Validatie ===
$fouten = [];

if (empty($naam) || strlen($naam) < 2) {
    $fouten[] = 'Naam is verplicht (minimaal 2 tekens).';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $fouten[] = 'Ongeldig e-mailadres.';
}
if ($voorstelling_id <= 0) {
    $fouten[] = 'Kies een geldige voorstelling.';
}
if ($aantal_stoelen < 1 || $aantal_stoelen > 10) {
    $fouten[] = 'Aantal stoelen moet tussen 1 en 10 zijn.';
}
$toegestane_methoden = ['ideal', 'creditcard', 'pin', 'contant'];
if (!in_array($betaalmethode, $toegestane_methoden)) {
    $fouten[] = 'Ongeldige betaalmethode.';
}

if (!empty($fouten)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode(' ', $fouten)]);
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    // 1. Haal voorstelling op + check beschikbaarheid
    $stmt = $pdo->prepare("
        SELECT id, titel, datum, aanvangstijd, prijs_per_stoel, beschikbare_stoelen, status
        FROM voorstellingen
        WHERE id = :id
        FOR UPDATE
    ");
    $stmt->execute([':id' => $voorstelling_id]);
    $voorstelling = $stmt->fetch();

    if (!$voorstelling) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Voorstelling niet gevonden.']);
        exit;
    }
    if ($voorstelling['status'] === 'geannuleerd') {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Deze voorstelling is geannuleerd.']);
        exit;
    }
    if ((int)$voorstelling['beschikbare_stoelen'] < $aantal_stoelen) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => "Niet genoeg beschikbare stoelen. Nog {$voorstelling['beschikbare_stoelen']} vrij.",
        ]);
        exit;
    }

    // 2. Zoek of maak gebruiker (klant)
    $userStmt = $pdo->prepare("SELECT id FROM gebruikers WHERE email = :email");
    $userStmt->execute([':email' => $email]);
    $gebruiker = $userStmt->fetch();

    if (!$gebruiker) {
        // Maak nieuwe klant aan met tijdelijk wachtwoord
        $temp_hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
        $insUser   = $pdo->prepare("
            INSERT INTO gebruikers (naam, email, wachtwoord_hash, rol)
            VALUES (:naam, :email, :hash, 'klant')
        ");
        $insUser->execute([':naam' => $naam, ':email' => $email, ':hash' => $temp_hash]);
        $gebruiker_id = (int)$pdo->lastInsertId();
    } else {
        $gebruiker_id = (int)$gebruiker['id'];
    }

    // 3. Bereken totaalprijs
    $totaalprijs = round((float)$voorstelling['prijs_per_stoel'] * $aantal_stoelen, 2);

    // 4. Maak reservering aan
    $insRes = $pdo->prepare("
        INSERT INTO reserveringen (gebruiker_id, voorstelling_id, aantal_stoelen, totaalprijs, status, betaalmethode, betaling_voltooid)
        VALUES (:uid, :vid, :stoelen, :prijs, 'bevestigd', :methode, 1)
    ");
    $insRes->execute([
        ':uid'     => $gebruiker_id,
        ':vid'     => $voorstelling_id,
        ':stoelen' => $aantal_stoelen,
        ':prijs'   => $totaalprijs,
        ':methode' => $betaalmethode,
    ]);
    $reservering_id = (int)$pdo->lastInsertId();

    // 5. Verminder beschikbare stoelen
    $updStoelen = $pdo->prepare("
        UPDATE voorstellingen
        SET beschikbare_stoelen = beschikbare_stoelen - :n1,
            status = CASE WHEN beschikbare_stoelen - :n2 <= 0 THEN 'uitverkocht' ELSE status END
        WHERE id = :id
    ");
    $updStoelen->execute([':n1' => $aantal_stoelen, ':n2' => $aantal_stoelen, ':id' => $voorstelling_id]);

    $pdo->commit();

    // Genereer ticket-code
    $ticket_code = sprintf('AUR-%d-%d', $reservering_id, $gebruiker_id);

    echo json_encode([
        'success'     => true,
        'message'     => 'Reservering succesvol aangemaakt.',
        'reservering' => [
            'id'            => $reservering_id,
            'ticket_code'   => $ticket_code,
            'voorstelling'  => $voorstelling['titel'],
            'datum'         => date('d M Y', strtotime($voorstelling['datum'])),
            'aanvangstijd'  => substr($voorstelling['aanvangstijd'], 0, 5),
            'aantal_stoelen'=> $aantal_stoelen,
            'totaalprijs'   => $totaalprijs,
            'betaalmethode' => $betaalmethode,
        ],
    ]);

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
