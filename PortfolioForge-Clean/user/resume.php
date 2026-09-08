<?php
// user/resume.php

require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$pdo = getDBConnection();

$portfolio =
    getOrCreateUserPortfolio(
        $user['user_id'],
        $pdo
    );

$pId =
    $portfolio['portfolio_id'];

$baseUrl =
    '/PortfolioForge-Clean';

$errors = [];

$extractedData = null;


/*
|--------------------------------------------------------------------------
| GET CURRENT RESUME
|--------------------------------------------------------------------------
*/

$rStmt =
    $pdo->prepare(
        "SELECT *
         FROM resume
         WHERE portfolio_id = ?
         LIMIT 1"
    );

$rStmt->execute([
    $pId
]);

$resume =
    $rStmt->fetch();


/*
|--------------------------------------------------------------------------
| POST
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

        $errors[] =
            'Invalid request token. Please refresh the page and try again.';
    }


    $action =
        $_POST['action'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | UPLOAD DOCX
    |--------------------------------------------------------------------------
    */

    if (
        empty($errors) &&
        $action === 'upload_resume'
    ) {

        if (
            !isset($_FILES['resume_file']) ||
            !is_array($_FILES['resume_file'])
        ) {

            $errors[] =
                'Please select a DOCX file to upload.';
        } elseif (
            $_FILES['resume_file']['error']
            !== UPLOAD_ERR_OK
        ) {

            switch ($_FILES['resume_file']['error']) {

                case UPLOAD_ERR_NO_FILE:

                    $errors[] =
                        'Please select a DOCX file to upload.';

                    break;


                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:

                    $errors[] =
                        'The uploaded file is too large. Maximum size is 5MB.';

                    break;


                default:

                    $errors[] =
                        'The file could not be uploaded. Upload error code: '
                        . $_FILES['resume_file']['error'];

                    break;
            }
        } else {

            /*
            |--------------------------------------------------------------------------
            | DOCX MIME TYPES
            |--------------------------------------------------------------------------
            */

            $allowedMimes = [

                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

                'application/zip',

                'application/octet-stream'
            ];


            /*
            |--------------------------------------------------------------------------
            | UPLOAD
            |--------------------------------------------------------------------------
            */

            $uploadRes =
                uploadFile(
                    $_FILES['resume_file'],
                    $allowedMimes,
                    ['docx'],
                    5 * 1024 * 1024,
                    __DIR__ . '/../uploads/resumes',
                    'resume_' . $pId
                );


            if (
                !($uploadRes['success'] ?? false)
            ) {

                $errors[] =
                    'Upload Error: '
                    . ($uploadRes['error'] ?? 'Unknown upload error.');
            } else {

                $filePath =
                    $uploadRes['filepath'];

                $fileName =
                    $uploadRes['original_name'];


               

                /*
                |--------------------------------------------------------------------------
                | SAVE RESUME RECORD
                |--------------------------------------------------------------------------
                */

                if ($resume) {

                    $uStmt =
                        $pdo->prepare(
                            "UPDATE resume
                             SET
                                file_name = ?,
                                file_path = ?,
                                uploaded_at = CURRENT_TIMESTAMP
                             WHERE portfolio_id = ?"
                        );


                    $uStmt->execute([
                        $fileName,
                        $filePath,
                        $pId
                    ]);
                } else {

                    $iStmt =
                        $pdo->prepare(
                            "INSERT INTO resume
                            (
                                portfolio_id,
                                file_name,
                                file_path,
                                public_download_enabled,
                                uploaded_at
                            )
                            VALUES (?, ?, ?, 0, CURRENT_TIMESTAMP)"
                        );


                    $iStmt->execute([
                        $pId,
                        $fileName,
                        $filePath
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | EXTRACT DOCX TEXT
                |--------------------------------------------------------------------------
                */

                if (
                    !function_exists(
                        'extractTextFromDOCX'
                    )
                ) {

                    $errors[] =
                        'DOCX extraction function is not available. Please make sure extractTextFromDOCX() exists in includes/functions.php.';
                } else {

                    $text =
                        extractTextFromDOCX(
                            $filePath
                        );


                    if (
                        !is_string($text)
                    ) {

                        $text = '';
                    }


                    $text =
                        trim($text);


                    /*
                    |--------------------------------------------------------------------------
                    | PARSE RESUME
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $text !== ''
                    ) {

                        if (
                            function_exists(
                                'cleanExtractedPDFText'
                            )
                        ) {

                            $text =
                                cleanExtractedPDFText(
                                    $text
                                );
                        }


                        $extractedData =
                            parseResumeText(
                                $text
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | NORMALIZE PARSED DATA
                        |--------------------------------------------------------------------------
                        */

                        $extractedData =
                            array_merge(
                                [
                                    'full_name' => '',
                                    'email' => '',
                                    'phone' => '',
                                    'location' => '',
                                    'summary' => '',
                                    'education' => [],
                                    'skills' => [],
                                    'projects' => [],
                                    'experience' => [],
                                    'certifications' => [],
                                    'achievements' => [],
                                    'languages' => [],
                                    'social_links' => [],
                                    'activities' => [],
                                    'interests' => [],
                                    'contact' => []
                                ],
                                is_array($extractedData)
                                    ? $extractedData
                                    : []
                            );


                        setFlash(
                            'success',
                            'Resume uploaded successfully. Review the extracted information below.'
                        );
                    } else {

                        $errors[] =
                            'The DOCX file was uploaded, but no readable text could be extracted. Please make sure the file is a normal Microsoft Word .docx document.';
                    }
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE PUBLIC DOWNLOAD
    |--------------------------------------------------------------------------
    */ elseif (
        empty($errors) &&
        $action === 'toggle_public_download'
    ) {

        $enabled =
            isset(
                $_POST['public_download_enabled']
            )
            ? 1
            : 0;


        $uStmt =
            $pdo->prepare(
                "UPDATE resume
                 SET public_download_enabled = ?
                 WHERE portfolio_id = ?"
            );


        $uStmt->execute([
            $enabled,
            $pId
        ]);


        setFlash(
            'success',
            'Resume download permission updated.'
        );


        header(
            "Location: {$baseUrl}/user/resume.php"
        );

        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE RESUME
    |--------------------------------------------------------------------------
    */ elseif (
        empty($errors) &&
        $action === 'delete_resume'
    ) {

        if ($resume) {

            if (
                !empty($resume['file_path']) &&
                file_exists($resume['file_path'])
            ) {

                @unlink(
                    $resume['file_path']
                );
            }


            $dStmt =
                $pdo->prepare(
                    "DELETE FROM resume
                     WHERE portfolio_id = ?"
                );


            $dStmt->execute([
                $pId
            ]);


            setFlash(
                'success',
                'Uploaded resume removed successfully.'
            );
        }


        header(
            "Location: {$baseUrl}/user/resume.php"
        );

        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE EXTRACTED DATA
    |--------------------------------------------------------------------------
    */ elseif (
        empty($errors) &&
        $action === 'save_extracted_data'
    ) {

        /*
        |--------------------------------------------------------------------------
        | HELPER: PARSE MULTI-LINE ENTRIES
        |--------------------------------------------------------------------------
        */

        $parseEntries =
            function ($value) {

                $value =
                    trim(
                        (string)$value
                    );


                if (
                    $value === ''
                ) {

                    return [];
                }


                /*
                |------------------------------------------------------------------
                | First try blank-line separated entries.
                |------------------------------------------------------------------
                */

                $entries =
                    preg_split(
                        "/\n\s*\n/",
                        $value
                    );


                $result = [];


                foreach (
                    $entries as $entry
                ) {

                    $entry =
                        trim(
                            $entry
                        );


                    if (
                        $entry === ''
                    ) {

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------
                    | Clean each line but preserve line breaks within an entry.
                    |--------------------------------------------------------------
                    */

                    $entryLines =
                        preg_split(
                            "/\r\n|\r|\n/",
                            $entry
                        );


                    $cleanLines = [];


                    foreach (
                        $entryLines as $line
                    ) {

                        $line =
                            trim(
                                $line
                            );


                        if (
                            $line !== ''
                        ) {

                            $cleanLines[] =
                                $line;
                        }
                    }


                    if (
                        !empty($cleanLines)
                    ) {

                        $result[] =
                            implode(
                                "\n",
                                $cleanLines
                            );
                    }
                }


                return array_values(
                    array_unique(
                        $result
                    )
                );
            };


        /*
        |--------------------------------------------------------------------------
        | ABOUT
        |--------------------------------------------------------------------------
        */

        $parsedSummary =
            trim(
                $_POST['extracted_summary'] ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | SKILLS
        |--------------------------------------------------------------------------
        */

        $parsedSkills = [];


        $skillsRaw =
            trim(
                $_POST['extracted_skills'] ?? ''
            );


        if (
            $skillsRaw !== ''
        ) {

            $skillParts =
                preg_split(
                    '/[,;|\n•●▪■]+/u',
                    $skillsRaw
                );


            foreach (
                $skillParts as $skill
            ) {

                $skill =
                    trim(
                        $skill
                    );


                $skill =
                    preg_replace(
                        '/^[\s\-*•●▪■]+/u',
                        '',
                        $skill
                    );


                $skill =
                    trim(
                        $skill
                    );


                if (
                    $skill !== '' &&
                    strlen($skill) <= 150
                ) {

                    $parsedSkills[] =
                        $skill;
                }
            }


            $parsedSkills =
                array_values(
                    array_unique(
                        $parsedSkills
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | EXPERIENCE
        |--------------------------------------------------------------------------
        */

        $parsedExperience =
            $parseEntries(
                $_POST['extracted_experience'] ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | EDUCATION
        |--------------------------------------------------------------------------
        */

        $parsedEducation =
            $parseEntries(
                $_POST['extracted_education'] ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | PROJECTS
        |--------------------------------------------------------------------------
        */

        $parsedProjects =
            $parseEntries(
                $_POST['extracted_projects'] ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | CERTIFICATIONS
        |--------------------------------------------------------------------------
        */

        $parsedCertifications =
            $parseEntries(
                $_POST['extracted_certifications'] ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | LANGUAGES
        |--------------------------------------------------------------------------
        */

        $parsedLanguages =
            $parseEntries(
                $_POST['extracted_languages'] ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | ACHIEVEMENTS
        |--------------------------------------------------------------------------
        */

        $parsedAchievements =
            $parseEntries(
                $_POST['extracted_achievements'] ?? ''
            );

        $parsedActivities =
            $parseEntries(
                $_POST['extracted_activities'] ?? ''
            );

        $parsedInterests =
            $parseEntries(
                $_POST['extracted_interests'] ?? ''
            );

        $parsedContact =
            $parseEntries(
                $_POST['extracted_contact'] ?? ''
            );



        /*
        |--------------------------------------------------------------------------
        | PERSONAL INFORMATION
        |--------------------------------------------------------------------------
        */

        $parsedFullName =
            trim(
                $_POST['extracted_full_name'] ?? ''
            );


        $parsedEmail =
            trim(
                $_POST['extracted_email'] ?? ''
            );


        $parsedPhone =
            trim(
                $_POST['extracted_phone'] ?? ''
            );


        $parsedLocation =
            trim(
                $_POST['extracted_location'] ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | BUILD SECTIONS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | We intentionally keep the same format used by the existing
        | portfolio system:
        |
        | About:
        |     {"text":"..."}
        |
        | Other sections:
        |     ["item 1","item 2"]
        |
        | This avoids breaking sections.php/templates that already
        | understand the existing database structure.
        |
        */

        $sectionsToSave = [

            'about' => [

                'title' =>
                'About Me',

                'type' =>
                'about',

                'order' =>
                1,

                'data' => [

                    'text' =>
                    $parsedSummary
                ]
            ],


            'education' => [

                'title' =>
                'Education',

                'type' =>
                'education',

                'order' =>
                2,

                'data' =>
                $parsedEducation
            ],


            'skills' => [

                'title' =>
                'Skills',

                'type' =>
                'skills',

                'order' =>
                3,

                'data' =>
                $parsedSkills
            ],


            'projects' => [

                'title' =>
                'Projects',

                'type' =>
                'projects',

                'order' =>
                4,

                'data' =>
                $parsedProjects
            ],


            'experience' => [

                'title' =>
                'Work Experience',

                'type' =>
                'experience',

                'order' =>
                5,

                'data' =>
                $parsedExperience
            ],


            'certifications' => [

                'title' =>
                'Certifications',

                'type' =>
                'certifications',

                'order' =>
                6,

                'data' =>
                $parsedCertifications
            ],


            'languages' => [

                'title' =>
                'Languages',

                'type' =>
                'languages',

                'order' =>
                7,

                'data' =>
                $parsedLanguages
            ],


            'achievements' => [

                'title' =>
                'Achievements',

                'type' =>
                'achievements',

                'order' =>
                8,

                'data' =>
                $parsedAchievements
            ],

            'activities' => [

                'title' =>
                'Activities',

                'type' =>
                'activities',

                'order' =>
                9,

                'data' =>
                $parsedActivities
            ],

            'interests' => [

                'title' =>
                'Interests',

                'type' =>
                'interests',

                'order' =>
                10,

                'data' =>
                $parsedInterests
            ],

            'contact' => [

                'title' =>
                'Contact',

                'type' =>
                'contact',

                'order' =>
                11,

                'data' =>
                $parsedContact
            ]
        ];


        /*
        |--------------------------------------------------------------------------
        | SAVE USING TRANSACTION
        |--------------------------------------------------------------------------
        */

        try {

            $pdo->beginTransaction();


            foreach (
                $sectionsToSave as $section
            ) {

                /*
                |--------------------------------------------------------------
                | Determine whether this section has useful data.
                |--------------------------------------------------------------
                */

                if (
                    $section['type'] === 'about'
                ) {

                    $hasData =
                        trim(
                            $section['data']['text'] ?? ''
                        ) !== '';
                } else {

                    $hasData =
                        is_array(
                            $section['data']
                        ) &&
                        count(
                            $section['data']
                        ) > 0;
                }


                /*
                |--------------------------------------------------------------
                | Do not create/update empty sections.
                |--------------------------------------------------------------
                */

                if (
                    !$hasData
                ) {

                    continue;
                }


                /*
                |--------------------------------------------------------------
                | JSON
                |--------------------------------------------------------------
                */

                $jsonContent =
                    json_encode(
                        $section['data'],
                        JSON_UNESCAPED_UNICODE |
                            JSON_UNESCAPED_SLASHES
                    );


                if (
                    $jsonContent === false
                ) {

                    throw new Exception(
                        'Unable to encode '
                            . $section['title']
                            . ' data.'
                    );
                }


                /*
                |--------------------------------------------------------------
                | Find existing section.
                |--------------------------------------------------------------
                */

                $checkStmt =
                    $pdo->prepare(
                        "SELECT section_id
                         FROM portfolio_sections
                         WHERE portfolio_id = ?
                         AND section_type = ?
                         LIMIT 1"
                    );


                $checkStmt->execute([
                    $pId,
                    $section['type']
                ]);


                $existing =
                    $checkStmt->fetch();


                /*
                |--------------------------------------------------------------
                | UPDATE
                |--------------------------------------------------------------
                */

                if (
                    $existing
                ) {

                    $updateStmt =
                        $pdo->prepare(
                            "UPDATE portfolio_sections
                             SET
                                title = ?,
                                content = ?,
                                display_order = ?,
                                is_visible = 1
                             WHERE section_id = ?
                             AND portfolio_id = ?"
                        );


                    $updateStmt->execute([
                        $section['title'],
                        $jsonContent,
                        $section['order'],
                        $existing['section_id'],
                        $pId
                    ]);
                }


                /*
                |--------------------------------------------------------------
                | INSERT
                |--------------------------------------------------------------
                */ else {

                    $insertStmt =
                        $pdo->prepare(
                            "INSERT INTO portfolio_sections
                            (
                                portfolio_id,
                                section_type,
                                title,
                                content,
                                display_order,
                                is_visible
                            )
                            VALUES (?, ?, ?, ?, ?, 1)"
                        );


                    $insertStmt->execute([
                        $pId,
                        $section['type'],
                        $section['title'],
                        $jsonContent,
                        $section['order']
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | SAVE BASIC PROFILE DATA FROM RESUME
            |--------------------------------------------------------------------------
            |
            | Only update values that were actually extracted.
            |
            */

            if (
                $parsedFullName !== '' ||
                $parsedEmail !== '' ||
                $parsedPhone !== '' ||
                $parsedLocation !== ''
            ) {

                /*
                |--------------------------------------------------------------
                | Check which columns actually exist before attempting update.
                |--------------------------------------------------------------
                */

                $userColumns = [];


                try {

                    $columnStmt =
                        $pdo->query(
                            "SHOW COLUMNS FROM users"
                        );


                    $columns =
                        $columnStmt->fetchAll(
                            PDO::FETCH_COLUMN
                        );


                    $possibleColumns = [

                        'full_name' =>
                        $parsedFullName,

                        'email' =>
                        $parsedEmail,

                        'phone' =>
                        $parsedPhone,

                        'location' =>
                        $parsedLocation
                    ];


                    $updates = [];
                    $values = [];


                    foreach (
                        $possibleColumns as $column => $value
                    ) {

                        if (
                            in_array(
                                $column,
                                $columns,
                                true
                            ) &&
                            $value !== ''
                        ) {

                            $updates[] =
                                $column . ' = ?';

                            $values[] =
                                $value;
                        }
                    }


                    if (
                        !empty($updates)
                    ) {

                        $values[] =
                            $user['user_id'];


                        $profileStmt =
                            $pdo->prepare(
                                "UPDATE users
                                 SET "
                                    . implode(
                                        ', ',
                                        $updates
                                    )
                                    . "
                                 WHERE user_id = ?"
                            );


                        $profileStmt->execute(
                            $values
                        );
                    }
                } catch (
                    Exception $profileException
                ) {

                    /*
                    |----------------------------------------------------------
                    | Profile fields are optional.
                    | Do not fail the entire resume import if the users
                    | table does not contain those columns.
                    |----------------------------------------------------------
                    */
                }
            }


            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            setFlash(
                'success',
                'Resume information has been added to your portfolio. You can edit it from Sections.'
            );


            header(
                "Location: {$baseUrl}/user/sections.php"
            );

            exit();
        } catch (
            Exception $e
        ) {

            if (
                $pdo->inTransaction()
            ) {

                $pdo->rollBack();
            }


            $errors[] =
                'Unable to save extracted resume information: '
                . $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| REFRESH RESUME
|--------------------------------------------------------------------------
*/

$rStmt =
    $pdo->prepare(
        "SELECT *
         FROM resume
         WHERE portfolio_id = ?
         LIMIT 1"
    );

$rStmt->execute([
    $pId
]);

$resume =
    $rStmt->fetch();


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

$pageTitle =
    'Resume Management - Portfolio Forge';

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
                <a href="<?= $baseUrl ?>/user/sections.php">
                    🧩 Sections
                </a>
            </li>


            <li>
                <a
                    href="<?= $baseUrl ?>/user/resume.php"
                    class="active">
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


    <!-- CONTENT -->

    <main class="dashboard-content">


        <div class="content-header">

            <div>

                <h1>
                    Resume Upload & Extraction
                </h1>


                <p style="color: var(--text-secondary);">

                    Upload a DOCX resume to automatically
                    extract information and use it to
                    populate your portfolio.

                </p>

            </div>

        </div>


        <!-- ERRORS -->

        <?php if (!empty($errors)): ?>

            <div class="alert alert-danger">

                <ul
                    style="
                        padding-left: 1.2rem;
                        margin: 0;
                    ">

                    <?php foreach (
                        $errors as $err
                    ): ?>

                        <li>
                            <?= sanitize($err) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <!-- UPLOAD -->

        <div class="panel-card">

            <h2
                class="panel-title"
                style="margin-bottom: 1rem;">
                Upload DOCX Resume
            </h2>


            <form
                action="<?= $baseUrl ?>/user/resume.php"
                method="POST"
                enctype="multipart/form-data">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>">


                <input
                    type="hidden"
                    name="action"
                    value="upload_resume">


                <div class="form-group">

                    <label for="resume_file">
                        Select DOCX File *
                    </label>


                    <input
                        type="file"
                        id="resume_file"
                        name="resume_file"
                        class="form-control"
                        accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        required>


                    <span class="form-help">

                        DOCX only.
                        Maximum size: 5MB.

                    </span>

                </div>


                <button
                    type="submit"
                    class="nav-btn btn-primary"
                    style="margin-top: 0.5rem;">
                    Upload & Extract Data
                </button>

            </form>

        </div>


        <!-- CURRENT RESUME -->

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
                        onsubmit="return confirm('Delete uploaded resume?');">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= generateCsrfToken() ?>">


                        <input
                            type="hidden"
                            name="action"
                            value="delete_resume">


                        <button
                            type="submit"
                            class="nav-btn btn-danger btn-sm">
                            Remove File
                        </button>

                    </form>

                </div>


                <p
                    style="
                        margin-bottom: 1rem;
                        color: var(--text-secondary);
                    ">

                    <strong>
                        File Name:
                    </strong>

                    <?= sanitize(
                        $resume['file_name']
                    ) ?>


                    <br>


                    <strong>
                        Uploaded At:
                    </strong>

                    <?= sanitize(
                        $resume['uploaded_at']
                    ) ?>

                </p>


                <!-- PUBLIC DOWNLOAD -->

                <form
                    action="<?= $baseUrl ?>/user/resume.php"
                    method="POST"
                    style="
                        border-top:
                        1px solid
                        var(--border-color);
                        padding-top: 1rem;
                    ">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= generateCsrfToken() ?>">


                    <input
                        type="hidden"
                        name="action"
                        value="toggle_public_download">


                    <div
                        class="form-group"
                        style="
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                            margin-bottom: 1rem;
                        ">

                        <input
                            type="checkbox"
                            id="public_download_enabled"
                            name="public_download_enabled"
                            value="1"
                            <?= $resume['public_download_enabled']
                                ? 'checked'
                                : '' ?>>


                        <label
                            for="public_download_enabled"
                            style="margin-bottom: 0;">
                            Allow visitors to download this resume
                        </label>

                    </div>


                    <button
                        type="submit"
                        class="nav-btn btn-outline btn-sm">
                        Update Download Permission
                    </button>

                </form>

            </div>

        <?php endif; ?>


        <!-- EXTRACTED DATA -->

        <?php if ($extractedData): ?>

            <div
                class="panel-card"
                style="
                    border:
                    2px solid
                    var(--primary-color);
                ">

                <div class="panel-header">

                    <h2
                        class="panel-title"
                        style="
                            color:
                            var(--primary-color);
                        ">
                        Review Extracted Resume Data
                    </h2>

                </div>


                <p
                    style="
                        color:
                        var(--text-secondary);
                        margin-bottom: 1.5rem;
                        font-size: 0.95rem;
                    ">

                    Review the extracted information before
                    adding it to your portfolio.

                    You can edit anything that was extracted
                    incorrectly.

                </p>


                <form
                    action="<?= $baseUrl ?>/user/resume.php"
                    method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= generateCsrfToken() ?>">


                    <input
                        type="hidden"
                        name="action"
                        value="save_extracted_data">


                    <!-- FULL NAME -->

                    <div class="form-group">

                        <label for="extracted_full_name">
                            Full Name
                        </label>


                        <input
                            type="text"
                            id="extracted_full_name"
                            name="extracted_full_name"
                            class="form-control"
                            value="<?= sanitize(
                                        $extractedData['full_name'] ?? ''
                                    ) ?>">

                    </div>


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="extracted_email">
                            Email
                        </label>


                        <input
                            type="email"
                            id="extracted_email"
                            name="extracted_email"
                            class="form-control"
                            value="<?= sanitize(
                                        $extractedData['email'] ?? ''
                                    ) ?>">

                    </div>


                    <!-- PHONE -->

                    <div class="form-group">

                        <label for="extracted_phone">
                            Phone
                        </label>


                        <input
                            type="text"
                            id="extracted_phone"
                            name="extracted_phone"
                            class="form-control"
                            value="<?= sanitize(
                                        $extractedData['phone'] ?? ''
                                    ) ?>">

                    </div>


                    <!-- LOCATION -->

                    <div class="form-group">

                        <label for="extracted_location">
                            Location
                        </label>


                        <input
                            type="text"
                            id="extracted_location"
                            name="extracted_location"
                            class="form-control"
                            value="<?= sanitize(
                                        $extractedData['location'] ?? ''
                                    ) ?>">

                    </div>


                    <!-- ABOUT -->

                    <div class="form-group">

                        <label for="extracted_summary">
                            Professional Summary / About
                        </label>


                        <textarea
                            id="extracted_summary"
                            name="extracted_summary"
                            class="form-control"
                            style="min-height: 120px;"><?= sanitize(
                                                            $extractedData['summary'] ?? ''
                                                        ) ?></textarea>

                    </div>


                    <!-- SKILLS -->

                    <div class="form-group">

                        <label for="extracted_skills">
                            Skills
                        </label>


                        <textarea
                            id="extracted_skills"
                            name="extracted_skills"
                            class="form-control"
                            style="min-height: 100px;"><?= sanitize(
                                                            implode(
                                                                ', ',
                                                                $extractedData['skills'] ?? []
                                                            )
                                                        ) ?></textarea>


                        <span class="form-help">

                            Separate skills with commas,
                            semicolons, bullets, or new lines.

                        </span>

                    </div>


                    <!-- EXPERIENCE -->

                    <div class="form-group">

                        <label for="extracted_experience">
                            Work Experience
                        </label>


                        <textarea
                            id="extracted_experience"
                            name="extracted_experience"
                            class="form-control"
                            style="min-height: 180px;"><?= sanitize(
                                                            implode(
                                                                "\n\n",
                                                                $extractedData['experience'] ?? []
                                                            )
                                                        ) ?></textarea>


                        <span class="form-help">

                            Separate different jobs with an empty line.

                        </span>

                    </div>


                    <!-- EDUCATION -->

                    <div class="form-group">

                        <label for="extracted_education">
                            Education
                        </label>


                        <textarea
                            id="extracted_education"
                            name="extracted_education"
                            class="form-control"
                            style="min-height: 150px;"><?= sanitize(
                                                            implode(
                                                                "\n\n",
                                                                $extractedData['education'] ?? []
                                                            )
                                                        ) ?></textarea>


                        <span class="form-help">

                            Separate different education entries
                            with an empty line.

                        </span>

                    </div>


                    <!-- PROJECTS -->

                    <div class="form-group">

                        <label for="extracted_projects">
                            Projects
                        </label>


                        <textarea
                            id="extracted_projects"
                            name="extracted_projects"
                            class="form-control"
                            style="min-height: 150px;"><?= sanitize(
                                                            implode(
                                                                "\n\n",
                                                                $extractedData['projects'] ?? []
                                                            )
                                                        ) ?></textarea>


                        <span class="form-help">

                            Separate different projects with
                            an empty line.

                        </span>

                    </div>


                    <!-- CERTIFICATIONS -->

                    <div class="form-group">

                        <label for="extracted_certifications">
                            Certifications
                        </label>


                        <textarea
                            id="extracted_certifications"
                            name="extracted_certifications"
                            class="form-control"
                            style="min-height: 120px;"><?= sanitize(
                                                            implode(
                                                                "\n\n",
                                                                $extractedData['certifications'] ?? []
                                                            )
                                                        ) ?></textarea>


                    </div>


                    <!-- LANGUAGES -->

                    <div class="form-group">

                        <label for="extracted_languages">
                            Languages
                        </label>


                        <textarea
                            id="extracted_languages"
                            name="extracted_languages"
                            class="form-control"
                            style="min-height: 100px;"><?= sanitize(
                                                            implode(
                                                                "\n\n",
                                                                $extractedData['languages'] ?? []
                                                            )
                                                        ) ?></textarea>


                    </div>


                    <!-- ACHIEVEMENTS -->

                    <div class="form-group">

                        <label for="extracted_achievements">
                            Achievements
                        </label>


                        <textarea
                            id="extracted_achievements"
                            name="extracted_achievements"
                            class="form-control"
                            style="min-height: 120px;"><?= sanitize(
                                                            implode(
                                                                "\n\n",
                                                                $extractedData['achievements'] ?? []
                                                            )
                                                        ) ?></textarea>


                    </div>

                    <div class="form-group">

                        <label for="extracted_activities">
                            Activities
                        </label>

                        <textarea
                            id="extracted_activities"
                            name="extracted_activities"
                            class="form-control"
                            style="min-height: 120px;"><?= sanitize(
                                                            implode(
                                                                "\n\n",
                                                                $extractedData['activities'] ?? []
                                                            )
                                                        ) ?></textarea>



                        <div class="form-group">

                            <label for="extracted_interests">
                                Interests
                            </label>

                            <textarea
                                id="extracted_interests"
                                name="extracted_interests"
                                class="form-control"
                                style="min-height: 120px;"><?= sanitize(
                                                                implode(
                                                                    "\n\n",
                                                                    $extractedData['interests'] ?? []
                                                                )
                                                            ) ?></textarea>


                        </div>

                        <div class="form-group">

                            <label for="extracted_contact">
                                Contact
                            </label>

                            <textarea
                                id="extracted_contact"
                                name="extracted_contact"
                                class="form-control"
                                style="min-height: 120px;"><?= sanitize(
                                                                implode(
                                                                    "\n\n",
                                                                    $extractedData['contact'] ?? []
                                                                )
                                                            ) ?></textarea>


                        </div>

                        <!-- SAVE -->

                        <div style="margin-top: 1.5rem;">

                            <button
                                type="submit"
                                class="nav-btn btn-primary"
                                style="
                                padding:
                                0.8rem 2rem;
                            ">
                                Confirm & Add to Portfolio
                            </button>

                        </div>

                </form>

            </div>

        <?php endif; ?>


    </main>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>