<?php
// user/profile.php

require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();
$pdo = getDBConnection();

$baseUrl = '/PortfolioForge-Clean';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    }

    $action = $_POST['action'] ?? '';

    /*
     * Update Profile
     */
    if ($action === 'update_profile') {

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($fullName)) {
            $errors[] = 'Full Name is required.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }

        if (empty($errors)) {

            $eStmt = $pdo->prepare("
                SELECT user_id
                FROM users
                WHERE email = ?
                AND user_id != ?
            ");

            $eStmt->execute([
                $email,
                $user['user_id']
            ]);

            if ($eStmt->fetch()) {
                $errors[] = 'Email address is already in use by another account.';
            }
        }

        if (empty($errors)) {

            $uStmt = $pdo->prepare("
                UPDATE users
                SET full_name = ?, email = ?
                WHERE user_id = ?
            ");

            $uStmt->execute([
                $fullName,
                $email,
                $user['user_id']
            ]);

            setFlash('success', 'Account profile updated successfully.');

            header("Location: {$baseUrl}/user/profile.php");
            exit();
        }
    }

    /*
     * Change Password
     */
    elseif ($action === 'change_password') {

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $pStmt = $pdo->prepare("
            SELECT password
            FROM users
            WHERE user_id = ?
        ");

        $pStmt->execute([$user['user_id']]);

        $dbPass = $pStmt->fetchColumn();

        if (!$dbPass || !password_verify($currentPassword, $dbPass)) {
            $errors[] = 'Current password is incorrect.';
        }

        if (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {

            $hashed = password_hash(
                $newPassword,
                PASSWORD_BCRYPT
            );

            $uPass = $pdo->prepare("
                UPDATE users
                SET password = ?
                WHERE user_id = ?
            ");

            $uPass->execute([
                $hashed,
                $user['user_id']
            ]);

            setFlash('success', 'Password changed successfully.');

            header("Location: {$baseUrl}/user/profile.php");
            exit();
        }
    }

    /*
     * Delete Account
     */
    elseif ($action === 'delete_account') {

        $confirmText = trim($_POST['confirm_text'] ?? '');

        if ($confirmText !== 'DELETE') {

            $errors[] =
                'Please type DELETE in uppercase to confirm account deletion.';

        } else {

            $dStmt = $pdo->prepare("
                DELETE FROM users
                WHERE user_id = ?
            ");

            $dStmt->execute([
                $user['user_id']
            ]);

            unset($_SESSION['user_id']);
            unset($_SESSION['username']);

            setFlash(
                'info',
                'Your account and all associated portfolio data have been permanently deleted.'
            );

            header("Location: {$baseUrl}/register.php");
            exit();
        }
    }
}

/*
 * Refresh user information
 */
$user = getCurrentUser();

$pageTitle = 'Profile Settings - Portfolio Forge';
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
                <a href="<?= $baseUrl ?>/user/dashboard.php">
                    Overview
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/edit-portfolio.php">
                    Edit Portfolio
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/sections.php">
                    Sections
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/resume.php">
                    Resume Upload
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/templates.php">
                    Templates
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/statistics.php">
                    Statistics
                </a>
            </li>

            <li>
                <a href="<?= $baseUrl ?>/user/profile.php" class="active">
                    Profile Settings
                </a>
            </li>

        </ul>

    </aside>


    <main class="dashboard-content">

        <div class="content-header">

            <div>

                <h1>Account & Security Settings</h1>

                <p style="color: var(--text-secondary);">
                    Manage your personal details, password, and account settings.
                </p>

            </div>

        </div>


        <?php if (!empty($errors)): ?>

            <div class="alert alert-danger">

                <ul style="padding-left: 1.2rem; margin: 0;">

                    <?php foreach ($errors as $err): ?>

                        <li>
                            <?= sanitize($err) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <div class="panel-card">

            <h2 class="panel-title" style="margin-bottom: 1.25rem;">
                Personal Information
            </h2>

            <form
                action="<?= $baseUrl ?>/user/profile.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="update_profile"
                >


                <div class="form-group">

                    <label for="full_name">
                        Full Name *
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        class="form-control"
                        value="<?= sanitize($user['full_name']) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        class="form-control"
                        value="<?= sanitize($user['username']) ?>"
                        disabled
                        style="background-color: #f1f5f9;"
                    >

                    <span class="form-help">
                        Username cannot be changed.
                    </span>

                </div>


                <div class="form-group">

                    <label for="email">
                        Email Address *
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="<?= sanitize($user['email']) ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="nav-btn btn-primary"
                    style="margin-top: 0.5rem;"
                >
                    Update Profile
                </button>

            </form>

        </div>


        <div class="panel-card">

            <h2 class="panel-title" style="margin-bottom: 1.25rem;">
                Change Password
            </h2>

            <form
                action="<?= $baseUrl ?>/user/profile.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="change_password"
                >


                <div class="form-group">

                    <label for="current_password">
                        Current Password *
                    </label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        class="form-control"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="new_password">
                        New Password *
                    </label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        class="form-control"
                        minlength="6"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="confirm_password">
                        Confirm New Password *
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        minlength="6"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="nav-btn btn-primary"
                    style="margin-top: 0.5rem;"
                >
                    Change Password
                </button>

            </form>

        </div>


        <div
            class="panel-card"
            style="border-color: #fca5a5;"
        >

            <h2
                class="panel-title"
                style="
                    color: var(--danger-color);
                    margin-bottom: 1rem;
                "
            >
                Delete Account
            </h2>


            <p
                style="
                    color: var(--text-secondary);
                    margin-bottom: 1.25rem;
                    font-size: 0.95rem;
                "
            >
                Permanently delete your account, portfolio, sections,
                resume uploads, and analytics data. This action cannot
                be undone.
            </p>


            <form
                action="<?= $baseUrl ?>/user/profile.php"
                method="POST"
                onsubmit="return confirm(
                    'Are you completely sure you want to permanently delete your account?'
                );"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="delete_account"
                >


                <div class="form-group">

                    <label for="confirm_text">
                        Type <strong>DELETE</strong> to confirm:
                    </label>

                    <input
                        type="text"
                        id="confirm_text"
                        name="confirm_text"
                        class="form-control"
                        placeholder="DELETE"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="nav-btn btn-danger"
                >
                    Permanently Delete Account
                </button>

            </form>

        </div>

    </main>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>