<?php
// user/dashboard.php
require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$pdo = getDBConnection();
$portfolio = getOrCreateUserPortfolio($user['user_id'], $pdo);
$baseUrl = '/PortfolioForge-Clean';

$pId = $portfolio['portfolio_id'];

// Views count
$vTotalStmt = $pdo->prepare("SELECT COUNT(*) FROM portfolio_visits WHERE portfolio_id = ?");
$vTotalStmt->execute([$pId]);
$totalViews = $vTotalStmt->fetchColumn();

$vTodayStmt = $pdo->prepare("SELECT COUNT(*) FROM portfolio_visits WHERE portfolio_id = ? AND DATE(visited_at) = CURDATE()");
$vTodayStmt->execute([$pId]);
$todayViews = $vTodayStmt->fetchColumn();

$vWeekStmt = $pdo->prepare("SELECT COUNT(*) FROM portfolio_visits WHERE portfolio_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$vWeekStmt->execute([$pId]);
$weekViews = $vWeekStmt->fetchColumn();

// Template name
$tStmt = $pdo->prepare("SELECT template_name FROM templates WHERE template_id = ?");
$tStmt->execute([$portfolio['template_id']]);
$templateName = $tStmt->fetchColumn() ?: 'Default';

// Resume status
$rStmt = $pdo->prepare("SELECT * FROM resume WHERE portfolio_id = ?");
$rStmt->execute([$pId]);
$resume = $rStmt->fetch();

// Portfolio actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid CSRF token.');
        header("Location: {$baseUrl}/user/dashboard.php");
        exit();
    }

    $action = $_POST['action'];

    if ($action === 'publish') {

        $uStmt = $pdo->prepare("UPDATE portfolios SET status = 'published' WHERE portfolio_id = ?");
        $uStmt->execute([$pId]);

        setFlash('success', 'Your portfolio is now published and accessible publicly!');

    } elseif ($action === 'unpublish') {

        $uStmt = $pdo->prepare("UPDATE portfolios SET status = 'unpublished' WHERE portfolio_id = ?");
        $uStmt->execute([$pId]);

        setFlash('warning', 'Your portfolio has been unpublished.');

    } elseif ($action === 'delete_portfolio') {

        $delSecs = $pdo->prepare("DELETE FROM portfolio_sections WHERE portfolio_id = ?");
        $delSecs->execute([$pId]);

        $uStmt = $pdo->prepare("UPDATE portfolios SET status = 'draft', title = ? WHERE portfolio_id = ?");
        $uStmt->execute([$user['full_name'] . "'s Portfolio", $pId]);

        setFlash('success', 'Portfolio content reset successfully.');
    }

    header("Location: {$baseUrl}/user/dashboard.php");
    exit();
}

