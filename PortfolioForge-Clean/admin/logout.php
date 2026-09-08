<?php
// admin/logout.php
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

setFlash('info', 'Admin logged out successfully.');
header("Location: /admin/login.php");
exit();

