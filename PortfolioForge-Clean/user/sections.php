<?php
// user/sections.php

require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$pdo = getDBConnection();

$portfolio = getOrCreateUserPortfolio(
    $user['user_id'],
    $pdo
);

$pId = $portfolio['portfolio_id'];

$baseUrl = '/PortfolioForge-Clean';


/*
|--------------------------------------------------------------------------
| HELPER: CONVERT SECTION CONTENT TO TEXT
|--------------------------------------------------------------------------
|
| All portfolio sections are stored consistently as:
|
| {
|     "text": "..."
| }
|
| This function also handles old/imported array formats.
|
*/

function normalizeSectionContent($content)
{
    if (is_string($content)) {

        $decoded = json_decode(
            $content,
            true
        );

        if (
            json_last_error() === JSON_ERROR_NONE
        ) {
            $content = $decoded;
        } else {
            return $content;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Already in correct format
    |--------------------------------------------------------------------------
    */

    if (
        is_array($content) &&
        array_key_exists('text', $content)
    ) {

        return (string)$content['text'];
    }


    if (is_array($content)) {

        $parts = [];


        foreach ($content as $item) {

            if (is_string($item)) {

                $parts[] = trim($item);

            } elseif (is_array($item)) {

                /*
                |--------------------------------------------------------------------------
                | If an imported item itself contains text
                |--------------------------------------------------------------------------
                */

                if (
                    isset($item['text']) &&
                    is_string($item['text'])
                ) {

                    $parts[] =
                        trim($item['text']);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Convert structured data to readable text
                    |--------------------------------------------------------------------------
                    */

                    $parts[] =
                        trim(
                            implode(
                                "\n",
                                array_map(
                                    function ($value) {

                                        if (
                                            is_array($value)
                                        ) {

                                            return implode(
                                                ', ',
                                                array_map(
                                                    'strval',
                                                    $value
                                                )
                                            );
                                        }

                                        return (string)$value;
                                    },
                                    $item
                                )
                            )
                        );
                }

            } else {

                $parts[] = (string)$item;
            }
        }


        return implode(
            "\n\n",
            array_filter(
                $parts,
                function ($value) {
                    return trim($value) !== '';
                }
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Empty / unknown content
    |--------------------------------------------------------------------------
    */

    if (
        is_null($content)
    ) {

        return '';
    }


    return (string)$content;
}


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

$sStmt->execute([
    $pId
]);

$sections = $sStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| FETCH RESUME
|--------------------------------------------------------------------------
*/

$rStmt = $pdo->prepare("
    SELECT *
    FROM resume
    WHERE portfolio_id = ?
    LIMIT 1
");

$rStmt->execute([
    $pId
]);

$resume = $rStmt->fetch();


/*
|--------------------------------------------------------------------------
| NORMALIZE OLD IMPORTED CONTENT
|--------------------------------------------------------------------------
|
| This is important.
|
| Resume imports may have created:
|
| skills:
| ["PHP","MySQL","HTML"]
|
| We convert that into:
|
| {"text":"PHP\nMySQL\nHTML"}
|
| This makes the old imported sections compatible with the
| rest of the portfolio system.
|
*/

foreach ($sections as &$sec) {

    $originalContent =
        $sec['content'] ?? '';

    $normalizedText =
        normalizeSectionContent(
            $originalContent
        );


    $normalizedJson =
        json_encode(
            [
                'text' =>
                    $normalizedText
            ],
            JSON_UNESCAPED_UNICODE
        );


    $currentDecoded =
        json_decode(
            $originalContent,
            true
        );


    $needsMigration = false;


    if (
        !is_array($currentDecoded) ||
        !array_key_exists(
            'text',
            $currentDecoded
        )
    ) {

        $needsMigration = true;
    }


    if ($needsMigration) {

        $migrateStmt = $pdo->prepare("
            UPDATE portfolio_sections
            SET content = ?
            WHERE section_id = ?
            AND portfolio_id = ?
        ");

        $migrateStmt->execute([
            $normalizedJson,
            $sec['section_id'],
            $pId
        ]);


        $sec['content'] =
            $normalizedJson;
    }
}

unset($sec);


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    if (
        !verifyCsrfToken(
            $_POST['csrf_token'] ?? ''
        )
    ) {

        setFlash(
            'danger',
            'Invalid request token. Please refresh the page and try again.'
        );

        header(
            "Location: {$baseUrl}/user/sections.php"
        );

        exit();
    }


    $action =
        $_POST['action'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | SAVE SECTION
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'save_section'
    ) {

        $sectionId =
            (int)(
                $_POST['section_id'] ?? 0
            );


        $sectionType =
            trim(
                $_POST['section_type'] ?? 'about'
            );


        $title =
            trim(
                $_POST['title'] ?? ''
            );


        $displayOrder =
            (int)(
                $_POST['display_order'] ?? 1
            );


        $isVisible =
            isset(
                $_POST['is_visible']
            )
                ? 1
                : 0;


        $contentRaw =
            $_POST['content'] ?? '';


        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            empty($title)
        ) {

            setFlash(
                'danger',
                'Section title is required.'
            );

            header(
                "Location: {$baseUrl}/user/sections.php"
            );

            exit();
        }


        if (
            empty($sectionType)
        ) {

            $sectionType = 'about';
        }


        if (
            $displayOrder < 1
        ) {

            $displayOrder = 1;
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE CONTENT
        |--------------------------------------------------------------------------
        |
        | Always save:
        |
        | {
        |     "text": "..."
        | }
        |
        */

        $contentText =
            normalizeSectionContent(
                $contentRaw
            );


        $contentJson =
            json_encode(
                [
                    'text' =>
                        $contentText
                ],
                JSON_UNESCAPED_UNICODE
            );


        if (
            $contentJson === false
        ) {

            setFlash(
                'danger',
                'Unable to save section content.'
            );

            header(
                "Location: {$baseUrl}/user/sections.php"
            );

            exit();
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE EXISTING SECTION
        |--------------------------------------------------------------------------
        */

        if (
            $sectionId > 0
        ) {

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

    elseif (
        $action === 'delete_section'
    ) {

        $sectionId =
            (int)(
                $_POST['section_id'] ?? 0
            );


        if (
            $sectionId > 0
        ) {

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

    header(
        "Location: {$baseUrl}/user/sections.php"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

$pageTitle =
    'Portfolio Sections - Portfolio Forge';

$extraCss =
    'dashboard.css';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="dashboard-layout">


    <!-- SIDEBAR -->

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


    <!-- MAIN CONTENT -->

    <main class="dashboard-content">


        <!-- HEADER -->

        <div class="content-header">

            <div>

                <h1>
                    Portfolio Sections Manager
                </h1>

                <p style="color:var(--text-secondary);">

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


        <!-- UPLOADED RESUME DOWNLOAD CARD -->

        <?php if (!empty($resume) && !empty($resume['file_path']) && file_exists($resume['file_path'])): ?>

            <div
                class="panel-card"
                style="
                    margin-bottom: 1.5rem;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    flex-wrap: wrap;
                    gap: 1rem;
                    background: #f8fafc;
                    border-left: 4px solid var(--primary-color);
                "
            >

                <div>

                    <strong style="display: block; font-size: 1.05rem; color: var(--text-color);">
                        📄 Uploaded Resume / CV
                    </strong>

                    <span style="color: var(--text-secondary); font-size: 0.9rem;">
                        File: <?= sanitize($resume['file_name']) ?>
                        (<?= !empty($resume['public_download_enabled']) ? 'Public Download Enabled' : 'Private' ?>)
                    </span>

                </div>


                <div style="display: flex; gap: 0.5rem; align-items: center;">

                    <a
                        href="<?= $baseUrl ?>/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>"
                        download
                        class="nav-btn btn-primary btn-sm"
                        style="display: inline-flex; align-items: center; gap: 0.4rem;"
                    >
                        📥 Download CV
                    </a>

                    <a
                        href="<?= $baseUrl ?>/user/resume.php"
                        class="nav-btn btn-outline btn-sm"
                    >
                        Manage Resume
                    </a>

                </div>

            </div>

        <?php endif; ?>


        <!-- SECTION LIST -->

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

                                <td>

                                    <strong>
                                        <?= (int)$sec['display_order'] ?>
                                    </strong>

                                </td>


                                <td>

                                    <strong>
                                        <?= sanitize(
                                            $sec['title']
                                        ) ?>
                                    </strong>

                                </td>


                                <td>

                                    <span class="badge badge-info">

                                        <?= sanitize(
                                            strtoupper(
                                                $sec['section_type']
                                            )
                                        ) ?>

                                    </span>

                                </td>


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


                                <td>

                                    <button
                                        type="button"
                                        class="nav-btn btn-outline btn-sm"
                                        onclick='openSectionModal(
                                            <?= (int)$sec["section_id"] ?>,
                                            <?= json_encode(
                                                $sec["section_type"],
                                                JSON_HEX_TAG |
                                                JSON_HEX_APOS |
                                                JSON_HEX_QUOT |
                                                JSON_HEX_AMP
                                            ) ?>,
                                            <?= json_encode(
                                                $sec["title"],
                                                JSON_HEX_TAG |
                                                JSON_HEX_APOS |
                                                JSON_HEX_QUOT |
                                                JSON_HEX_AMP
                                            ) ?>,
                                            <?= json_encode(
                                                $sec["content"],
                                                JSON_HEX_TAG |
                                                JSON_HEX_APOS |
                                                JSON_HEX_QUOT |
                                                JSON_HEX_AMP
                                            ) ?>,
                                            <?= (int)$sec["display_order"] ?>,
                                            <?= (int)$sec["is_visible"] ?>
                                        )'
                                    >
                                        Edit
                                    </button>


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


        <!-- MODAL -->

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

                <h2
                    id="modalHeaderTitle"
                    style="
                        margin-bottom:1.5rem;
                        color:var(--secondary-color);
                    "
                >
                    Add New Section
                </h2>


                <form
                    id="sectionForm"
                    action="<?= $baseUrl ?>/user/sections.php"
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
                        value="save_section"
                    >


                    <input
                        type="hidden"
                        id="modal_section_id"
                        name="section_id"
                        value="0"
                    >


                    <input
                        type="hidden"
                        id="section_content"
                        name="content"
                        value=""
                    >


                    <!-- TITLE -->

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


                    <!-- TYPE + ORDER -->

                    <div
                        style="
                            display:grid;
                            grid-template-columns:1fr 1fr;
                            gap:1rem;
                        "
                    >

                        <div class="form-group">

                            <label for="modal_section_type">
                                Section Type *
                            </label>


                            <select
                                id="modal_section_type"
                                name="section_type"
                                class="form-control"
                                required
                                onchange="generateSectionFields(this.value)"
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


                    <!-- VISIBILITY -->

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


                    <!-- CONTENT -->

                    <div
                        id="sectionFields"
                        style="margin-top:1rem;"
                    ></div>


                    <!-- BUTTONS -->

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
| OPEN MODAL
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
    ).value = id || 0;


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
    ).checked =
        Number(visible) === 1;


    document.getElementById(
        'modalHeaderTitle'
    ).innerText =
        Number(id) > 0
            ? 'Edit Section'
            : 'Add New Section';


    generateSectionFields(
        type || 'about',
        content || ''
    );


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
| CLICK OUTSIDE MODAL
|--------------------------------------------------------------------------
*/

document
    .getElementById('sectionModal')
    .addEventListener(
        'click',
        function(event) {

            if (
                event.target === this
            ) {

                closeSectionModal();
            }
        }
    );


/*
|--------------------------------------------------------------------------
| EXTRACT TEXT FROM ANY CONTENT FORMAT
|--------------------------------------------------------------------------
*/

function extractSectionText(content) {

    if (!content) {
        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | If PHP passed an object directly
    |--------------------------------------------------------------------------
    */

    if (
        typeof content === 'object'
    ) {

        if (
            typeof content.text === 'string'
        ) {

            return content.text;
        }


        if (
            Array.isArray(content)
        ) {

            return content
                .map(function(item) {

                    if (
                        typeof item === 'string'
                    ) {

                        return item;
                    }


                    if (
                        item &&
                        typeof item.text === 'string'
                    ) {

                        return item.text;
                    }


                    return String(item);

                })
                .join('\n\n');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | JSON string
    |--------------------------------------------------------------------------
    */

    if (
        typeof content === 'string'
    ) {

        try {

            const parsed =
                JSON.parse(content);


            if (
                parsed &&
                typeof parsed.text === 'string'
            ) {

                return parsed.text;
            }


            if (
                Array.isArray(parsed)
            ) {

                return parsed
                    .map(function(item) {

                        if (
                            typeof item === 'string'
                        ) {

                            return item;
                        }


                        if (
                            item &&
                            typeof item.text === 'string'
                        ) {

                            return item.text;
                        }


                        return String(item);

                    })
                    .join('\n\n');
            }

        } catch (e) {

            /*
            |--------------------------------------------------------------------------
            | It is already plain text
            |--------------------------------------------------------------------------
            */

            return content;
        }
    }


    return '';
}


/*
|--------------------------------------------------------------------------
| GENERATE SECTION FIELDS
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


    const existingText =
        extractSectionText(
            content
        );


    const placeholder =
        sectionExamples[type] ||
        `Enter your information here.

Example:

Add the information you want to display in this section.`;


    container.innerHTML = `

        <div class="form-group">

            <label for="field_text">

                ${formatSectionName(type)}
                Information:

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
| SAVE TEXTAREA CONTENT
|--------------------------------------------------------------------------
*/

document
    .getElementById('sectionForm')
    .addEventListener(
        'submit',
        function() {

            const field =
                document.getElementById(
                    'field_text'
                );


            const text =
                field
                    ? field.value
                    : '';


            document.getElementById(
                'section_content'
            ).value =
                JSON.stringify({
                    text: text
                });
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