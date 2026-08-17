<?php
// portfolio/view.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$isPreview = isset($_GET['is_preview']) && $_GET['is_preview'] === 'true';

if (empty($slug)) {
    http_response_code(404);
    die("<h2 style='text-align:center; margin-top:3rem; color:#334155;'>Portfolio Slug Not Specified</h2>");
}

$pdo = getDBConnection();

$pStmt = $pdo->prepare("
    SELECT 
        p.*,
        u.full_name,
        u.username,
        u.email,
        u.profile_image,
        u.status AS user_status,
        t.slug AS template_slug
    FROM portfolios p
    JOIN users u ON p.user_id = u.user_id
    JOIN templates t ON p.template_id = t.template_id
    WHERE p.portfolio_slug = ?
");

$pStmt->execute([$slug]);
$portfolio = $pStmt->fetch();

if (!$portfolio) {
    http_response_code(404);
    $pageTitle = "Portfolio Not Found";

    require_once __DIR__ . '/../includes/header.php';

    echo "
        <div style='text-align:center; padding:5rem 1.5rem;'>
            <h1 style='font-size:2rem; color:var(--secondary-color); margin-bottom:1rem;'>
                Portfolio Unavailable
            </h1>

            <p style='color:var(--text-secondary);'>
                The portfolio with slug <strong>" . sanitize($slug) . "</strong> does not exist.
            </p>

            <a href='/PortfolioForge-Clean/' class='nav-btn btn-primary'
               style='margin-top:1.5rem; display:inline-block;'>
                Return to Home
            </a>
        </div>
    ";

    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

/*
 * Check whether the portfolio owner is active.
 */
if ($portfolio['user_status'] !== 'active') {
    http_response_code(403);
    $pageTitle = "Portfolio Inactive";

    require_once __DIR__ . '/../includes/header.php';

    echo "
        <div style='text-align:center; padding:5rem 1.5rem;'>
            <h1 style='font-size:2rem; color:var(--danger-color); margin-bottom:1rem;'>
                Portfolio Temporarily Unavailable
            </h1>

            <p style='color:var(--text-secondary);'>
                The user account associated with this portfolio is currently inactive.
            </p>

            <a href='/PortfolioForge-Clean/' class='nav-btn btn-primary'
               style='margin-top:1.5rem; display:inline-block;'>
                Return to Home
            </a>
        </div>
    ";

    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

/*
 * Preview security.
 *
 * Preview is allowed only when:
 * 1. The preview flag is present.
 * 2. A user is logged in.
 * 3. The logged-in user owns this portfolio.
 */
if ($isPreview) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (
        !isset($_SESSION['user_id']) ||
        (int)$_SESSION['user_id'] !== (int)$portfolio['user_id']
    ) {
        http_response_code(403);

        $pageTitle = "Preview Not Allowed";

        require_once __DIR__ . '/../includes/header.php';

        echo "
            <div style='text-align:center; padding:5rem 1.5rem;'>
                <h1 style='font-size:2rem; color:var(--danger-color); margin-bottom:1rem;'>
                    Preview Not Allowed
                </h1>

                <p style='color:var(--text-secondary);'>
                    You can only preview your own portfolio.
                </p>

                <a href='/PortfolioForge-Clean/login.php'
                   class='nav-btn btn-primary'
                   style='margin-top:1.5rem; display:inline-block;'>
                    Login
                </a>
            </div>
        ";

        require_once __DIR__ . '/../includes/footer.php';
        exit();
    }
}

/*
 * Normal public viewing.
 *
 * Draft/unpublished portfolios cannot be viewed publicly.
 */
if (!$isPreview && $portfolio['status'] !== 'published') {
    http_response_code(403);
    $pageTitle = "Portfolio Not Published";

    require_once __DIR__ . '/../includes/header.php';

    echo "
        <div style='text-align:center; padding:5rem 1.5rem;'>
            <h1 style='font-size:2rem; color:var(--warning-color); margin-bottom:1rem;'>
                Portfolio Draft
            </h1>

            <p style='color:var(--text-secondary);'>
                This portfolio is currently not published for public viewing.
            </p>

            <a href='/PortfolioForge-Clean/'
               class='nav-btn btn-primary'
               style='margin-top:1.5rem; display:inline-block;'>
                Return to Home
            </a>
        </div>
    ";

    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

/*
 * Record public visits only.
 * Owner previews do not count.
 */
if (!$isPreview) {

    $vStmt = $pdo->prepare("
        INSERT INTO portfolio_visits (portfolio_id)
        VALUES (?)
    ");

    $vStmt->execute([
        $portfolio['portfolio_id']
    ]);
}

/*
 * Fetch visible sections.
 */
$sStmt = $pdo->prepare("
    SELECT *
    FROM portfolio_sections
    WHERE portfolio_id = ?
      AND is_visible = 1
    ORDER BY display_order ASC, section_id ASC
");

$sStmt->execute([
    $portfolio['portfolio_id']
]);

$sections = $sStmt->fetchAll();

/*
 * Fetch resume.
 */
$rStmt = $pdo->prepare("
    SELECT *
    FROM resume
    WHERE portfolio_id = ?
");

$rStmt->execute([
    $portfolio['portfolio_id']
]);

$resume = $rStmt->fetch();

/*
 * User information available to templates.
 */
$user = [
    'full_name' => $portfolio['full_name'],
    'username' => $portfolio['username'],
    'email' => $portfolio['email'],
    'profile_image' => $portfolio['profile_image']
];

$pageTitle = $portfolio['title'] . " - " . $portfolio['full_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title><?= sanitize($pageTitle) ?></title>

    <link
        rel="stylesheet"
        href="/PortfolioForge-Clean/assets/css/style.css">

    <link
        rel="stylesheet"
        href="/PortfolioForge-Clean/assets/css/templates.css">

</head>

<body>

    <?php if ($isPreview): ?>

        <div style="
        background-color:#fef3c7;
        color:#92400e;
        padding:0.75rem;
        text-align:center;
        font-weight:600;
        border-bottom:1px solid #fde68a;
    ">

            Owner Preview Mode

            <span style="font-weight:400;">
                This visit does not count toward your public view statistics.
            </span>

            <a
                href="/PortfolioForge-Clean/user/dashboard.php"
                style="
                margin-left:1rem;
                color:#92400e;
                text-decoration:underline;
            ">
                Back to Dashboard
            </a>

        </div>

    <?php endif; ?>


    <?php

    $templateFolder = $portfolio['template_slug'] ?? 'modern';

    $templateFile =
        __DIR__ . "/../templates/{$templateFolder}/view.php";

    if (file_exists($templateFile)) {

        require $templateFile;
    } else {

        require __DIR__ . "/../templates/modern/view.php";
    }

    ?>

</body>

</html>