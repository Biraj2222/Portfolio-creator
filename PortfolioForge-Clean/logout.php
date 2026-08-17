<?php
// logout.php

require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear user session
unset($_SESSION['user_id']);
unset($_SESSION['username']);

// Clear admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

setFlash('info', 'You have been logged out successfully.');

header("Location: /PortfolioForge-Clean/login.php");
exit();