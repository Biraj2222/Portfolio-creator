<?php
// user/statistics.php

require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$pdo = getDBConnection();

$portfolio = getOrCreateUserPortfolio(
    $user['user_id'],
    $pdo
);

$pId = $portfolio['portfolio_id'];


// =====================================================
// TOTAL PORTFOLIO VIEWS
// =====================================================

$vTotalStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM portfolio_visits
    WHERE portfolio_id = ?
");

$vTotalStmt->execute([$pId]);

$totalViews = (int)$vTotalStmt->fetchColumn();


// =====================================================
// TODAY'S VIEWS
// =====================================================

$vTodayStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM portfolio_visits
    WHERE portfolio_id = ?
    AND DATE(visited_at) = CURDATE()
");

$vTodayStmt->execute([$pId]);

$todayViews = (int)$vTodayStmt->fetchColumn();


// =====================================================
// LAST 7 DAYS VIEWS
// =====================================================

$vWeekStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM portfolio_visits
    WHERE portfolio_id = ?
    AND visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");

$vWeekStmt->execute([$pId]);

$weekViews = (int)$vWeekStmt->fetchColumn();


// =====================================================
// RECENT PUBLIC VISITS
// =====================================================

$recentStmt = $pdo->prepare("
    SELECT visited_at
    FROM portfolio_visits
    WHERE portfolio_id = ?
    ORDER BY visited_at DESC
    LIMIT 15
");

$recentStmt->execute([$pId]);

$recentVisits = $recentStmt->fetchAll();


// =====================================================
// PAGE SETUP
// =====================================================

$pageTitle = 'Portfolio Statistics - Portfolio Forge';

$extraCss = 'dashboard.css';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="dashboard-layout">

    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside class="sidebar">

        <div class="sidebar-heading">
            User Dashboard
        </div>

        <ul class="sidebar-menu">

            <li>
                <a href="/PortfolioForge-Clean/user/dashboard.php">
                    📊 Overview
                </a>
            </li>

            <li>
                <a href="/PortfolioForge-Clean/user/edit-portfolio.php">
                    ✏️ Edit Portfolio
                </a>
            </li>

            <li>
                <a href="/PortfolioForge-Clean/user/sections.php">
                    🧩 Sections
                </a>
            </li>

            <li>
                <a href="/PortfolioForge-Clean/user/resume.php">
                    📄 Resume Upload
                </a>
            </li>

            <li>
                <a href="/PortfolioForge-Clean/user/templates.php">
                    🎨 Templates
                </a>
            </li>

            <li>
                <a href="/PortfolioForge-Clean/user/statistics.php"
                   class="active">
                    📈 Statistics
                </a>
            </li>

            <li>
                <a href="/PortfolioForge-Clean/user/profile.php">
                    ⚙️ Profile Settings
                </a>
            </li>

        </ul>

    </aside>


    <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="dashboard-content">

        <div class="content-header">

            <div>

                <h1>
                    Portfolio View Analytics
                </h1>

                <p style="color: var(--text-secondary);">
                    Track visits to your public portfolio.
                    Owner previews are not included in these statistics.
                </p>

            </div>

        </div>


        <!-- =================================================
             STATISTICS CARDS
        ================================================== -->

        <div class="metrics-grid">

            <!-- TOTAL -->

            <div class="metric-card">

                <div class="metric-label">
                    Total Portfolio Views
                </div>

                <div class="metric-value">
                    <?= number_format($totalViews) ?>
                </div>

            </div>


            <!-- TODAY -->

            <div class="metric-card">

                <div class="metric-label">
                    Views Today
                </div>

                <div class="metric-value">
                    <?= number_format($todayViews) ?>
                </div>

            </div>


            <!-- WEEK -->

            <div class="metric-card">

                <div class="metric-label">
                    Views This Week
                </div>

                <div class="metric-value">
                    <?= number_format($weekViews) ?>
                </div>

            </div>

        </div>


        <!-- =================================================
             RECENT VISITS
        ================================================== -->

        <div class="panel-card">

            <h2 class="panel-title" style="margin-bottom: 1rem;">
                Recent Public Visits
            </h2>


            <?php if (empty($recentVisits)): ?>

                <p style="color: var(--text-secondary);">

                    No public views recorded yet.

                    <br>

                    Share your portfolio link to start receiving visitors.

                </p>


            <?php else: ?>


                <table class="data-table">

                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Visit Date & Time
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($recentVisits as $index => $visit): ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>

                                <td>
                                    <?= sanitize($visit['visited_at']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>


            <?php endif; ?>

        </div>

    </main>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>