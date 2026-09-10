<?php
// user/templates.php
require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$pdo = getDBConnection();
$portfolio = getOrCreateUserPortfolio($user['user_id'], $pdo);

// Fetch all active templates
$tStmt = $pdo->query("
    SELECT *
    FROM templates
    WHERE is_active = 1
    ORDER BY template_id ASC
");
$templates = $tStmt->fetchAll();

/*
 * Handle template selection
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF protection
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid CSRF token.');
        header("Location: /PortfolioForge-Clean/user/templates.php");
        exit();
    }

    $templateId = (int)($_POST['template_id'] ?? 0);

    // Check that selected template exists and is active
    $chStmt = $pdo->prepare("
        SELECT template_id, template_name
        FROM templates
        WHERE template_id = ?
        AND is_active = 1
    ");

    $chStmt->execute([$templateId]);
    $selectedTemplate = $chStmt->fetch();

    if (!$selectedTemplate) {

        setFlash(
            'danger',
            'Selected template is invalid or inactive.'
        );

    } else {

        // Update only the template.
        // Portfolio sections and other data remain unchanged.
        $uStmt = $pdo->prepare("
            UPDATE portfolios
            SET template_id = ?
            WHERE portfolio_id = ?
        ");

        $uStmt->execute([
            $templateId,
            $portfolio['portfolio_id']
        ]);

        setFlash(
            'success',
            "Template switched to '" .
            $selectedTemplate['template_name'] .
            "' without losing any portfolio data."
        );
    }

    header("Location: /PortfolioForge-Clean/user/templates.php");
    exit();
}

// Refresh portfolio after any changes
$portfolio = getOrCreateUserPortfolio(
    $user['user_id'],
    $pdo
);

$pageTitle = 'Templates - Portfolio Forge';
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
                <a href="/PortfolioForge-Clean/user/templates.php" class="active">
                    🎨 Templates
                </a>
            </li>

            <li>
                <a href="/PortfolioForge-Clean/user/statistics.php">
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


    <main class="dashboard-content">

        <div class="content-header">

            <div>

                <h1>Choose Your Design Template</h1>

                <p style="color: var(--text-secondary);">
                    Switch templates to change the visual design of your
                    portfolio without losing your portfolio data.
                </p>

            </div>

        </div>


        <div
            style="
                display: grid;
                grid-template-columns:
                    repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
            "
        >

            <?php foreach ($templates as $tpl): ?>

                <?php
                $isSelected =
                    ((int)$tpl['template_id'] ===
                    (int)$portfolio['template_id']);
                ?>

                <div
                    class="panel-card"
                    style="
                        padding: 0;
                        overflow: hidden;
                        display: flex;
                        flex-direction: column;
                        border:
                        <?= $isSelected
                            ? '2px solid var(--primary-color)'
                            : '1px solid var(--border-color)' ?>;
                    "
                >

                    <!-- Template Preview Area -->
                    <div class="template-preview" style="position: relative; overflow: hidden;">

    <?php if ($tpl['template_name'] === 'Modern'): ?>

        <div class="preview-modern">

            <div class="modern-header">
                <div class="preview-avatar"></div>

                <div>
                    <div class="preview-name">Alex Johnson</div>
                    <div class="preview-job">Web Developer</div>
                    <div class="preview-contact">alex@email.com</div>
                </div>
            </div>

            <div class="modern-section">
                <h4>ABOUT</h4>
                <div class="preview-line"></div>
                <div class="preview-text"></div>
                <div class="preview-text short"></div>
            </div>

            <div class="modern-cards">

                <div>
                    <strong>PROJECTS</strong>
                    <span></span>
                    <span></span>
                </div>

                <div>
                    <strong>SKILLS</strong>
                    <span></span>
                    <span></span>
                </div>

            </div>

        </div>


    <?php elseif ($tpl['template_name'] === 'Minimal'): ?>

        <div class="preview-minimal">

            <div class="minimal-header">
                <div class="preview-name">Alex Johnson</div>
                <div class="preview-job">Web Developer</div>
                <div class="minimal-contact">alex@email.com</div>
            </div>

            <div class="minimal-section">

                <h4>ABOUT</h4>
                <div class="minimal-line"></div>

                <div class="preview-text"></div>
                <div class="preview-text"></div>
                <div class="preview-text short"></div>

            </div>

            <div class="minimal-section">

                <h4>EXPERIENCE</h4>
                <div class="minimal-line"></div>

                <div class="minimal-entry">
                    <strong>Web Developer</strong>
                    <span>2024 — Present</span>
                </div>

                <div class="preview-text"></div>
                <div class="preview-text short"></div>

            </div>

            <div class="minimal-skills">
                <span>PHP</span>
                <span>JavaScript</span>
                <span>MySQL</span>
            </div>

        </div>


    <?php elseif ($tpl['template_name'] === 'Professional'): ?>

        <div class="preview-professional">

            <div class="professional-header">

                <div class="preview-avatar"></div>

                <div>
                    <div class="preview-name">Alex Johnson</div>
                    <div class="preview-job">Software Engineer</div>

                    <div class="preview-contact">
                        Email: alex@email.com
                    </div>
                </div>

            </div>

            <div class="professional-section">

                <h4>ABOUT ME</h4>

                <div class="professional-card">
                    <div class="preview-text"></div>
                    <div class="preview-text short"></div>
                </div>

            </div>

            <div class="professional-section">

                <h4>EXPERIENCE</h4>

                <div class="professional-card">

                    <strong>Software Engineer</strong>

                    <div class="preview-text"></div>
                    <div class="preview-text short"></div>

                </div>

            </div>

            <div class="professional-skills">
                <span>PHP</span>
                <span>MySQL</span>
                <span>Java</span>
            </div>

        </div>


    <?php elseif ($tpl['template_name'] === 'Creative'): ?>

        <div class="preview-creative">

            <div class="creative-header">

                <div class="creative-avatar"></div>

                <div>
                    <div class="creative-name">Alex Johnson</div>
                    <div class="preview-job">Creative Developer</div>
                </div>

            </div>

            <div class="creative-section">

                <h4>✦ ABOUT ME</h4>

                <div class="creative-card">
                    <div class="preview-text"></div>
                    <div class="preview-text short"></div>
                </div>

            </div>

            <div class="creative-section">

                <h4>✦ SKILLS</h4>

                <div class="creative-pills">
                    <span>PHP</span>
                    <span>JavaScript</span>
                    <span>UI Design</span>
                </div>

            </div>

            <div class="creative-section">

                <h4>✦ PROJECTS</h4>

                <div class="creative-projects">
                    <div></div>
                    <div></div>
                </div>

            </div>

        </div>


    <?php elseif ($tpl['template_name'] === 'Classic'): ?>

        <div class="preview-classic">

            <div class="classic-header">

                <div class="preview-avatar classic-avatar"></div>

                <div>
                    <div class="classic-name">Alex Johnson</div>

                    <div class="classic-job">
                        Software Developer
                    </div>

                    <div class="classic-contact">
                        Email: alex@email.com
                    </div>
                </div>

            </div>

            <div class="classic-section">

                <h4>ABOUT ME</h4>

                <div class="preview-text"></div>
                <div class="preview-text"></div>
                <div class="preview-text short"></div>

            </div>

            <div class="classic-section">

                <h4>EXPERIENCE</h4>

                <div class="classic-entry">

                    <strong>Software Developer</strong>
                    <span>2024 — Present</span>

                </div>

                <div class="preview-text"></div>
                <div class="preview-text short"></div>

            </div>

            <div class="classic-section">

                <h4>SKILLS</h4>

                <div class="classic-skills">
                    PHP • JavaScript • MySQL • Java
                </div>

            </div>

        </div>

    <?php endif; ?>


    <?php if ($isSelected): ?>

        <span
            class="badge badge-success"
            style="position: absolute; top: 10px; right: 10px; z-index: 10;"
        >
            Active Template
        </span>

    <?php endif; ?>

</div>


                    <!-- Template Information -->
                    <div
                        style="
                            padding: 1.25rem;
                            flex: 1;
                            display: flex;
                            flex-direction: column;
                        "
                    >

                        <h3
                            style="
                                font-size: 1.1rem;
                                color: var(--secondary-color);
                                margin-bottom: 0.5rem;
                            "
                        >
                            <?= sanitize($tpl['template_name']) ?>
                        </h3>


                        <p
                            style="
                                color: var(--text-secondary);
                                font-size: 0.875rem;
                                flex: 1;
                                margin-bottom: 1.25rem;
                                line-height: 1.5;
                            "
                        >
                            <?= sanitize($tpl['description']) ?>
                        </p>


                        <?php if ($isSelected): ?>

                            <button
                                type="button"
                                disabled
                                class="nav-btn btn-outline"
                                style="
                                    width: 100%;
                                    opacity: 0.7;
                                    cursor: default;
                                "
                            >
                                Currently Applied
                            </button>

                        <?php else: ?>

                            <form
                                action="/PortfolioForge-Clean/user/templates.php"
                                method="POST"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= generateCsrfToken() ?>"
                                >

                                <input
                                    type="hidden"
                                    name="template_id"
                                    value="<?= (int)$tpl['template_id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="nav-btn btn-primary"
                                    style="width: 100%;"
                                >
                                    Select This Template
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </main>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>