<?php
// register.php

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$baseUrl = '/PortfolioForge-Clean';

// If already logged in, go to user dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: $baseUrl/user/dashboard.php");
    exit();
}

$errors = [];

$fullName = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token. Please refresh the page and try again.';
    }

    // Get form values
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // -------------------------
    // VALIDATION
    // -------------------------

    if ($fullName === '') {
        $errors[] = 'Full Name is required.';
    }

    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $errors[] = 'Username must be 3-30 characters and contain only letters, numbers, and underscores.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($confirmPassword === '') {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // -------------------------
    // DATABASE CHECK
    // -------------------------

    if (empty($errors)) {

        $pdo = getDBConnection();

        // Check username separately
        $usernameStmt = $pdo->prepare(
            "SELECT user_id FROM users WHERE username = ? LIMIT 1"
        );

        $usernameStmt->execute([$username]);

        if ($usernameStmt->fetch()) {
            $errors[] = 'Username is already taken. Please choose another username.';
        }

        // Check email separately
        if (empty($errors)) {

            $emailStmt = $pdo->prepare(
                "SELECT user_id FROM users WHERE email = ? LIMIT 1"
            );

            $emailStmt->execute([$email]);

            if ($emailStmt->fetch()) {
                $errors[] = 'Email address is already registered. Please use another email.';
            }
        }
    }

    // -------------------------
    // CREATE ACCOUNT
    // -------------------------

    if (empty($errors)) {

        try {

            $pdo->beginTransaction();

            // Hash password
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user
            $insertStmt = $pdo->prepare("
                INSERT INTO users
                    (full_name, username, email, password, status)
                VALUES
                    (?, ?, ?, ?, 'active')
            ");

            $insertStmt->execute([
                $fullName,
                $username,
                $email,
                $hashedPassword
            ]);

            // Get newly created user ID
            $userId = $pdo->lastInsertId();

            // Create initial portfolio
            getOrCreateUserPortfolio($userId, $pdo);

            // Everything succeeded
            $pdo->commit();

            // Create login session
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;

            setFlash(
                'success',
                'Registration successful! Welcome to Portfolio Forge.'
            );

            header("Location: $baseUrl/user/dashboard.php");
            exit();

        } catch (PDOException $e) {

            // Undo database changes if anything failed
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Register - Portfolio Forge';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container">

    <div class="auth-header">
        <h2>Create Account</h2>
        <p>Build and showcase your professional portfolio</p>
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

    <form
        action="<?= $baseUrl ?>/register.php"
        method="POST"
        class="auth-form"
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= generateCsrfToken() ?>"
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
                value="<?= sanitize($fullName) ?>"
                required
            >

        </div>

        <div class="form-group">

            <label for="username">
                Username *
            </label>

            <input
                type="text"
                id="username"
                name="username"
                class="form-control"
                value="<?= sanitize($username) ?>"
                required
                minlength="3"
                maxlength="30"
            >

            <span class="form-help">
                3-30 characters. Letters, numbers and underscores only.
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
                value="<?= sanitize($email) ?>"
                required
            >

        </div>

        <div class="form-group">

            <label for="password">
                Password *
            </label>

            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                required
                minlength="6"
            >

        </div>

        <div class="form-group">

            <label for="confirm_password">
                Confirm Password *
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                class="form-control"
                required
                minlength="6"
            >

        </div>

        <button
            type="submit"
            class="nav-btn btn-primary"
            style="width: 100%; margin-top: 1rem; padding: 0.8rem;"
        >
            Register Account
        </button>

    </form>

    <div
        style="
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        "
    >

        Already have an account?

        <a
            href="<?= $baseUrl ?>/login.php"
            style="
                color: var(--primary-color);
                font-weight: 600;
                text-decoration: none;
            "
        >
            Login here
        </a>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>