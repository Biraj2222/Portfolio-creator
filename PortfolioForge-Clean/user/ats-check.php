<?php
// user/ats-check.php

require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$pdo = getDBConnection();

$portfolio = getOrCreateUserPortfolio(
    $user['user_id'],
    $pdo
);

$pId = $portfolio['portfolio_id'];

$baseUrl = '/PortfolioForge-Clean';

$errors = [];

$jobDescription = '';

$matchedKeywords = [];

$missingKeywords = [];

$matchPercentage = null;


/*
|--------------------------------------------------------------------------
| KEYWORDS
|--------------------------------------------------------------------------
|
| These are common technical and job-related keywords.
|
| The checker first finds which of these keywords appear in the
| job description and then checks whether they appear in the
| user's existing portfolio sections.
|
*/

$keywords = [

    // Programming & IT
    'php',
    'java',
    'python',
    'javascript',
    'typescript',
    'c',
    'c++',
    'c#',
    'html',
    'css',
    'bootstrap',
    'laravel',
    'react',
    'vue',
    'angular',
    'node.js',
    'sql',
    'mysql',
    'postgresql',
    'mongodb',
    'git',
    'github',
    'docker',
    'linux',
    'rest api',
    'api',
    'json',
    'aws',
    'azure',
    'google cloud',
    'cybersecurity',
    'machine learning',
    'data analysis',
    'data processing',
    'database management',
    'web development',
    'software development',
    'object oriented programming',
    'oop',
    'technical support',
    'troubleshooting',

    // Microsoft / Office & Productivity
    'microsoft office',
    'microsoft word',
    'microsoft excel',
    'microsoft powerpoint',
    'excel',
    'powerpoint',
    'word',
    'google workspace',
    'google docs',
    'google sheets',
    'data entry',
    'documentation',
    'record keeping',

    // Business & Management
    'business management',
    'business analysis',
    'project management',
    'project coordination',
    'operations',
    'operations management',
    'strategic planning',
    'planning',
    'administration',
    'office management',
    'budgeting',
    'financial management',
    'reporting',
    'financial reporting',
    'research',
    'market research',
    'process improvement',

    // Accounting & Finance
    'accounting',
    'bookkeeping',
    'auditing',
    'financial analysis',
    'payroll',
    'taxation',
    'accounts payable',
    'accounts receivable',

    // Marketing & Sales
    'marketing',
    'digital marketing',
    'content marketing',
    'social media marketing',
    'email marketing',
    'seo',
    'sem',
    'branding',
    'advertising',
    'sales',
    'sales management',
    'lead generation',
    'customer acquisition',
    'crm',
    'market analysis',

    // Design & Creative
    'graphic design',
    'web design',
    'ui design',
    'ux design',
    'ui/ux',
    'figma',
    'adobe photoshop',
    'photoshop',
    'adobe illustrator',
    'illustrator',
    'video editing',
    'photography',
    'content creation',
    'creative writing',
    'content writing',
    'copywriting',

    // Education & Training
    'teaching',
    'training',
    'tutoring',
    'lesson planning',
    'curriculum development',
    'classroom management',
    'instruction',
    'education',
    'academic writing',

    // Human Resources
    'human resources',
    'hr',
    'recruitment',
    'talent acquisition',
    'employee relations',
    'performance management',
    'interviewing',
    'onboarding',

    // Customer Service
    'customer service',
    'customer support',
    'client relations',
    'customer relations',
    'complaint resolution',
    'technical support',
    'help desk',

    // Communication & Workplace Skills
    'communication',
    'teamwork',
    'collaboration',
    'leadership',
    'problem solving',
    'critical thinking',
    'decision making',
    'time management',
    'organization',
    'adaptability',
    'creativity',
    'attention to detail',
    'multitasking',
    'presentation',
    'public speaking',
    'negotiation',
    'interpersonal skills',
    'work ethic',

    // Common Professional Skills
    'project coordination',
    'client management',
    'vendor management',
    'inventory management',
    'quality assurance',
    'quality control',
    'risk management',
    'event management',
    'social media',
    'research and analysis',
    'report writing',
    'professional writing',
    'problem resolution'
];


/*
|--------------------------------------------------------------------------
| FETCH PORTFOLIO SECTIONS
|--------------------------------------------------------------------------
*/

$sStmt = $pdo->prepare("
    SELECT
        section_type,
        title,
        content,
        is_visible
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
| CONVERT SECTION CONTENT TO TEXT
|--------------------------------------------------------------------------
|
| Your sections.php stores content as:
|
| {
|     "text": "..."
| }
|
| This function also safely handles arrays or plain text.
|
*/

function getSectionText($content)
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


    if (
        is_array($content) &&
        isset($content['text'])
    ) {

        return (string)$content['text'];
    }


    if (
        is_array($content)
    ) {

        $parts = [];


        foreach ($content as $item) {

            if (
                is_string($item)
            ) {

                $parts[] = $item;

            } elseif (
                is_array($item)
            ) {

                if (
                    isset($item['text'])
                ) {

                    $parts[] =
                        (string)$item['text'];

                } else {

                    $parts[] =
                        implode(
                            ' ',
                            array_map(
                                'strval',
                                $item
                            )
                        );
                }
            }
        }


        return implode(
            ' ',
            $parts
        );
    }


    return '';
}


