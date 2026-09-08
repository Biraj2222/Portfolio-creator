<?php
// user/edit-portfolio.php
require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();
$pdo = getDBConnection();

$baseUrl = '/PortfolioForge-Clean';
$portfolio = getOrCreateUserPortfolio($user['user_id'], $pdo);
$pId = $portfolio['portfolio_id'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    }

    $title = trim($_POST['title'] ?? '');
    $slug = generateSlug($_POST['portfolio_slug'] ?? '');
    $accentColor = trim($_POST['accent_color'] ?? '#2563eb');
    $fontFamily = trim($_POST['font_family'] ?? 'Inter, sans-serif');

    $showProfileImage = isset($_POST['show_profile_image']) ? 1 : 0;
    $showEmail = isset($_POST['show_email']) ? 1 : 0;
    $showPhone = isset($_POST['show_phone']) ? 1 : 0;
    $showLocation = isset($_POST['show_location']) ? 1 : 0;

    if (empty($title)) {
        $errors[] = 'Portfolio Title is required.';
    }

    if (empty($slug)) {
        $errors[] = 'Portfolio Slug is required.';
    }

    // Check whether the personalized URL is already being used
    if (empty($errors)) {
        $sStmt = $pdo->prepare("
            SELECT portfolio_id
            FROM portfolios
            WHERE portfolio_slug = ?
            AND portfolio_id != ?
        ");

        $sStmt->execute([$slug, $pId]);

        if ($sStmt->fetch()) {
            $errors[] = 'That personalized URL slug is already taken. Please choose another.';
        }
    }

    // Handle profile picture upload
    if (
        empty($errors) &&
        isset($_FILES['profile_image']) &&
        $_FILES['profile_image']['error'] === UPLOAD_ERR_OK
    ) {
        $uploadResult = uploadFile(
            $_FILES['profile_image'],
            ['image/jpeg', 'image/png', 'image/webp'],
            ['jpg', 'jpeg', 'png', 'webp'],
            2 * 1024 * 1024,
            __DIR__ . '/../uploads/profiles'
        );

        if ($uploadResult['success']) {
            $uImgStmt = $pdo->prepare("
                UPDATE users
                SET profile_image = ?
                WHERE user_id = ?
            ");

            $uImgStmt->execute([
                $uploadResult['filename'],
                $user['user_id']
            ]);
        } else {
            $errors[] = 'Profile Picture Error: ' . $uploadResult['error'];
        }
    }

    if (empty($errors)) {
        $uPort = $pdo->prepare("
            UPDATE portfolios
            SET
                title = ?,
                portfolio_slug = ?,
                accent_color = ?,
                font_family = ?,
                show_profile_image = ?,
                show_email = ?,
                show_phone = ?,
                show_location = ?
            WHERE portfolio_id = ?
        ");

        $uPort->execute([
            $title,
            $slug,
            $accentColor,
            $fontFamily,
            $showProfileImage,
            $showEmail,
            $showPhone,
            $showLocation,
            $pId
        ]);

        setFlash('success', 'Portfolio settings updated successfully.');

        header("Location: {$baseUrl}/user/edit-portfolio.php");
        exit();
    }
}

// Refresh user and portfolio data
$user = getCurrentUser();
$portfolio = getOrCreateUserPortfolio($user['user_id'], $pdo);

$pageTitle = 'Edit Portfolio - Portfolio Forge';
$extraCss = 'dashboard.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">

    <aside class="sidebar">
        <div class="sidebar-heading">User Dashboard</div>

        <ul class="sidebar-menu">
            <li>
                <a href="<?= $baseUrl ?>/user/dashboard.php">
                    📊 Overview
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/edit-portfolio.php" class="active">
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
                <h1>General Portfolio Settings</h1>
                <p style="color: var(--text-secondary);">
                    Manage your portfolio title, personalized URL, profile picture, and display preferences.
                </p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="padding-left: 1.2rem; margin: 0;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= sanitize($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="panel-card">

            <form
                action="<?= $baseUrl ?>/user/edit-portfolio.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>"
                >

                <div class="form-group">
                    <label for="title">Portfolio Title *</label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="form-control"
                        value="<?= sanitize($portfolio['title'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="portfolio_slug">
                        Personalized URL Slug *
                    </label>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="color: var(--text-secondary); font-weight: 500;">
                            /portfolio/
                        </span>

                        <input
                            type="text"
                            id="portfolio_slug"
                            name="portfolio_slug"
                            class="form-control"
                            value="<?= sanitize($portfolio['portfolio_slug'] ?? '') ?>"
                            required
                        >
                    </div>

                    <span class="form-help">
                        This unique slug will be used for your public portfolio URL.
                    </span>
                </div>

                <div
                    style="
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 1.5rem;
                        margin-top: 1.5rem;
                    "
                >

                    <div class="form-group">
                        <label for="accent_color">
                            Accent Color
                        </label>

                        <input
                            type="color"
                            id="accent_color"
                            name="accent_color"
                            class="form-control"
                            value="<?= sanitize($portfolio['accent_color'] ?? '#2563eb') ?>"
                            style="height: 45px; padding: 0.2rem;"
                        >
                    </div>

                    <div class="form-group">
                        <label for="font_family">
                            Font Family Style
                        </label>

                        <select
                            id="font_family"
                            name="font_family"
                            class="form-control"
                        >
                            <option
                                value="Inter, sans-serif"
                                <?= ($portfolio['font_family'] ?? '') === 'Inter, sans-serif' ? 'selected' : '' ?>
                            >
                                Modern Sans-Serif (Inter)
                            </option>

                            <option
                                value="'Roboto', sans-serif"
                                <?= ($portfolio['font_family'] ?? '') === "'Roboto', sans-serif" ? 'selected' : '' ?>
                            >
                                Clean Sans-Serif (Roboto)
                            </option>

                            <option
                                value="Georgia, serif"
                                <?= ($portfolio['font_family'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>
                            >
                                Classic Serif (Georgia)
                            </option>

                            <option
                                value="'Courier New', monospace"
                                <?= ($portfolio['font_family'] ?? '') === "'Courier New', monospace" ? 'selected' : '' ?>
                            >
                                Monospace (Tech)
                            </option>
                        </select>
                    </div>

                </div>

                <div
                    class="form-group"
                    style="margin-top: 1.5rem;"
                >
                    <label for="profile_image">
                        Profile Picture (Optional)
                    </label>

                    <?php if (!empty($user['profile_image'])): ?>

                        <div
                            style="
                                margin-bottom: 0.75rem;
                                display: flex;
                                align-items: center;
                                gap: 1rem;
                            "
                        >
                            <img
                                src="<?= $baseUrl ?>/uploads/profiles/<?= sanitize($user['profile_image']) ?>"
                                alt="Profile"
                                style="
                                    width: 70px;
                                    height: 70px;
                                    border-radius: 50%;
                                    object-fit: cover;
                                    border: 1px solid var(--border-color);
                                "
                            >

                            <span
                                style="
                                    font-size: 0.875rem;
                                    color: var(--text-secondary);
                                "
                            >
                                Current profile picture
                            </span>
                        </div>

                    <?php endif; ?>

                    <input
                        type="file"
                        id="profile_image"
                        name="profile_image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <span class="form-help">
                        Accepted formats: JPG, PNG, WEBP. Maximum size: 2MB.
                    </span>
                </div>

                <div
                    style="
                        margin-top: 2rem;
                        border-top: 1px solid var(--border-color);
                        padding-top: 1.5rem;
                    "
                >
                    <h3
                        style="
                            font-size: 1.1rem;
                            color: var(--secondary-color);
                            margin-bottom: 1rem;
                        "
                    >
                        Public Display Controls
                    </h3>

                    <div
                        class="form-group"
                        style="
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        "
                    >
                        <input
                            type="checkbox"
                            id="show_profile_image"
                            name="show_profile_image"
                            value="1"
                            <?= !empty($portfolio['show_profile_image']) ? 'checked' : '' ?>
                        >

                        <label
                            for="show_profile_image"
                            style="margin-bottom: 0;"
                        >
                            Show Profile Picture on Public Portfolio
                        </label>
                    </div>

                    <div
                        class="form-group"
                        style="
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        "
                    >
                        <input
                            type="checkbox"
                            id="show_email"
                            name="show_email"
                            value="1"
                            <?= !empty($portfolio['show_email']) ? 'checked' : '' ?>
                        >

                        <label
                            for="show_email"
                            style="margin-bottom: 0;"
                        >
                            Show Email Address on Public Portfolio
                        </label>
                    </div>

                    <div
                        class="form-group"
                        style="
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        "
                    >
                        <input
                            type="checkbox"
                            id="show_phone"
                            name="show_phone"
                            value="1"
                            <?= !empty($portfolio['show_phone']) ? 'checked' : '' ?>
                        >

                        <label
                            for="show_phone"
                            style="margin-bottom: 0;"
                        >
                            Show Phone Number on Public Portfolio
                        </label>
                    </div>

                    <div
                        class="form-group"
                        style="
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        "
                    >
                        <input
                            type="checkbox"
                            id="show_location"
                            name="show_location"
                            value="1"
                            <?= !empty($portfolio['show_location']) ? 'checked' : '' ?>
                        >

                        <label
                            for="show_location"
                            style="margin-bottom: 0;"
                        >
                            Show Location on Public Portfolio
                        </label>
                    </div>
                </div>


              <div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">

    <button
        type="submit"
        class="nav-btn btn-primary"
        style="padding: 0.8rem 2rem;"
    >
        Save Settings
    </button>

    <a
        href="<?= $baseUrl ?>/user/sections.php"
        class="nav-btn btn-outline"
        style="padding: 0.8rem 2rem;"
    >
        ➕ Add / Edit Information
    </a>

</div>

            </form>

        </div>

    </main>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>