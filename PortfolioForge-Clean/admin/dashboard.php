<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/admin-auth.php';

$admin = requireAdminLogin();

$pdo = getDBConnection();

// Metrics calculations
$totalUsers = $pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

$activeUsers = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE status = 'active'"
)->fetchColumn();

$inactiveUsers = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE status = 'inactive'"
)->fetchColumn();

$totalPortfolios = $pdo->query(
    "SELECT COUNT(*) FROM portfolios"
)->fetchColumn();

$publishedPortfolios = $pdo->query(
    "SELECT COUNT(*) FROM portfolios WHERE status = 'published'"
)->fetchColumn();

$unpublishedPortfolios = $pdo->query(
    "SELECT COUNT(*) FROM portfolios WHERE status != 'published'"
)->fetchColumn();

$activeTemplates = $pdo->query(
    "SELECT COUNT(*) FROM templates WHERE is_active = 1"
)->fetchColumn();

$pageTitle = 'Admin Dashboard - Portfolio Forge';
$extraCss = 'dashboard.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">

    <!-- Admin Sidebar -->
   <?php require_once __DIR__ . '/../includes/admin-sidebar.php'; ?>


    <!-- Main Content -->
    <main class="dashboard-content">

        <div class="content-header">

            <div>

                <h1>
                    Admin System Overview
                </h1>

                <p style="color: var(--text-secondary);">
                    Real-time statistics for Portfolio Forge users, portfolios, and templates.
                </p>

            </div>

        </div>


        <!-- Metrics -->
        <div class="metrics-grid">

            <!-- Total Users -->
            <div class="metric-card">

                <div class="metric-label">
                    Total Registered Users
                </div>

                <div class="metric-value">
                    <?= number_format($totalUsers) ?>
                </div>

            </div>


            <!-- Active Users -->
            <div class="metric-card">

                <div class="metric-label">
                    Active Users
                </div>

                <div class="metric-value" style="color: #059669;">
                    <?= number_format($activeUsers) ?>
                </div>

            </div>


            <!-- Inactive Users -->
            <div class="metric-card">

                <div class="metric-label">
                    Inactive Users
                </div>

                <div class="metric-value" style="color: #dc2626;">
                    <?= number_format($inactiveUsers) ?>
                </div>

            </div>


            <!-- Total Portfolios -->
            <div class="metric-card">

                <div class="metric-label">
                    Total Portfolios
                </div>

                <div class="metric-value">
                    <?= number_format($totalPortfolios) ?>
                </div>

            </div>


            <!-- Published Portfolios -->
            <div class="metric-card">

                <div class="metric-label">
                    Published Portfolios
                </div>

                <div class="metric-value" style="color: #2563eb;">
                    <?= number_format($publishedPortfolios) ?>
                </div>

            </div>


            <!-- Unpublished Portfolios -->
            <div class="metric-card">

                <div class="metric-label">
                    Unpublished / Draft
                </div>

                <div class="metric-value" style="color: #d97706;">
                    <?= number_format($unpublishedPortfolios) ?>
                </div>

            </div>


            <!-- Active Templates -->
            <div class="metric-card">

                <div class="metric-label">
                    Active System Templates
                </div>

                <div class="metric-value">
                    <?= number_format($activeTemplates) ?>
                </div>

            </div>

        </div>


        <!-- System Management -->
        <div class="panel-card">

            <div class="panel-header">

                <h2 class="panel-title">
                    System Management
                </h2>

            </div>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">

                <a
                    href="/PortfolioForge-Clean/admin/users.php"
                    class="nav-btn btn-primary"
                >
                    👥 Manage User Accounts
                </a>

                <a
                    href="/PortfolioForge-Clean/admin/templates.php"
                    class="nav-btn btn-secondary"
                >
                    🎨 Manage System Templates
                </a>

            </div>

        </div>

    </main>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>