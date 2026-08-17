<?php
// includes/auth.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function requireLogin() {

    if (!isset($_SESSION['user_id'])) {

        setFlash('danger', 'Please login to access this page.');

        header("Location: /PortfolioForge-Clean/login.php");
        exit();
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT 
            user_id,
            full_name,
            username,
            email,
            status,
            profile_image
        FROM users
        WHERE user_id = ?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {

        unset($_SESSION['user_id']);
        unset($_SESSION['username']);

        setFlash(
            'danger',
            'Your account is deactivated or no longer exists. Please contact an administrator.'
        );

        header("Location: /PortfolioForge-Clean/login.php");
        exit();
    }

    return $user;
}


function getCurrentUser() {

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT 
            user_id,
            full_name,
            username,
            email,
            status,
            profile_image
        FROM users
        WHERE user_id = ?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    return $stmt->fetch() ?: null;
}