$pageTitle = 'Dashboard - Portfolio Forge';
$extraCss = 'dashboard.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">

    <aside class="sidebar">

        <div class="sidebar-heading">
            User Dashboard
        </div>

        <ul class="sidebar-menu">

            <li>
                <a href="<?= $baseUrl ?>/user/dashboard.php" class="active">
                    📊 Overview
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/edit-portfolio.php">
                    ✏️ Edit Portfolio
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/sections.php">
                    🧩 Sections
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/resume.php">
                    📄 Resume Upload
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/templates.php">
                    🎨 Templates
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/statistics.php">
                    📈 Statistics
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/profile.php">
                    ⚙️ Profile Settings
                </a>
            </li>

        </ul>

    </aside>

    <main class="dashboard-content">

        <div class="content-header">

            <div>
                <h1>Welcome, <?= sanitize($user['full_name']) ?>!</h1>

                <p style="color: var(--text-secondary);">
                    Manage your online portfolio, template styles, and settings.
                </p>
            </div>

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">

                <a
                    href="<?= $baseUrl ?>/user/preview.php"
                    target="_blank"
                    class="nav-btn btn-outline"
                >
                    👁️ Owner Preview
                </a>

                <?php if ($portfolio['status'] === 'published'): ?>

                    <form
                        action="<?= $baseUrl ?>/user/dashboard.php"
                        method="POST"
                        style="display: inline;"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= generateCsrfToken() ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="unpublish"
                        >

                        <button
                            type="submit"
                            class="nav-btn btn-danger"
                        >
                            Unpublish Portfolio
                        </button>
                    </form>

                <?php else: ?>

                    <form
                        action="<?= $baseUrl ?>/user/dashboard.php"
                        method="POST"
                        style="display: inline;"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= generateCsrfToken() ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="publish"
                        >

                        <button
                            type="submit"
                            class="nav-btn btn-primary"
                        >
                            🚀 Publish Portfolio
                        </button>
                    </form>

                <?php endif; ?>

            </div>

        </div>

        <div class="metrics-grid">

            <div class="metric-card">

                <div class="metric-label">
                    Portfolio Status
                </div>

                <div style="margin-top: 0.5rem;">

                    <?php if ($portfolio['status'] === 'published'): ?>

                        <span
                            class="badge badge-success"
                            style="font-size: 1rem;"
                        >
                            Published
                        </span>

                    <?php elseif ($portfolio['status'] === 'unpublished'): ?>

                        <span
                            class="badge badge-warning"
                            style="font-size: 1rem;"
                        >
                            Unpublished
                        </span>

                    <?php else: ?>

                        <span
                            class="badge badge-info"
                            style="font-size: 1rem;"
                        >
                            Draft
                        </span>

                    <?php endif; ?>

                </div>

            </div>

            <div class="metric-card">

                <div class="metric-label">
                    Selected Template
                </div>

                <div
                    class="metric-value"
                    style="font-size: 1.5rem;"
                >
                    <?= sanitize($templateName) ?>
                </div>

            </div>

            <div class="metric-card">

                <div class="metric-label">
                    Total Public Views
                </div>

                <div class="metric-value">
                    <?= number_format($totalViews) ?>
                </div>

            </div>

            <div class="metric-card">

                <div class="metric-label">
                    Resume Uploaded
                </div>

                <div style="margin-top: 0.5rem;">

                    <?php if ($resume): ?>

                        <span
                            class="badge badge-success"
                            style="font-size: 0.9rem;"
                        >
                            Uploaded
                        </span>

                    <?php else: ?>

                        <span
                            class="badge badge-warning"
                            style="font-size: 0.9rem;"
                        >
                            None
                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <?php if ($portfolio['status'] === 'published'): ?>

            <div
                class="panel-card"
                style="background-color: #f0fdf4; border-color: #bbf7d0;"
            >

                <div
                    class="panel-title"
                    style="
                        color: #166534;
                        font-size: 1.1rem;
                        margin-bottom: 0.5rem;
                    "
                >
                    🌐 Live Portfolio URL
                </div>

                <p
                    style="
                        color: #15803d;
                        font-size: 0.95rem;
                        margin-bottom: 0.75rem;
                    "
                >
                    Your portfolio is published and available at:
                </p>

                <div
                    style="
                        display: flex;
                        gap: 0.5rem;
                        align-items: center;
                    "
                >

                    <input
                        type="text"
                        readonly
                        class="form-control"
                        value="http://<?= sanitize($_SERVER['HTTP_HOST']) ?><?= $baseUrl ?>/portfolio/<?= sanitize($portfolio['portfolio_slug']) ?>"
                        style="background: #fff;"
                    >

                    <a
                        href="<?= $baseUrl ?>/portfolio/<?= sanitize($portfolio['portfolio_slug']) ?>"
                        target="_blank"
                        class="nav-btn btn-primary"
                    >
                        Open Link
                    </a>

                </div>

            </div>

        <?php endif; ?>

        <div class="panel-card">

            <div class="panel-header">
                <h2 class="panel-title">Quick Actions</h2>
            </div>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">

                <a
                    href="<?= $baseUrl ?>/user/edit-portfolio.php"
                    class="nav-btn btn-outline"
                >
                    ✏️ Edit Information
                </a>

                <a
                    href="<?= $baseUrl ?>/user/sections.php"
                    class="nav-btn btn-outline"
                >
                    🧩 Manage Sections
                </a>

                <a
                    href="<?= $baseUrl ?>/user/resume.php"
                    class="nav-btn btn-outline"
                >
                    📄 Upload / Extract Resume
                </a>

                <a
                    href="<?= $baseUrl ?>/user/templates.php"
                    class="nav-btn btn-outline"
                >
                    🎨 Switch Template
                </a>

                <a
                    href="<?= $baseUrl ?>/user/statistics.php"
                    class="nav-btn btn-outline"
                >
                    📈 View Analytics
                </a>

            </div>

        </div>

        <div
            class="panel-card"
            style="border-color: #fca5a5;"
        >

            <div class="panel-header">

                <h2
                    class="panel-title"
                    style="color: var(--danger-color);"
                >
                    Danger Zone
                </h2>

            </div>

            <p
                style="
                    color: var(--text-secondary);
                    font-size: 0.95rem;
                    margin-bottom: 1rem;
                "
            >
                Reset your portfolio content back to initial draft state.
                This will erase custom sections but keep your user account intact.
            </p>

            <form
                action="<?= $baseUrl ?>/user/dashboard.php"
                method="POST"
                onsubmit="return confirm('Are you sure you want to reset your portfolio data? This action cannot be undone.');"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="delete_portfolio"
                >

                <button
                    type="submit"
                    class="nav-btn btn-danger"
                >
                    Reset Portfolio Content
                </button>

            </form>

        </div>

    </main>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>