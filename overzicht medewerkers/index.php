<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin(['admin', 'medewerker']);
readfile(__DIR__ . '/index.html');
exit;
