<?php
// ============================================================
//  Aurora Theater — Database configuratie
// ============================================================

define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'aurora_theater');
define('DB_USER',    'aurora_user');
define('DB_PASS',    'AuroraPass2026!');
define('DB_CHARSET', 'utf8mb4');

/**
 * Geeft een PDO-instantie terug (singleton).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                'error'  => 'Database verbinding mislukt.',
                'detail' => $e->getMessage()
            ]));
        }
    }

    return $pdo;
}
