
<?php

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT password FROM admins WHERE username = ?");
$stmt->execute(['biraj']);

$admin = $stmt->fetch();

if (!$admin) {
    die('ADMIN NOT FOUND');
}

if (password_verify('Biraj@123', $admin['password'])) {
    echo 'PASSWORD MATCHES';
} else {
    echo 'PASSWORD DOES NOT MATCH';
}