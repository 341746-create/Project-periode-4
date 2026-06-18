<?php
// ============================================================
//  Aurora Theater — Database configuratie
// ============================================================

define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'aurora_theater');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Geeft een PDO-instantie terug (singleton).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:unix_socket=%s;dbname=%s;charset=%s',
            '/opt/lampp/var/mysql/mysql.sock',
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new RuntimeException('Database verbinding mislukt: ' . $e->getMessage());
        }
    }

    return $pdo;
}
