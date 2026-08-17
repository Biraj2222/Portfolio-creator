<?php
// admin/templates.php

require_once __DIR__ . '/../includes/admin-auth.php';

$admin = requireAdminLogin();
$pdo = getDBConnection();


// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {

        setFlash('danger', 'Invalid CSRF token.');

        header("Location: /PortfolioForge-Clean/admin/templates.php");
        exit();
    }


    $action = $_POST['action'] ?? '';
    $templateId = (int)($_POST['template_id'] ?? 0);


    if ($templateId > 0) {


        // Activate / deactivate
        if ($action === 'toggle_active') {

            $currentActive = (int)($_POST['current_active'] ?? 1);

            $newActive = $currentActive ? 0 : 1;


            $stmt = $pdo->prepare("
                UPDATE templates
                SET is_active = ?
                WHERE template_id = ?
            ");

            $stmt->execute([
                $newActive,
                $templateId
            ]);


            $statusText = $newActive
                ? 'activated'
                : 'deactivated';


            setFlash(
                'success',
                "Template #{$templateId} {$statusText} successfully."
            );
        }


        // Update metadata
        elseif ($action === 'update_metadata') {

            $name = trim(
                $_POST['template_name'] ?? ''
            );

            $desc = trim(
                $_POST['description'] ?? ''
            );


            if (!empty($name)) {

                $stmt = $pdo->prepare("
                    UPDATE templates
                    SET template_name = ?,
                        description = ?
                    WHERE template_id = ?
                ");

                $stmt->execute([
                    $name,
                    $desc,
                    $templateId
                ]);


                setFlash(
                    'success',
                    'Template metadata updated successfully.'
                );

            } else {

                setFlash(
                    'danger',
                    'Template name cannot be empty.'
                );
            }
        }
    }


    header("Location: /PortfolioForge-Clean/admin/templates.php");
    exit();
}



// Fetch templates
$stmt = $pdo->query("
    SELECT
        t.*,
        COUNT(p.portfolio_id) AS usage_count

    FROM templates t

    LEFT JOIN portfolios p
        ON t.template_id = p.template_id

    GROUP BY t.template_id

    ORDER BY t.template_id ASC
");

$templatesList = $stmt->fetchAll();


$pageTitle = 'Template Management - Admin Portal';
$extraCss = 'dashboard.css';

require_once __DIR__ . '/../includes/header.php';
?>


<div class="dashboard-layout">


    <!-- Sidebar -->

    <?php require_once __DIR__ . '/../includes/admin-sidebar.php'; ?>



    <!-- Main -->

    <main class="dashboard-content">


        <div class="content-header">

            <div>

                <h1>
                    Template Management
                </h1>

                <p style="color: var(--text-secondary);">
                    Manage system template availability and metadata.
                    Actual designs are implemented in source code.
                </p>

            </div>

        </div>



        <div class="panel-card">

            <h2
                class="panel-title"
                style="margin-bottom: 1rem;"
            >
                System Templates
            </h2>


            <table class="data-table">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Template Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Portfolios Using</th>
                        <th>Actions</th>
                    </tr>

                </thead>


                <tbody>

                <?php foreach ($templatesList as $tpl): ?>

                    <tr>

                        <td>
                            #<?= (int)$tpl['template_id'] ?>
                        </td>


                        <td>
                            <strong>
                                <?= sanitize($tpl['template_name']) ?>
                            </strong>
                        </td>


                        <td>
                            <code>
                                <?= sanitize($tpl['slug']) ?>
                            </code>
                        </td>


                        <td
                            style="
                                max-width: 250px;
                                font-size: 0.875rem;
                            "
                        >
                            <?= sanitize($tpl['description']) ?>
                        </td>


                        <td>

                            <?php if ($tpl['is_active']): ?>

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
                            <strong>
                                <?= (int)$tpl['usage_count'] ?>
                            </strong>
                        </td>


                        <td>


                            <!-- Toggle -->

                            <form
                                action="/PortfolioForge-Clean/admin/templates.php"
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
                                    value="toggle_active"
                                >

                                <input
                                    type="hidden"
                                    name="template_id"
                                    value="<?= (int)$tpl['template_id'] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="current_active"
                                    value="<?= (int)$tpl['is_active'] ?>"
                                >


                                <button
                                    type="submit"
                                    class="nav-btn <?= $tpl['is_active'] ? 'btn-danger' : 'btn-primary' ?> btn-sm"
                                >
                                    <?= $tpl['is_active']
                                        ? 'Deactivate'
                                        : 'Activate'
                                    ?>
                                </button>

                            </form>



                            <!-- Edit Info -->

                            <button
                                type="button"
                                class="nav-btn btn-outline btn-sm"
                                onclick='openMetaModal(
                                    <?= (int)$tpl["template_id"] ?>,
                                    <?= json_encode($tpl["template_name"]) ?>,
                                    <?= json_encode($tpl["description"]) ?>
                                )'
                            >
                                Edit Info
                            </button>


                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>



        <!-- Edit Metadata Modal -->

        <div
            id="metaModal"
            style="
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 2000;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            "
        >

            <div
                style="
                    background: #fff;
                    border-radius: var(--radius-md);
                    max-width: 500px;
                    width: 100%;
                    padding: 2rem;
                "
            >

                <h2
                    style="
                        margin-bottom: 1.5rem;
                        color: var(--secondary-color);
                    "
                >
                    Edit Template Info
                </h2>


                <form
                    action="/PortfolioForge-Clean/admin/templates.php"
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
                        value="update_metadata"
                    >

                    <input
                        type="hidden"
                        id="modal_template_id"
                        name="template_id"
                        value="0"
                    >


                    <div class="form-group">

                        <label for="modal_template_name">
                            Template Name *
                        </label>

                        <input
                            type="text"
                            id="modal_template_name"
                            name="template_name"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="modal_description">
                            Description
                        </label>

                        <textarea
                            id="modal_description"
                            name="description"
                            class="form-control"
                            style="min-height: 100px;"
                        ></textarea>

                    </div>


                    <div
                        style="
                            display: flex;
                            justify-content: flex-end;
                            gap: 1rem;
                            margin-top: 1.5rem;
                        "
                    >

                        <button
                            type="button"
                            class="nav-btn btn-outline"
                            onclick="closeMetaModal()"
                        >
                            Cancel
                        </button>


                        <button
                            type="submit"
                            class="nav-btn btn-primary"
                        >
                            Save Changes
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </main>

</div>



<script>

function openMetaModal(id, name, desc) {

    document.getElementById('modal_template_id').value = id;

    document.getElementById('modal_template_name').value = name;

    document.getElementById('modal_description').value = desc || '';

    document.getElementById('metaModal').style.display = 'flex';
}


function closeMetaModal() {

    document.getElementById('metaModal').style.display = 'none';
}


document.getElementById('metaModal').addEventListener(
    'click',
    function(event) {

        if (event.target === this) {
            closeMetaModal();
        }

    }
);

</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>