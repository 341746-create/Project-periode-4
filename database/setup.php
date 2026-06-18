#!/usr/bin/env php
<?php
// ============================================================
//  Aurora Theater — Database setup script
//  Gebruik: php database/setup.php
//           php database/setup.php --user=root --pass=geheim
// ============================================================

// --- Argument parsing ---
$opts = getopt('', ['user:', 'pass:', 'host:', 'port:']);
$host = $opts['host'] ?? 'localhost';
$port = $opts['port'] ?? '3306';
$user = $opts['user'] ?? 'root';
$pass = $opts['pass'] ?? '';

echo "\n🎭  Aurora Theater — Database Setup\n";
echo str_repeat('─', 40) . "\n\n";

// --- Verbinding zonder database selectie ---
$dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅  Verbinding met MySQL gelukt\n";
} catch (PDOException $e) {
    echo "❌  Verbinding mislukt: " . $e->getMessage() . "\n\n";
    echo "Gebruik: php database/setup.php --user=<gebruiker> --pass=<wachtwoord>\n\n";
    exit(1);
}

// --- SQL-bestand uitvoeren ---
$sqlFile = __DIR__ . '/init.sql';
if (!file_exists($sqlFile)) {
    echo "❌  init.sql niet gevonden op: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);

// Splits op puntkomma (eenvoudige splitter)
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($s) => $s !== '' && !str_starts_with($s, '--')
);

$ok = 0;
$fail = 0;
foreach ($statements as $stmt) {
    try {
        $pdo->exec($stmt);
        $ok++;
    } catch (PDOException $e) {
        // Sla ER_DUP_ENTRY en EXISTS-fouten over (idempotent)
        if (in_array($e->getCode(), ['23000', '42S01'])) {
            continue;
        }
        echo "⚠️   Waarschuwing: " . $e->getMessage() . "\n";
        $fail++;
    }
}

echo "✅  $ok statements uitgevoerd";
if ($fail > 0) echo ", $fail overgeslagen";
echo "\n\n";

// --- Maak aurora_user aan (optioneel) ---
echo "👤  Aanmaken van database-gebruiker 'aurora_user'...\n";
try {
    $pdo->exec("CREATE USER IF NOT EXISTS 'aurora_user'@'localhost' IDENTIFIED BY 'AuroraPass2026!'");
    $pdo->exec("GRANT ALL PRIVILEGES ON aurora_theater.* TO 'aurora_user'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "✅  Gebruiker 'aurora_user' aangemaakt en rechten toegewezen\n";
} catch (PDOException $e) {
    echo "⚠️   Gebruiker aanmaken overgeslagen: " . $e->getMessage() . "\n";
}

// --- Samenvatting ---
echo "\n" . str_repeat('─', 40) . "\n";
echo "🎉  Database 'aurora_theater' is klaar!\n\n";

// Toon tabel-overzicht
$pdo->exec("USE aurora_theater");
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "📋  Tabellen aangemaakt:\n";
foreach ($tables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "    • $t ($count rijen)\n";
}

echo "\n✨  Klaar! Pas config/database.php aan indien nodig.\n\n";
