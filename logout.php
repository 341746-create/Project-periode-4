<?php
// ============================================================
//  Aurora Theater — Uitloggen
// ============================================================
setcookie('gebruiker_id',   '', time() - 3600, '/');
setcookie('gebruiker_naam', '', time() - 3600, '/');
setcookie('gebruiker_rol',  '', time() - 3600, '/');
header('Location: /Homepaginamaken/index.php');
exit;
