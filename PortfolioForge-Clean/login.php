<?php
// login.php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /PortfolioForge-Clean/user/dashboard.php");
    exit();
}

if (isset($_SESSION['admin_id'])) {
    header("Location: /PortfolioForge-Clean/admin/dashboard.php");
    exit();
}

$errors = [];
$loginInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    }

    $loginInput = trim($_POST['login_input'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($loginInput)) {
        $errors[] = 'Username or Email is required.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {

        $pdo = getDBConnection();

        /*
         * FIRST: Check admin account
         */
        $stmt = $pdo->prepare("
            SELECT admin_id, username, email, password
            FROM admins
            WHERE username = ? OR email = ?
        ");

        $stmt->execute([$loginInput, $loginInput]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {

            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];

            setFlash('success', 'Admin login successful.');

            header("Location: /PortfolioForge-Clean/admin/dashboard.php");
            exit();
        }


        /*
         * SECOND: Check normal user account
         */
        $stmt = $pdo->prepare("
            SELECT user_id, full_name, username, email, password, status
            FROM users
            WHERE username = ? OR email = ?
        ");

        $stmt->execute([$loginInput, $loginInput]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            if ($user['status'] !== 'active') {

                $errors[] = 'Your account has been deactivated by an administrator. Please contact support.';

            } else {

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];

                // Ensure portfolio exists
                getOrCreateUserPortfolio($user['user_id'], $pdo);

                setFlash(
                    'success',
                    'Welcome back, ' . htmlspecialchars($user['full_name']) . '!'
                );

                header("Location: /PortfolioForge-Clean/user/dashboard.php");
                exit();
            }

        } else {

            $errors[] = 'Invalid username/email or password.';

        }
    }
}

$pageTitle = 'Login - Portfolio Forge';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container">

    <div class="auth-header">
        <h2>Login</h2>
        <p>Access your Portfolio Forge account</p>
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


    <form action="/PortfolioForge-Clean/login.php" method="POST" class="auth-form">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= generateCsrfToken() ?>"
        >

        <div class="form-group">

            <label for="login_input">
                Username or Email *
            </label>

            <input
                type="text"
                id="login_input"
                name="login_input"
                class="form-control"
                value="<?= sanitize($loginInput) ?>"
                required
                autofocus
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
            >

        </div>


        <button
            type="submit"
            class="nav-btn btn-primary"
            style="width: 100%; margin-top: 1rem; padding: 0.8rem;"
        >
            Log In
        </button>

    </form>


    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">

        Don't have an account?

        <a
            href="/PortfolioForge-Clean/register.php"
            style="color: var(--primary-color); font-weight: 600; text-decoration: none;"
        >
            Register here
        </a>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>