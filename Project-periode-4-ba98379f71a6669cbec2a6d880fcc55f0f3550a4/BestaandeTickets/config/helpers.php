<?php
// ============================================================
//  Helper functies — Aurora Theater
// ============================================================

/**
 * Schrijft een wijziging weg in de wijzigingen_log tabel zodat
 * gebruikers hun wijzigingsgeschiedenis kunnen terugzien.
 */
function logWijziging(PDO $pdo, int $reservering_id, string $type, string $detail): void
{
    $stmt = $pdo->prepare("
        INSERT INTO wijzigingen_log (reservering_id, type, detail, gewijzigd_op)
        VALUES (:rid, :type, :detail, NOW())
    ");
    $stmt->execute([
        ':rid'   => $reservering_id,
        ':type'  => $type,
        ':detail'=> $detail,
    ]);
}
