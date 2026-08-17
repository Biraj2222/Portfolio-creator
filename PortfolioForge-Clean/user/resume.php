<?php
// user/resume.php
require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$pdo = getDBConnection();
$portfolio = getOrCreateUserPortfolio($user['user_id'], $pdo);
$pId = $portfolio['portfolio_id'];

$baseUrl = '/PortfolioForge-Clean';
$errors = [];
$extractedData = null;

// Get current resume
$rStmt = $pdo->prepare("SELECT * FROM resume WHERE portfolio_id = ?");
$rStmt->execute([$pId]);
$resume = $rStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token. Please refresh the page and try again.';
    }

    $action = $_POST['action'] ?? '';

    if (empty($errors) && $action === 'upload_resume') {

        if (
            !isset($_FILES['resume_file']) ||
            $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK
        ) {
            $errors[] = 'Please select a valid PDF file to upload.';
        } else {

            $uploadRes = uploadFile(
                $_FILES['resume_file'],
                ['application/pdf'],
                ['pdf'],
                5 * 1024 * 1024,
                __DIR__ . '/../uploads/resumes'
            );

            if ($uploadRes['success']) {

                $filePath = $uploadRes['filepath'];
                $fileName = $uploadRes['original_name'];

                if ($resume) {

                    // Delete old physical file
                    if (!empty($resume['file_path']) && file_exists($resume['file_path'])) {
                        @unlink($resume['file_path']);
                    }

                    // Update existing database record
                    $uStmt = $pdo->prepare("
                        UPDATE resume
                        SET file_name = ?, file_path = ?, uploaded_at = CURRENT_TIMESTAMP
                        WHERE portfolio_id = ?
                    ");

                    $uStmt->execute([
                        $fileName,
                        $filePath,
                        $pId
                    ]);

                } else {

                    // Create new resume record
                    $iStmt = $pdo->prepare("
                        INSERT INTO resume
                        (portfolio_id, file_name, file_path, public_download_enabled)
                        VALUES (?, ?, ?, 0)
                    ");

                    $iStmt->execute([
                        $pId,
                        $fileName,
                        $filePath
                    ]);
                }

                // Extract readable text from PDF
                $text = extractTextFromPDF($filePath);

                if (!empty($text)) {
                    $extractedData = parseResumeText($text);

                    setFlash(
                        'success',
                        'Resume uploaded successfully. Review the extracted information below.'
                    );
                } else {
                    setFlash(
                        'warning',
                        'Resume uploaded successfully, but no readable text could be extracted from the PDF.'
                    );
                }

            } else {
                $errors[] = 'Upload Error: ' . $uploadRes['error'];
            }
        }

    } elseif (empty($errors) && $action === 'toggle_public_download') {

        $enabled = isset($_POST['public_download_enabled']) ? 1 : 0;

        $uStmt = $pdo->prepare("
            UPDATE resume
            SET public_download_enabled = ?
            WHERE portfolio_id = ?
        ");

        $uStmt->execute([
            $enabled,
            $pId
        ]);

        setFlash('success', 'Resume download permission updated.');

        header("Location: {$baseUrl}/user/resume.php");
        exit();

    } elseif (empty($errors) && $action === 'delete_resume') {

        if ($resume) {

            // Delete physical file
            if (!empty($resume['file_path']) && file_exists($resume['file_path'])) {
                @unlink($resume['file_path']);
            }

            // Delete database record
            $dStmt = $pdo->prepare("
                DELETE FROM resume
                WHERE portfolio_id = ?
            ");

            $dStmt->execute([$pId]);

            setFlash('success', 'Uploaded resume removed successfully.');
        }

        header("Location: {$baseUrl}/user/resume.php");
        exit();

    } elseif (empty($errors) && $action === 'save_extracted_data') {

        $parsedSummary = trim(
            $_POST['extracted_summary'] ?? ''
        );

        $parsedSkills = array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(',', $_POST['extracted_skills'] ?? '')
                )
            )
        );

        $parsedExperience = array_values(
            array_filter(
                array_map(
                    'trim',
                    explode("\n\n", $_POST['extracted_experience'] ?? '')
                )
            )
        );

        $parsedEducation = array_values(
            array_filter(
                array_map(
                    'trim',
                    explode("\n\n", $_POST['extracted_education'] ?? '')
                )
            )
        );

        $parsedProjects = array_values(
            array_filter(
                array_map(
                    'trim',
                    explode("\n\n", $_POST['extracted_projects'] ?? '')
                )
            )
        );

        $parsedCertifications = array_values(
            array_filter(
                array_map(
                    'trim',
                    explode("\n\n", $_POST['extracted_certifications'] ?? '')
                )
            )
        );

        $sectionsToSave = [

            'about' => [
                'title' => 'About Me',
                'type' => 'about',
                'order' => 1,
                'data' => [
                    'text' => $parsedSummary
                ]
            ],

            'skills' => [
                'title' => 'Skills',
                'type' => 'skills',
                'order' => 2,
                'data' => $parsedSkills
            ],

            'experience' => [
                'title' => 'Work Experience',
                'type' => 'experience',
                'order' => 3,
                'data' => $parsedExperience
            ],

            'education' => [
                'title' => 'Education',
                'type' => 'education',
                'order' => 4,
                'data' => $parsedEducation
            ],

            'projects' => [
                'title' => 'Projects',
                'type' => 'projects',
                'order' => 5,
                'data' => $parsedProjects
            ],

            'certifications' => [
                'title' => 'Certifications',
                'type' => 'certifications',
                'order' => 6,
                'data' => $parsedCertifications
            ]
        ];

        foreach ($sectionsToSave as $section) {

            if (
                empty($section['data']) ||
                (is_array($section['data']) && empty($section['data']))
            ) {
                continue;
            }

            $jsonContent = json_encode(
                $section['data'],
                JSON_UNESCAPED_UNICODE
            );

            // Check if section already exists
            $checkStmt = $pdo->prepare("
                SELECT section_id
                FROM portfolio_sections
                WHERE portfolio_id = ?
                AND section_type = ?
            ");

            $checkStmt->execute([
                $pId,
                $section['type']
            ]);

            $existing = $checkStmt->fetch();

            if ($existing) {

                $updateStmt = $pdo->prepare("
                    UPDATE portfolio_sections
                    SET content = ?,
                        title = ?,
                        is_visible = 1
                    WHERE section_id = ?
                ");

                $updateStmt->execute([
                    $jsonContent,
                    $section['title'],
                    $existing['section_id']
                ]);

            } else {

                $insertStmt = $pdo->prepare("
                    INSERT INTO portfolio_sections
                    (
                        portfolio_id,
                        section_type,
                        title,
                        content,
                        display_order,
                        is_visible
                    )
                    VALUES (?, ?, ?, ?, ?, 1)
                ");

                $insertStmt->execute([
                    $pId,
                    $section['type'],
                    $section['title'],
                    $jsonContent,
                    $section['order']
                ]);
            }
        }

        setFlash(
            'success',
            'Resume information has been added to your portfolio. You can edit it from Sections.'
        );

        header("Location: {$baseUrl}/user/sections.php");
        exit();
    }
}

// Refresh resume information
$rStmt = $pdo->prepare("SELECT * FROM resume WHERE portfolio_id = ?");
$rStmt->execute([$pId]);
$resume = $rStmt->fetch();

$pageTitle = 'Resume Management - Portfolio Forge';
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
                <a href="<?= $baseUrl ?>/user/resume.php" class="active">
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
                <h1>Resume Upload & Extraction</h1>

                <p style="color: var(--text-secondary);">
                    Upload a PDF resume to automatically extract information
                    and use it to populate your portfolio.
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

            <h2 class="panel-title" style="margin-bottom: 1rem;">
                Upload PDF Resume
            </h2>

            <form
                action="<?= $baseUrl ?>/user/resume.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="upload_resume"
                >

                <div class="form-group">

                    <label for="resume_file">
                        Select PDF File *
                    </label>

                    <input
                        type="file"
                        id="resume_file"
                        name="resume_file"
                        class="form-control"
                        accept="application/pdf"
                        required
                    >

                    <span class="form-help">
                        PDF only. Maximum size: 5MB.
                    </span>

                </div>

                <button
                    type="submit"
                    class="nav-btn btn-primary"
                    style="margin-top: 0.5rem;"
                >
                    Upload & Extract Data
                </button>

            </form>

        </div>

        <?php if ($resume): ?>

            <div class="panel-card">

                <div class="panel-header">

                    <h2 class="panel-title">
                        Current Uploaded Resume
                    </h2>

                    <form
                        action="<?= $baseUrl ?>/user/resume.php"
                        method="POST"
                        style="margin: 0;"
                        onsubmit="return confirm('Delete uploaded resume?');"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= generateCsrfToken() ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="delete_resume"
                        >

                        <button
                            type="submit"
                            class="nav-btn btn-danger btn-sm"
                        >
                            Remove File
                        </button>

                    </form>

                </div>

                <p style="margin-bottom: 1rem; color: var(--text-secondary);">

                    <strong>File Name:</strong>
                    <?= sanitize($resume['file_name']) ?>

                    <br>

                    <strong>Uploaded At:</strong>
                    <?= sanitize($resume['uploaded_at']) ?>

                </p>

                <form
                    action="<?= $baseUrl ?>/user/resume.php"
                    method="POST"
                    style="border-top: 1px solid var(--border-color); padding-top: 1rem;"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= generateCsrfToken() ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="toggle_public_download"
                    >

                    <div
                        class="form-group"
                        style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;"
                    >

                        <input
                            type="checkbox"
                            id="public_download_enabled"
                            name="public_download_enabled"
                            value="1"
                            <?= $resume['public_download_enabled'] ? 'checked' : '' ?>
                        >

                        <label
                            for="public_download_enabled"
                            style="margin-bottom: 0;"
                        >
                            Allow visitors to download this resume
                        </label>

                    </div>

                    <button
                        type="submit"
                        class="nav-btn btn-outline btn-sm"
                    >
                        Update Download Permission
                    </button>

                </form>

            </div>

        <?php endif; ?>

        <?php if ($extractedData): ?>

            <div
                class="panel-card"
                style="border: 2px solid var(--primary-color);"
            >

                <div class="panel-header">

                    <h2
                        class="panel-title"
                        style="color: var(--primary-color);"
                    >
                        Review Extracted Resume Data
                    </h2>

                </div>

                <p
                    style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 0.95rem;"
                >
                    Review the extracted information before adding it
                    to your portfolio. You can edit anything that was
                    extracted incorrectly.
                </p>

                <form
                    action="<?= $baseUrl ?>/user/resume.php"
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
                        value="save_extracted_data"
                    >

                    <div class="form-group">

                        <label for="extracted_summary">
                            Professional Summary / About
                        </label>

                        <textarea
                            id="extracted_summary"
                            name="extracted_summary"
                            class="form-control"
                            style="min-height: 100px;"
                        ><?= sanitize($extractedData['summary'] ?? '') ?></textarea>

                    </div>

                    <div class="form-group">

                        <label for="extracted_skills">
                            Skills
                        </label>

                        <input
                            type="text"
                            id="extracted_skills"
                            name="extracted_skills"
                            class="form-control"
                            value="<?= sanitize(implode(', ', $extractedData['skills'] ?? [])) ?>"
                        >

                        <span class="form-help">
                            Separate skills with commas.
                        </span>

                    </div>

                    <div class="form-group">

                        <label for="extracted_experience">
                            Work Experience
                        </label>

                        <textarea
                            id="extracted_experience"
                            name="extracted_experience"
                            class="form-control"
                            style="min-height: 120px;"
                        ><?= sanitize(implode("\n\n", $extractedData['experience'] ?? [])) ?></textarea>

                        <span class="form-help">
                            Separate different entries with an empty line.
                        </span>

                    </div>

                    <div class="form-group">

                        <label for="extracted_education">
                            Education
                        </label>

                        <textarea
                            id="extracted_education"
                            name="extracted_education"
                            class="form-control"
                            style="min-height: 100px;"
                        ><?= sanitize(implode("\n\n", $extractedData['education'] ?? [])) ?></textarea>

                    </div>

                    <div class="form-group">

                        <label for="extracted_projects">
                            Projects
                        </label>

                        <textarea
                            id="extracted_projects"
                            name="extracted_projects"
                            class="form-control"
                            style="min-height: 100px;"
                        ><?= sanitize(implode("\n\n", $extractedData['projects'] ?? [])) ?></textarea>

                    </div>

                    <div class="form-group">

                        <label for="extracted_certifications">
                            Certifications
                        </label>

                        <textarea
                            id="extracted_certifications"
                            name="extracted_certifications"
                            class="form-control"
                            style="min-height: 80px;"
                        ><?= sanitize(implode("\n\n", $extractedData['certifications'] ?? [])) ?></textarea>

                    </div>

                    <div style="margin-top: 1.5rem;">

                        <button
                            type="submit"
                            class="nav-btn btn-primary"
                            style="padding: 0.8rem 2rem;"
                        >
                            Confirm & Add to Portfolio
                        </button>

                    </div>

                </form>

            </div>

        <?php endif; ?>

    </main>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>