/*
|--------------------------------------------------------------------------
| BUILD PORTFOLIO TEXT
|--------------------------------------------------------------------------
*/

$portfolioText = '';


foreach (
    $sections as $section
) {

    /*
    |--------------------------------------------------------------------------
    | Include only visible sections
    |--------------------------------------------------------------------------
    */

    if (
        isset($section['is_visible']) &&
        !$section['is_visible']
    ) {

        continue;
    }


    $portfolioText .= ' ';


    $portfolioText .=
        $section['title'] ?? '';


    $portfolioText .= ' ';


    $portfolioText .=
        getSectionText(
            $section['content'] ?? ''
        );
}


/*
|--------------------------------------------------------------------------
| HANDLE ATS CHECK
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


    if (
        empty($errors)
    ) {

        $jobDescription =
            trim(
                $_POST['job_description'] ?? ''
            );


        if (
            $jobDescription === ''
        ) {

            $errors[] =
                'Please paste a job description.';
        }

        elseif (
            strlen($jobDescription) < 20
        ) {

            $errors[] =
                'Please enter a complete job description.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK KEYWORDS
    |--------------------------------------------------------------------------
    */

    if (
        empty($errors)
    ) {

        $jobText =
            strtolower(
                $jobDescription
            );


        $portfolioTextLower =
            strtolower(
                $portfolioText
            );


        $jobKeywords = [];


        /*
        |--------------------------------------------------------------------------
        | Find keywords inside job description
        |--------------------------------------------------------------------------
        */

        foreach (
            $keywords as $keyword
        ) {

            $keywordLower =
                strtolower(
                    $keyword
                );


            if (
                strpos(
                    $jobText,
                    $keywordLower
                ) !== false
            ) {

                $jobKeywords[] =
                    $keyword;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Compare with portfolio
        |--------------------------------------------------------------------------
        */

        foreach (
            $jobKeywords as $keyword
        ) {

            $keywordLower =
                strtolower(
                    $keyword
                );


            if (
                strpos(
                    $portfolioTextLower,
                    $keywordLower
                ) !== false
            ) {

                $matchedKeywords[] =
                    $keyword;

            } else {

                $missingKeywords[] =
                    $keyword;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | MATCH PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $totalKeywords =
            count(
                $jobKeywords
            );


        if (
            $totalKeywords > 0
        ) {

            $matchPercentage =
                round(
                    (
                        count($matchedKeywords)
                        /
                        $totalKeywords
                    ) * 100
                );

        } else {

            $matchPercentage = 0;
        }
    }
}


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

$pageTitle =
    'ATS Resume Check - Portfolio Forge';

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

                <a
                    href="<?= $baseUrl ?>/user/dashboard.php"
                >
                    📊 Overview
                </a>

            </li>


            <li>

                <a
                    href="<?= $baseUrl ?>/user/edit-portfolio.php"
                >
                    ✏️ Edit Portfolio
                </a>

            </li>


            <li>

                <a
                    href="<?= $baseUrl ?>/user/sections.php"
                >
                    🧩 Sections
                </a>

            </li>


            <li>

                <a
                    href="<?= $baseUrl ?>/user/resume.php"
                >
                    📄 Resume Upload
                </a>

            </li>


            <li>

                <a
                    href="<?= $baseUrl ?>/user/templates.php"
                >
                    🎨 Templates
                </a>

            </li>


            <li>

                <a
                    href="<?= $baseUrl ?>/user/statistics.php"
                >
                    📈 Statistics
                </a>

            </li>


            <li>

                <a
                    href="<?= $baseUrl ?>/user/profile.php"
                >
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
                    ATS Resume Check
                </h1>


                <p
                    style="
                        color:
                        var(--text-secondary);
                    "
                >

                    Compare your existing portfolio
                    with a specific job description
                    to identify relevant keywords.

                </p>

            </div>

        </div>


        <!-- ERRORS -->

        <?php if (!empty($errors)): ?>

            <div class="alert alert-danger">

                <ul
                    style="
                        padding-left:1.2rem;
                        margin:0;
                    "
                >

                    <?php foreach (
                        $errors as $error
                    ): ?>

                        <li>

                            <?= sanitize($error) ?>

                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <!-- JOB DESCRIPTION -->

        <div class="panel-card">

            <h2
                class="panel-title"
                style="
                    margin-bottom:1rem;
                "
            >
                Job Description
            </h2>


            <p
                style="
                    color:var(--text-secondary);
                    margin-bottom:1rem;
                "
            >

                Paste the job description below.
                The system will identify relevant
                keywords and compare them with your
                existing portfolio information.

            </p>


            <form
                action="<?= $baseUrl ?>/user/ats-check.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>"
                >


                <div class="form-group">

                    <label
                        for="job_description"
                    >
                        Job Description *
                    </label>


                    <textarea
                        id="job_description"
                        name="job_description"
                        class="form-control"
                        required
                        style="
                            min-height:250px;
                            resize:vertical;
                        "
                        placeholder="Paste the job description here..."
                    ><?= sanitize($jobDescription) ?></textarea>

                </div>


                <button
                    type="submit"
                    class="nav-btn btn-primary"
                >
                    Check Keywords
                </button>

            </form>

        </div>


        <!-- RESULTS -->

        <?php if (
            $matchPercentage !== null &&
            empty($errors)
        ): ?>

            <div class="panel-card">

                <h2
                    class="panel-title"
                    style="
                        margin-bottom:1rem;
                    "
                >
                    ATS Keyword Results
                </h2>


                <?php if (
                    empty($matchedKeywords) &&
                    empty($missingKeywords)
                ): ?>

                    <p
                        style="
                            color:
                            var(--text-secondary);
                        "
                    >

                        No keywords from the current
                        keyword list were found in the
                        job description.

                    </p>

                <?php else: ?>


                    <!-- MATCH PERCENTAGE -->

                    <div
                        style="
                            padding:1.5rem;
                            margin-bottom:1.5rem;
                            border:1px solid var(--border-color);
                            border-radius:var(--radius-md);
                            text-align:center;
                        "
                    >

                        <div
                            style="
                                font-size:2rem;
                                font-weight:700;
                                color:var(--primary-color);
                            "
                        >

                            <?= (int)$matchPercentage ?>%

                        </div>


                        <div
                            style="
                                color:
                                var(--text-secondary);
                            "
                        >

                            Keyword Match

                        </div>

                    </div>


                    <!-- MATCHED -->

                    <div
                        style="
                            margin-bottom:1.5rem;
                        "
                    >

                        <h3
                            style="
                                margin-bottom:.75rem;
                            "
                        >
                            Matched Keywords
                        </h3>


                        <?php if (
                            !empty($matchedKeywords)
                        ): ?>

                            <div
                                style="
                                    display:flex;
                                    flex-wrap:wrap;
                                    gap:.5rem;
                                "
                            >

                                <?php foreach (
                                    $matchedKeywords
                                    as $keyword
                                ): ?>

                                    <span
                                        class="badge badge-success"
                                    >

                                        ✓
                                        <?= sanitize($keyword) ?>

                                    </span>

                                <?php endforeach; ?>

                            </div>

                        <?php else: ?>

                            <p
                                style="
                                    color:
                                    var(--text-secondary);
                                "
                            >

                                No matching keywords were
                                found in your portfolio.

                            </p>

                        <?php endif; ?>

                    </div>


                    <!-- MISSING -->

                    <div>

                        <h3
                            style="
                                margin-bottom:.75rem;
                            "
                        >
                            Missing Keywords
                        </h3>


                        <?php if (
                            !empty($missingKeywords)
                        ): ?>

                            <div
                                style="
                                    display:flex;
                                    flex-wrap:wrap;
                                    gap:.5rem;
                                "
                            >

                                <?php foreach (
                                    $missingKeywords
                                    as $keyword
                                ): ?>

                                    <span
                                        class="badge badge-warning"
                                    >

                                        ✗
                                        <?= sanitize($keyword) ?>

                                    </span>

                                <?php endforeach; ?>

                            </div>


                            <p
                                style="
                                    color:
                                    var(--text-secondary);
                                    margin-top:1rem;
                                "
                            >

                                Only add missing keywords to your
                                portfolio if you genuinely have
                                the related skill or experience.

                            </p>


                            <a
                                href="<?= $baseUrl ?>/user/sections.php"
                                class="nav-btn btn-outline"
                                style="
                                    margin-top:.5rem;
                                "
                            >

                                Edit Portfolio Sections

                            </a>

                        <?php else: ?>

                            <p
                                style="
                                    color:
                                    var(--text-secondary);
                                "
                            >

                                All detected job keywords were
                                found in your portfolio.

                            </p>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

        <?php endif; ?>


        <!-- INFORMATION -->

        <div class="panel-card">

            <h2
                class="panel-title"
                style="
                    margin-bottom:1rem;
                "
            >
                About This Check
            </h2>


            <p
                style="
                    color:
                    var(--text-secondary);
                    margin-bottom:.75rem;
                "
            >

                This tool performs a basic keyword comparison
                between a job description and the information
                already present in your portfolio.

            </p>


            <p
                style="
                    color:
                    var(--text-secondary);
                    margin:0;
                "
            >

                A missing keyword does not necessarily mean
                you are unqualified. Only include skills or
                experience that accurately represent your
                background.

            </p>

        </div>


    </main>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>