<?php
// includes/header.php
require_once __DIR__ . '/functions.php';

$currentUser = isset($_SESSION['user_id']) ? getCurrentUser() : null;
$isAdmin = isset($_SESSION['admin_id']);

$pageTitle = $pageTitle ?? 'Portfolio Forge - Dynamic Portfolio Builder';
$baseUrl = '/PortfolioForge-Clean';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= sanitize($pageTitle) ?></title>

    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">

    <?php if (isset($extraCss)): ?>
        <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/<?= sanitize($extraCss) ?>">
    <?php endif; ?>
</head>

<body>

    <header class="navbar-header">
        <div class="nav-container">

            <!-- Logo -->
            <a href="<?= $baseUrl ?>/" class="brand-logo">
                <span class="logo-icon">PF</span> Portfolio Forge
            </a>

            <nav class="main-nav">

                <?php if ($isAdmin): ?>

                    <!-- ADMIN NAVIGATION -->
                    <a href="<?= $baseUrl ?>/admin/dashboard.php"
                        class="nav-btn btn-secondary">
                        Admin Panel
                    </a>

                    <a href="<?= $baseUrl ?>/logout.php" class="nav-btn btn-outline">
                        Logout
                    </a>

                <?php elseif ($currentUser): ?>

                    <!-- USER NAVIGATION -->
                    <a href="<?= $baseUrl ?>/user/dashboard.php"
                        class="nav-btn btn-secondary">
                        Dashboard
                    </a>

                    <a href="<?= $baseUrl ?>/logout.php"
                        class="nav-btn btn-outline">
                        Logout
                    </a>

                <?php else: ?>

                    <!-- PUBLIC NAVIGATION -->
                    <a href="<?= $baseUrl ?>/#features">
                        Features
                    </a>

                    <a href="<?= $baseUrl ?>/#how-it-works">
                        How It Works
                    </a>

                    <a href="<?= $baseUrl ?>/#templates">
                        Templates
                    </a>

                    <a href="<?= $baseUrl ?>/login.php"
                        class="nav-btn btn-outline">
                        Login
                    </a>

                    <a href="<?= $baseUrl ?>/register.php"
                        class="nav-btn btn-primary">
                        Get Started
                    </a>

                <?php endif; ?>

            </nav>

        </div>
    </header>

    <main class="main-wrapper">

        <div class="flash-messages-container">
            <?php displayFlash(); ?>
        </div>