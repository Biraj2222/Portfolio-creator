<?php
// admin/users.php
require_once __DIR__ . '/../includes/admin-auth.php';

$admin = requireAdminLogin();

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid CSRF token.');
        header("Location: /PortfolioForge-Clean/admin/users.php");
        exit();
    }

    $action = $_POST['action'] ?? '';
    $targetUserId = (int)($_POST['user_id'] ?? 0);

    if ($targetUserId > 0) {

        if ($action === 'deactivate') {

            $uStmt = $pdo->prepare(
                "UPDATE users SET status = 'inactive' WHERE user_id = ?"
            );

            $uStmt->execute([$targetUserId]);

            setFlash(
                'warning',
                "User ID #{$targetUserId} account deactivated. The user cannot log in and their published portfolio is hidden. Their data is preserved."
            );

        } elseif ($action === 'activate') {

            $uStmt = $pdo->prepare(
                "UPDATE users SET status = 'active' WHERE user_id = ?"
            );

            $uStmt->execute([$targetUserId]);

            setFlash(
                'success',
                "User ID #{$targetUserId} account activated successfully."
            );

        } elseif ($action === 'delete_user') {

            $dStmt = $pdo->prepare(
                "DELETE FROM users WHERE user_id = ?"
            );

            $dStmt->execute([$targetUserId]);

            setFlash(
                'success',
                "User ID #{$targetUserId} account and associated data deleted permanently."
            );
        }
    }

    header("Location: /PortfolioForge-Clean/admin/users.php");
    exit();
}


// Fetch all users with portfolio information
$uStmt = $pdo->query("
    SELECT 
        u.user_id,
        u.full_name,
        u.username,
        u.email,
        u.status,
        u.created_at,
        p.portfolio_slug,
        p.status AS portfolio_status
    FROM users u
    LEFT JOIN portfolios p 
        ON u.user_id = p.user_id
    ORDER BY u.user_id DESC
");

$usersList = $uStmt->fetchAll();

$pageTitle = 'User Management - Admin Portal';
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
                    User Account Management
                </h1>

                <p style="color: var(--text-secondary);">
                    Activate or deactivate platform user accounts.
                    Administrators manage account status only and cannot edit portfolio content.
                </p>

            </div>

        </div>


        <!-- Registered Users -->
        <div class="panel-card">

            <h2 class="panel-title" style="margin-bottom: 1rem;">
                Registered Accounts
            </h2>


            <?php if (empty($usersList)): ?>

                <p style="color: var(--text-secondary);">
                    No registered users in the database yet.
                </p>

            <?php else: ?>

                <table class="data-table">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Portfolio Slug</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($usersList as $u): ?>

                            <tr>

                                <td>
                                    #<?= (int)$u['user_id'] ?>
                                </td>


                                <td>
                                    <strong>
                                        <?= sanitize($u['full_name']) ?>
                                    </strong>
                                </td>


                                <td>
                                    <?= sanitize($u['username']) ?>
                                </td>


                                <td>
                                    <?= sanitize($u['email']) ?>
                                </td>


                                <td>

                                    <?php if ($u['status'] === 'active'): ?>

                                        <span class="badge badge-success">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-danger">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if (!empty($u['portfolio_slug'])): ?>

                                        <code>
                                            <?= sanitize($u['portfolio_slug']) ?>
                                        </code>

                                        (
                                        <?= sanitize($u['portfolio_status'] ?? 'draft') ?>
                                        )

                                    <?php else: ?>

                                        <span style="color: var(--text-secondary);">
                                            -
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>
                                    <?= sanitize(
                                        date(
                                            'M d, Y',
                                            strtotime($u['created_at'])
                                        )
                                    ) ?>
                                </td>


                                <td>

                                    <?php if ($u['status'] === 'active'): ?>

                                        <form
                                            action="/PortfolioForge-Clean/admin/users.php"
                                            method="POST"
                                            style="display: inline;"
                                            onsubmit="return confirm('Deactivating this account will prevent the user from logging in and will temporarily hide their published portfolio. Their data will be preserved. Proceed?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= generateCsrfToken() ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="deactivate"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$u['user_id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="nav-btn btn-danger btn-sm"
                                            >
                                                Deactivate
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <form
                                            action="/PortfolioForge-Clean/admin/users.php"
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
                                                value="activate"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$u['user_id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="nav-btn btn-primary btn-sm"
                                            >
                                                Activate
                                            </button>

                                        </form>

                                    <?php endif; ?>


                                    <form
                                        action="/PortfolioForge-Clean/admin/users.php"
                                        method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('Permanently delete user account #<?= (int)$u['user_id'] ?> and all portfolio data?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= generateCsrfToken() ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete_user"
                                        >

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?= (int)$u['user_id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="nav-btn btn-outline btn-sm"
                                        >
                                            Delete
                                        </button>

                                    </form>

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