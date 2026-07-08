<?php
// ============================================================
//  Aurora Theater — Login handler (Feature-Inloggen)
//  Verwerkt het formulier van index.html en toont fouten
//  op dezelfde gestylde pagina via ?error.
// ============================================================
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password =        $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: index.html?error=1');
    exit;
}

try {
    $pdo  = db();
    $stmt = $pdo->prepare("SELECT * FROM gebruikers WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['wachtwoord_hash'])) {
        $expire = isset($_POST['remember']) ? time() + (86400 * 30) : 0;
        setcookie('gebruiker_id',   $user['id'],   $expire, '/');
        setcookie('gebruiker_naam', $user['naam'], $expire, '/');
        setcookie('gebruiker_rol',  $user['rol'],  $expire, '/');
        header('Location: /Homepaginamaken/index.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: index.html?error=2');
    exit;
}

header('Location: index.html?error=1');
exit;
