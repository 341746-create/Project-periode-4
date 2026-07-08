<?php
// ============================================================
//  Database configuratie — Aurora Theater
// ============================================================

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host = '127.0.0.1';
    $db   = 'aurora_theater';
    $user = 'aurora';
    $pass = 'Aurora2026!';

    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}