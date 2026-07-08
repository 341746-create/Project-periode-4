<?php
// ============================================================
//  Overzicht Voorstellingen — API
//  Geeft de voorstellingen die momenteel aanwezig zijn
//  (niet geannuleerd en datum vanaf vandaag) als JSON.
//  Verwijderen plaatst de show in de prullenbak (voorstellingen_trash)
//  zodat deze later via "Herstel voorstellingen" teruggehaald kan worden.
// ============================================================

require_once __DIR__ . '/../config/auth.php';
requireLogin(['admin', 'medewerker']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();

// Zorg dat de prullenbak-tabel bestaat
$pdo->exec("
    CREATE TABLE IF NOT EXISTS voorstellingen_trash (
        id                  INT UNSIGNED    NOT NULL,
        titel               VARCHAR(200)    NOT NULL,
        beschrijving        TEXT,
        categorie           ENUM('drama','musical','klassiek','comedy','dans','opera') NOT NULL DEFAULT 'drama',
        datum               DATE            NOT NULL,
        aanvangstijd        TIME            NOT NULL,
        duur_minuten        SMALLINT        NOT NULL DEFAULT 120,
        zaal_id             INT UNSIGNED    NOT NULL,
        prijs_per_stoel     DECIMAL(6,2)    NOT NULL,
        afbeelding_url      VARCHAR(500),
        beschikbare_stoelen SMALLINT        NOT NULL,
        status              ENUM('gepland','actief','uitverkocht','geannuleerd') NOT NULL DEFAULT 'gepland',
        aangemaakt_op       TIMESTAMP       NULL,
        verwijderd_op       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
");

// ── Herstel alles wat verwijderd is ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['actie'] ?? '') === 'herstel') {
    $pdo->prepare("
        INSERT INTO voorstellingen
            (titel, beschrijving, categorie, datum, aanvangstijd, duur_minuten,
             zaal_id, prijs_per_stoel, afbeelding_url, beschikbare_stoelen, status, aangemaakt_op)
        SELECT
            titel, beschrijving, categorie, datum, aanvangstijd, duur_minuten,
            zaal_id, prijs_per_stoel, afbeelding_url, beschikbare_stoelen, status, aangemaakt_op
        FROM voorstellingen_trash
    ")->execute();
    $pdo->exec("DELETE FROM voorstellingen_trash");

    echo json_encode(['success' => true]);
    exit;
}

// ── Verwijderen (naar prullenbak) ──
// Echte verwijdering uit de actieve tabel. De show wordt eerst gearchiveerd
// in voorstellingen_trash, gekoppelde reserveringen worden verwijderd (FK
// RESTRICT) en de bijbehorende stoelen vallen via CASCADE weg. Na een
// 'site reset' (php database/setup.php) worden de demo-voorstellingen
// opnieuw ingeladen en is de show ook weer terug.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verwijder_id'])) {
    $id = (int) $_POST['verwijder_id'];

    // Archiveer
    $pdo->prepare("
        INSERT INTO voorstellingen_trash
            (id, titel, beschrijving, categorie, datum, aanvangstijd, duur_minuten,
             zaal_id, prijs_per_stoel, afbeelding_url, beschikbare_stoelen, status, aangemaakt_op)
        SELECT
            id, titel, beschrijving, categorie, datum, aanvangstijd, duur_minuten,
            zaal_id, prijs_per_stoel, afbeelding_url, beschikbare_stoelen, status, aangemaakt_op
        FROM voorstellingen WHERE id = ?
    ")->execute([$id]);

    // Verwijder gekoppelde reserveringen, daarna de show (stoelen cascaden)
    $pdo->prepare("DELETE FROM reserveringen WHERE voorstelling_id = ?")
        ->execute([$id]);
    $pdo->prepare("DELETE FROM voorstellingen WHERE id = ?")
        ->execute([$id]);

    echo json_encode(['success' => true]);
    exit;
}

// ── Lijst van aanwezige voorstellingen ──
$stmt = $pdo->prepare("
    SELECT
        v.id,
        v.titel,
        v.datum,
        v.aanvangstijd AS tijd,
        z.naam         AS zaal
    FROM voorstellingen v
    JOIN zalen z ON z.id = v.zaal_id
    WHERE v.status <> 'geannuleerd'
      AND v.datum >= CURDATE()
    ORDER BY v.datum ASC, v.aanvangstijd ASC
");
$stmt->execute();
$voorstellingen = $stmt->fetchAll();

echo json_encode($voorstellingen, JSON_UNESCAPED_UNICODE);
