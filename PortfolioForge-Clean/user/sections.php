<?php
// user/sections.php

require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$pdo = getDBConnection();
$portfolio = getOrCreateUserPortfolio($user['user_id'], $pdo);
$pId = $portfolio['portfolio_id'];

$baseUrl = '/PortfolioForge-Clean';


/*
|--------------------------------------------------------------------------
| FETCH SECTIONS
|--------------------------------------------------------------------------
*/

$sStmt = $pdo->prepare("
    SELECT *
    FROM portfolio_sections
    WHERE portfolio_id = ?
    ORDER BY display_order ASC, section_id ASC
");

$sStmt->execute([$pId]);
$sections = $sStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {

        setFlash(
            'danger',
            'Invalid request token. Please refresh the page and try again.'
        );

        header("Location: {$baseUrl}/user/sections.php");
        exit();
    }

    $action = $_POST['action'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | SAVE SECTION
    |--------------------------------------------------------------------------
    */

    if ($action === 'save_section') {

        $sectionId = (int)($_POST['section_id'] ?? 0);

        $sectionType = trim(
            $_POST['section_type'] ?? 'about'
        );

        $title = trim(
            $_POST['title'] ?? ''
        );

        $displayOrder = (int)(
            $_POST['display_order'] ?? 1
        );

        $isVisible = isset($_POST['is_visible'])
            ? 1
            : 0;

        $contentRaw = $_POST['content'] ?? '';


        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (empty($title)) {

            setFlash(
                'danger',
                'Section title is required.'
            );

            header("Location: {$baseUrl}/user/sections.php");
            exit();
        }

        if ($displayOrder < 1) {
            $displayOrder = 1;
        }


        /*
        |--------------------------------------------------------------------------
        | CONTENT
        |--------------------------------------------------------------------------
        |
        | Everything is stored as:
        |
        | {
        |     "text": "User written information..."
        | }
        |
        */

        $decoded = json_decode(
            $contentRaw,
            true
        );

        if (
            json_last_error() === JSON_ERROR_NONE &&
            is_array($decoded)
        ) {

            $contentJson = json_encode(
                $decoded,
                JSON_UNESCAPED_UNICODE
            );

        } else {

            $contentJson = json_encode(
                [
                    'text' => $contentRaw
                ],
                JSON_UNESCAPED_UNICODE
            );
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE EXISTING SECTION
        |--------------------------------------------------------------------------
        */

        if ($sectionId > 0) {

            $uStmt = $pdo->prepare("
                UPDATE portfolio_sections
                SET
                    section_type = ?,
                    title = ?,
                    content = ?,
                    display_order = ?,
                    is_visible = ?
                WHERE section_id = ?
                AND portfolio_id = ?
            ");

            $uStmt->execute([
                $sectionType,
                $title,
                $contentJson,
                $displayOrder,
                $isVisible,
                $sectionId,
                $pId
            ]);

            setFlash(
                'success',
                "Section '{$title}' updated successfully."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ADD NEW SECTION
        |--------------------------------------------------------------------------
        */

        else {

            $iStmt = $pdo->prepare("
                INSERT INTO portfolio_sections
                (
                    portfolio_id,
                    section_type,
                    title,
                    content,
                    display_order,
                    is_visible
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $iStmt->execute([
                $pId,
                $sectionType,
                $title,
                $contentJson,
                $displayOrder,
                $isVisible
            ]);

            setFlash(
                'success',
                "New section '{$title}' added successfully."
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE SECTION
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'delete_section') {

        $sectionId = (int)(
            $_POST['section_id'] ?? 0
        );

        if ($sectionId > 0) {

            $dStmt = $pdo->prepare("
                DELETE FROM portfolio_sections
                WHERE section_id = ?
                AND portfolio_id = ?
            ");

            $dStmt->execute([
                $sectionId,
                $pId
            ]);

            setFlash(
                'success',
                'Section deleted successfully.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header("Location: {$baseUrl}/user/sections.php");
    exit();
}


$pageTitle = 'Portfolio Sections - Portfolio Forge';
$extraCss = 'dashboard.css';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="dashboard-layout">


    <!-- ========================================================= -->
    <!-- SIDEBAR -->
    <!-- ========================================================= -->

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
                <a
                    href="<?= $baseUrl ?>/user/sections.php"
                    class="active"
                >
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


    <!-- ========================================================= -->
    <!-- MAIN CONTENT -->
    <!-- ========================================================= -->

    <main class="dashboard-content">


        <!-- ===================================================== -->
        <!-- PAGE HEADER -->
        <!-- ===================================================== -->

        <div class="content-header">

            <div>

                <h1>
                    Portfolio Sections Manager
                </h1>

                <p style="color: var(--text-secondary);">
                    Add, edit, remove, and organize the sections
                    displayed on your portfolio.
                </p>

            </div>


            <button
                type="button"
                onclick="openSectionModal(
                    0,
                    'about',
                    '',
                    '',
                    1,
                    1
                )"
                class="nav-btn btn-primary"
            >
                + Add New Section
            </button>

        </div>


        <!-- ===================================================== -->
        <!-- SECTION LIST -->
        <!-- ===================================================== -->

        <div class="panel-card">

            <h2
                class="panel-title"
                style="margin-bottom:1rem;"
            >
                Your Active Sections
            </h2>


            <?php if (empty($sections)): ?>

                <p style="color:var(--text-secondary);">

                    No sections configured yet.

                    Click
                    <strong>
                        "Add New Section"
                    </strong>
                    to begin.

                </p>

            <?php else: ?>


                <table class="data-table">

                    <thead>

                        <tr>

                            <th>
                                Order
                            </th>

                            <th>
                                Section Title
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Visibility
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach ($sections as $sec): ?>

                            <tr>


                                <!-- ORDER -->

                                <td>

                                    <strong>
                                        <?= (int)$sec['display_order'] ?>
                                    </strong>

                                </td>


                                <!-- TITLE -->

                                <td>

                                    <strong>
                                        <?= sanitize($sec['title']) ?>
                                    </strong>

                                </td>


                                <!-- TYPE -->

                                <td>

                                    <span class="badge badge-info">

                                        <?= sanitize(
                                            strtoupper(
                                                $sec['section_type']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <!-- VISIBILITY -->

                                <td>

                                    <?php if ($sec['is_visible']): ?>

                                        <span class="badge badge-success">
                                            Visible
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-warning">
                                            Hidden
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>


                                    <!-- EDIT -->

                                    <button
                                        type="button"
                                        class="nav-btn btn-outline btn-sm"
                                        onclick='openSectionModal(
                                            <?= (int)$sec["section_id"] ?>,
                                            <?= json_encode($sec["section_type"]) ?>,
                                            <?= json_encode($sec["title"]) ?>,
                                            <?= json_encode($sec["content"]) ?>,
                                            <?= (int)$sec["display_order"] ?>,
                                            <?= (int)$sec["is_visible"] ?>
                                        )'
                                    >
                                        Edit
                                    </button>


                                    <!-- DELETE -->

                                    <form
                                        action="<?= $baseUrl ?>/user/sections.php"
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm(
                                            'Delete this section?'
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
                                            value="delete_section"
                                        >

                                        <input
                                            type="hidden"
                                            name="section_id"
                                            value="<?= (int)$sec['section_id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="nav-btn btn-danger btn-sm"
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


        <!-- ===================================================== -->
        <!-- SECTION MODAL -->
        <!-- ===================================================== -->

        <div
            id="sectionModal"
            style="
                display:none;
                position:fixed;
                inset:0;
                width:100%;
                height:100%;
                background:rgba(0,0,0,.5);
                z-index:2000;
                align-items:center;
                justify-content:center;
                padding:1rem;
            "
        >


            <div
                style="
                    background:#fff;
                    border-radius:var(--radius-md);
                    max-width:700px;
                    width:100%;
                    padding:2rem;
                    max-height:90vh;
                    overflow-y:auto;
                "
            >


                <!-- MODAL TITLE -->

                <h2
                    id="modalHeaderTitle"
                    style="
                        margin-bottom:1.5rem;
                        color:var(--secondary-color);
                    "
                >
                    Add New Section
                </h2>


                <!-- FORM -->

                <form
                    id="sectionForm"
                    action="<?= $baseUrl ?>/user/sections.php"
                    method="POST"
                >


                    <!-- CSRF -->

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= generateCsrfToken() ?>"
                    >


                    <!-- ACTION -->

                    <input
                        type="hidden"
                        name="action"
                        value="save_section"
                    >


                    <!-- SECTION ID -->

                    <input
                        type="hidden"
                        id="modal_section_id"
                        name="section_id"
                        value="0"
                    >


                    <!-- ================================================= -->
                    <!-- HIDDEN CONTENT -->
                    <!-- ================================================= -->

                    <input
                        type="hidden"
                        id="section_content"
                        name="content"
                        value=""
                    >


                    <!-- ================================================= -->
                    <!-- SECTION TITLE -->
                    <!-- ================================================= -->

                    <div class="form-group">

                        <label for="modal_title">
                            Section Display Title *
                        </label>

                        <input
                            type="text"
                            id="modal_title"
                            name="title"
                            class="form-control"
                            required
                            placeholder="e.g. About Me, Education, Projects"
                        >

                    </div>


                    <!-- ================================================= -->
                    <!-- TYPE + ORDER -->
                    <!-- ================================================= -->

                    <div
                        style="
                            display:grid;
                            grid-template-columns:1fr 1fr;
                            gap:1rem;
                        "
                    >


                        <!-- TYPE -->

                        <div class="form-group">

                            <label for="modal_section_type">
                                Section Type *
                            </label>

                            <select
                                id="modal_section_type"
                                name="section_type"
                                class="form-control"
                                required
                                onchange="
                                    generateSectionFields(
                                        this.value
                                    )
                                "
                            >

                                <optgroup label="Core Sections">

                                    <option value="about">
                                        About Me
                                    </option>

                                    <option value="education">
                                        Education
                                    </option>

                                    <option value="skills">
                                        Skills
                                    </option>

                                    <option value="projects">
                                        Projects
                                    </option>

                                    <option value="experience">
                                        Experience
                                    </option>

                                    <option value="contact">
                                        Contact
                                    </option>

                                </optgroup>


                                <optgroup label="Optional Sections">

                                    <option value="certifications">
                                        Certifications
                                    </option>

                                    <option value="achievements">
                                        Achievements
                                    </option>

                                    <option value="languages">
                                        Languages
                                    </option>

                                    <option value="activities">
                                        Activities
                                    </option>

                                    <option value="interests">
                                        Interests
                                    </option>

                                </optgroup>

                            </select>

                        </div>


                        <!-- ORDER -->

                        <div class="form-group">

                            <label for="modal_display_order">
                                Display Order
                            </label>

                            <input
                                type="number"
                                id="modal_display_order"
                                name="display_order"
                                class="form-control"
                                value="1"
                                min="1"
                            >

                        </div>


                    </div>


                    <!-- ================================================= -->
                    <!-- VISIBILITY -->
                    <!-- ================================================= -->

                    <div
                        class="form-group"
                        style="
                            display:flex;
                            align-items:center;
                            gap:.5rem;
                        "
                    >

                        <input
                            type="checkbox"
                            id="modal_is_visible"
                            name="is_visible"
                            value="1"
                            checked
                        >

                        <label
                            for="modal_is_visible"
                            style="margin-bottom:0;"
                        >
                            Visible on Public Portfolio
                        </label>

                    </div>


                    <!-- ================================================= -->
                    <!-- TEXTAREA -->
                    <!-- ================================================= -->

                    <div
                        id="sectionFields"
                        style="margin-top:1rem;"
                    ></div>


                    <!-- ================================================= -->
                    <!-- BUTTONS -->
                    <!-- ================================================= -->

                    <div
                        style="
                            display:flex;
                            justify-content:flex-end;
                            gap:1rem;
                            margin-top:1.5rem;
                        "
                    >

                        <button
                            type="button"
                            class="nav-btn btn-outline"
                            onclick="closeSectionModal()"
                        >
                            Cancel
                        </button>


                        <button
                            type="submit"
                            class="nav-btn btn-primary"
                        >
                            Save Section
                        </button>

                    </div>


                </form>

            </div>

        </div>


    </main>

</div>


<script>

/*
|--------------------------------------------------------------------------
| OPEN SECTION MODAL
|--------------------------------------------------------------------------
*/

function openSectionModal(
    id,
    type,
    title,
    content,
    order,
    visible
) {

    document.getElementById(
        'modal_section_id'
    ).value = id;


    document.getElementById(
        'modal_title'
    ).value = title || '';


    document.getElementById(
        'modal_section_type'
    ).value = type || 'about';


    document.getElementById(
        'modal_display_order'
    ).value = order || 1;


    document.getElementById(
        'modal_is_visible'
    ).checked = Number(visible) === 1;


    document.getElementById(
        'modalHeaderTitle'
    ).innerText =
        id > 0
            ? 'Edit Section'
            : 'Add New Section';


    /*
    |--------------------------------------------------------------------------
    | GENERATE TEXTAREA
    |--------------------------------------------------------------------------
    */

    generateSectionFields(
        type || 'about',
        content || ''
    );


    /*
    |--------------------------------------------------------------------------
    | SHOW MODAL
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'sectionModal'
    ).style.display = 'flex';
}


/*
|--------------------------------------------------------------------------
| CLOSE MODAL
|--------------------------------------------------------------------------
*/

function closeSectionModal() {

    document.getElementById(
        'sectionModal'
    ).style.display = 'none';
}


/*
|--------------------------------------------------------------------------
| CLOSE MODAL WHEN CLICKING OUTSIDE
|--------------------------------------------------------------------------
*/

document
    .getElementById('sectionModal')
    .addEventListener(
        'click',
        function(event) {

            if (event.target === this) {

                closeSectionModal();

            }

        }
    );


/*
|--------------------------------------------------------------------------
| SECTION EXAMPLES
|--------------------------------------------------------------------------
*/

const sectionExamples = {

    about:
`Write a short introduction about yourself.

Example:

I am a BCA student interested in web development and database systems. I enjoy building practical web applications and learning new technologies.`,


    education:
`BCA — Kathmandu Model College
2024 - Present
Studying computer applications, programming, web development and database systems.

+2 Science — ABC College
2022 - 2024
Completed higher secondary education.`,


    skills:
`PHP
MySQL
HTML
CSS
JavaScript
Java
Git
Bootstrap`,


    projects:
`Portfolio Forge
A customizable portfolio builder for students and professionals.
Technologies: PHP, MySQL, HTML, CSS, JavaScript

College Attendance System
A web-based system for managing student attendance.
Technologies: PHP, MySQL, HTML, CSS`,


    experience:
`Web Developer — ABC Company
2025 - Present

Developed and maintained websites using PHP, MySQL, HTML and CSS.

Intern — XYZ Company
2024 - 2025

Worked on web development and database-related tasks.`,


    contact:
`Phone: +977-98XXXXXXXX
Location: Kathmandu, Nepal
Email: example@email.com
LinkedIn: https://linkedin.com/in/yourname
GitHub: https://github.com/yourname`,


    certifications:
`PHP & MySQL Certification — Coursera — 2025

Web Development Certification — Udemy — 2024`,


    achievements:
`Winner — College Hackathon — 2025

Best Project Award — ABC College — 2024`,


    languages:
`English — Fluent
Nepali — Native
Hindi — Intermediate`,


    activities:
`Member — College Coding Club — 2025

Participated in coding competitions, technical events and workshops.`,


    interests:
`Web Development
Programming
Photography
Gaming
Reading`

};


/*
|--------------------------------------------------------------------------
| GENERATE SECTION TEXTAREA
|--------------------------------------------------------------------------
*/

function generateSectionFields(
    type,
    content = ''
) {

    const container =
        document.getElementById(
            'sectionFields'
        );


    /*
    |--------------------------------------------------------------------------
    | GET EXISTING TEXT
    |--------------------------------------------------------------------------
    */

    let existingText = '';


    try {

        const data =
            typeof content === 'string'
                ? JSON.parse(content || '{}')
                : content;


        if (
            data &&
            typeof data === 'object'
        ) {

            existingText =
                data.text || '';

        }

    } catch (e) {

        existingText = '';

    }


    /*
    |--------------------------------------------------------------------------
    | GET EXAMPLE
    |--------------------------------------------------------------------------
    */

    const placeholder =
        sectionExamples[type] ||
        `Enter your information here.

Example:

Add the information you want to display in this section.`;


    /*
    |--------------------------------------------------------------------------
    | CREATE TEXTAREA
    |--------------------------------------------------------------------------
    */

    container.innerHTML = `

        <div class="form-group">

            <label for="field_text">

                ${formatSectionName(type)} Information:

            </label>


            <textarea
                id="field_text"
                class="form-control"
                rows="14"
                placeholder="${escapeAttribute(placeholder)}"
                style="
                    resize:vertical;
                    font-family:inherit;
                    line-height:1.6;
                "
            >${escapeHtml(existingText)}</textarea>


            <span
                class="form-help"
                style="
                    display:block;
                    margin-top:.5rem;
                "
            >

                Write your information in any format you like.
                The example in the box is only a guide.

            </span>

        </div>

    `;
}


/*
|--------------------------------------------------------------------------
| FORMAT SECTION NAME
|--------------------------------------------------------------------------
*/

function formatSectionName(type) {

    if (!type) {
        return 'Section';
    }


    return type.charAt(0).toUpperCase()
        + type.slice(1);
}


/*
|--------------------------------------------------------------------------
| SAVE TEXTAREA CONTENT AS JSON
|--------------------------------------------------------------------------
*/

document
    .getElementById('sectionForm')
    .addEventListener(
        'submit',
        function() {

            const text =
                document.getElementById(
                    'field_text'
                )?.value || '';


            const data = {

                text: text

            };


            document.getElementById(
                'section_content'
            ).value =
                JSON.stringify(data);

        }
    );


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    return String(value || '')

        .replace(
            /&/g,
            '&amp;'
        )

        .replace(
            /</g,
            '&lt;'
        )

        .replace(
            />/g,
            '&gt;'
        )

        .replace(
            /"/g,
            '&quot;'
        )

        .replace(
            /'/g,
            '&#039;'
        );
}


/*
|--------------------------------------------------------------------------
| ESCAPE ATTRIBUTE
|--------------------------------------------------------------------------
*/

function escapeAttribute(value) {

    return escapeHtml(value);

}

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>