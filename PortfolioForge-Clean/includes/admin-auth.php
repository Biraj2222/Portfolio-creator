<?php
// includes/admin-auth.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function requireAdminLogin() {

    if (!isset($_SESSION['admin_id'])) {

        setFlash('danger', 'Admin authentication required.');

        header("Location: /PortfolioForge-Clean/admin/login.php");
        exit();
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT 
            admin_id,
            username,
            email
        FROM admins
        WHERE admin_id = ?
    ");

    $stmt->execute([$_SESSION['admin_id']]);

    $admin = $stmt->fetch();

    if (!$admin) {

        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);

        setFlash(
            'danger',
            'Session invalid. Please login again.'
        );

        header("Location: /PortfolioForge-Clean/admin/login.php");
        exit();
    }

    return $admin;
}