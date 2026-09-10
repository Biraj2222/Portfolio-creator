<?php

/**
 * Classic ATS-Friendly Portfolio Template
 *
 * @var array $user
 * @var array $portfolio
 * @var array $sections
 * @var array|null $resume
 */

$accentColor = $portfolio['accent_color'] ?? '#333333';
$fontFamily  = $portfolio['font_family'] ?? 'Arial, sans-serif';

$sections = $sections ?? [];

/*
 * Convert section JSON into an array.
 */
function classicContent($section)
{
    $content = json_decode($section['content'] ?? '', true);

    if (!is_array($content)) {
        $content = [
            'text' => $section['content'] ?? ''
        ];
    }

    return $content;
}

/*
 * Get a readable value.
 */
function classicValue($value)
{
    if (is_array($value)) {
        return implode(', ', array_map('strval', $value));
    }

    return (string)$value;
}
?>

<style>
    /* =========================================================
       CLASSIC TEMPLATE
       Traditional / Elegant Portfolio
       ========================================================= */

    .tpl-classic {
        --classic-accent: <?= sanitize($accentColor) ?>;
        --classic-font: <?= sanitize($fontFamily) ?>;

        font-family: var(--classic-font);
        color: #292929;
        background: #f4f1eb;
        line-height: 1.65;
        min-height: 100vh;
    }

    .tpl-classic .classic-container {
        max-width: 980px;
        margin: 0 auto;
        padding: 55px 45px 75px;
    }


    /* =========================================================
       MAIN PAPER
       ========================================================= */

    .tpl-classic .classic-container {
        position: relative;
    }


    /* =========================================================
       HEADER
       ========================================================= */

   .tpl-classic .classic-header {
    position: relative;

    text-align: left;

    background: #fffdf9;

    padding: 58px 55px 42px;

    margin-bottom: 48px;

    border: 1px solid #ddd7cc;
    border-radius: 4px;

    box-shadow:
        0 10px 30px rgba(50, 43, 32, 0.06);
}

    .tpl-classic .classic-header::before {
        content: "";

        position: absolute;

        top: 13px;
        left: 13px;
        right: 13px;
        bottom: 13px;

        border: 1px solid rgba(120, 110, 95, 0.18);

        pointer-events: none;
    }

    .tpl-classic .classic-header>* {
        position: relative;
        z-index: 1;
    }


    /* =========================================================
       PROFILE IMAGE
       ========================================================= */

    .tpl-classic .classic-avatar {
        width: 105px;
        height: 105px;

        object-fit: cover;

        border-radius: 50%;

        margin: 0 auto 18px;

        display: block;

        border: 3px solid #fffdf9;

        outline: 1px solid #cfc7ba;
    }


    /* =========================================================
       NAME
       ========================================================= */

   .tpl-classic .classic-name {
    margin: 0;

    color: #242424;

    font-family: Georgia, "Times New Roman", serif;

    font-size: clamp(2.2rem, 5vw, 3.4rem);

    line-height: 1.1;

    font-weight: 700;

    letter-spacing: -0.025em;
}


    /* =========================================================
       TITLE
       ========================================================= */

.tpl-classic .classic-title {
    position: absolute;

    top: 58px;
    right: 119px;

    color: var(--classic-accent);

    font-family: Georgia, "Times New Roman", serif;

    font-size: 1.08rem;

    font-style: italic;

    letter-spacing: 0.02em;
}


    /* =========================================================
       CONTACT
       ========================================================= */

    .tpl-classic .classic-contact {
        display: flex;

        justify-content: center;
        flex-wrap: wrap;

        gap: 0;

        margin-top: 23px;
        padding-top: 18px;

        border-top: 1px solid #ddd7cc;

        color: #5f5a52;

        font-size: 0.88rem;
        right: -28px;
    }

    .tpl-classic .classic-contact span {
        display: inline-flex;
        align-items: center;
    }

    .tpl-classic .classic-contact span:not(:last-child)::after {
        content: "•";

        margin: 0 12px;

        color: var(--classic-accent);
    }

    .tpl-classic .classic-contact a {
        color: inherit;

        text-decoration: none;

        border-bottom: 1px solid #aaa39a;

        transition: 0.2s ease;
    }

    .tpl-classic .classic-contact a:hover {
        color: var(--classic-accent);

        border-color: var(--classic-accent);
    }


    /* =========================================================
       SECTIONS
       ========================================================= */

    .tpl-classic .classic-section {
        position: relative;

        margin-bottom: 36px;

        padding: 0 5px;
    }

    .tpl-classic .classic-section:last-child {
        margin-bottom: 0;
    }


    /* =========================================================
       SECTION TITLE
       ========================================================= */

    .tpl-classic .classic-section-title {
        display: flex;
        align-items: center;

        gap: 15px;

        margin: 0 0 17px;

        color: #292929;

        font-family: Georgia, "Times New Roman", serif;

        font-size: 1.18rem;

        line-height: 1.3;

        font-weight: 700;

        letter-spacing: 0.08em;

        text-transform: uppercase;
    }

    .tpl-classic .classic-section-title::after {
        content: "";

        flex: 1;

        height: 1px;

        background: #d6d0c6;
    }


    /* =========================================================
       TEXT
       ========================================================= */

    .tpl-classic .classic-text {
        margin: 0;

        color: #4e4b46;

        font-size: 0.97rem;

        line-height: 1.8;

        white-space: pre-line;
    }


    /* =========================================================
       ENTRIES
       ========================================================= */

    .tpl-classic .classic-entry {
        position: relative;

        margin-bottom: 20px;

        padding: 16px 24px;

        background: #fffdf9;

        border: 1px solid #e0dbd2;

        border-radius: 4px;

        text-align: left;

        box-shadow:
            0 5px 18px rgba(50, 43, 32, 0.035);

        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .tpl-classic .classic-entry:hover {
        transform: translateY(-3px);
        border-color: var(--classic-accent);
        box-shadow: 0 10px 25px rgba(50, 43, 32, 0.08);
    }

    .tpl-classic .classic-entry:last-child {
        margin-bottom: 0;
    }


    /* =========================================================
       ENTRY TITLE
       ========================================================= */

    .tpl-classic .classic-entry-title {
        margin: 0 0 5px;

        color: #292929;

        font-family: Georgia, "Times New Roman", serif;

        font-size: 1.08rem;

        line-height: 1.45;

        font-weight: 700;
    }


    /* =========================================================
       META
       ========================================================= */

    .tpl-classic .classic-meta {
        margin-bottom: 9px;

        color: var(--classic-accent);

        font-size: 0.84rem;

        font-weight: 600;

        letter-spacing: 0.015em;
    }


    /* =========================================================
       DESCRIPTION
       ========================================================= */

    .tpl-classic .classic-description {
        margin: 0;
        

        color: #55514a;

        font-size: 0.94rem;

        line-height: 1.75;

        white-space: pre-line;
    }


    /* =========================================================
       LISTS
       ========================================================= */

    .tpl-classic .classic-list {
        margin: 0;

        padding: 18px 24px 18px 42px;

        background: #fffdf9;

        border: 1px solid #e0dbd2;

        border-radius: 3px;

        box-shadow:
            0 5px 18px rgba(50, 43, 32, 0.025);
    }

    .tpl-classic .classic-list li {
        margin-bottom: 8px;

        color: #4e4b46;

        line-height: 1.65;
    }

    .tpl-classic .classic-list li:last-child {
        margin-bottom: 0;
    }

    .tpl-classic .classic-list li::marker {
        color: var(--classic-accent);
    }


    /* =========================================================
       LABEL
       ========================================================= */

    .tpl-classic .classic-label {
        color: #292929;

        font-weight: 700;
    }

    .tpl-classic .classic-entry p {
        margin-top: 0;
    }

    .tpl-classic .classic-entry a {
        color: var(--classic-accent);

        text-decoration: none;

        border-bottom: 1px solid transparent;

        transition: 0.2s ease;
    }

    .tpl-classic .classic-entry a:hover {
        border-bottom-color: var(--classic-accent);
    }


    /* =========================================================
       MOBILE
       ========================================================= */

    @media (max-width: 700px) {

        .tpl-classic .classic-container {
            padding: 25px 15px 45px;
        }

        .tpl-classic .classic-header {
            padding: 45px 27px 32px;

            margin-bottom: 38px;
        }

        .tpl-classic .classic-name {
            font-size: 2.35rem;
        }

        .tpl-classic .classic-title {
            font-size: 1rem;
        }

        .tpl-classic .classic-contact {
            flex-direction: column;

            align-items: center;

            gap: 7px;
        }

        .tpl-classic .classic-contact span {
            display: block;
        }

        .tpl-classic .classic-contact span:not(:last-child)::after {
            display: none;
        }

        .tpl-classic .classic-section {
            margin-bottom: 32px;
        }

        .tpl-classic .classic-section-title {
            font-size: 1rem;

            gap: 11px;
        }

        .tpl-classic .classic-entry {
            padding: 17px 18px;
        }

        .tpl-classic .classic-list {
            padding: 16px 18px 16px 36px;
        }
    }


    /* =========================================================
       SMALL MOBILE
       ========================================================= */

    @media (max-width: 430px) {

        .tpl-classic .classic-header {
            padding: 38px 21px 28px;
        }

        .tpl-classic .classic-header::before {
            top: 9px;
            left: 9px;
            right: 9px;
            bottom: 9px;
        }

        .tpl-classic .classic-name {
            font-size: 2rem;
        }

        .tpl-classic .classic-avatar {
            width: 88px;
            height: 88px;
        }

        .tpl-classic .classic-section-title {
            font-size: 0.92rem;
        }
    }


    /* =========================================================
       PRINT
       ========================================================= */

    @media print {

        .tpl-classic {
            background: #fff;
        }

        .tpl-classic .classic-container {
            max-width: none;
            padding: 25px;
        }

        .tpl-classic .classic-header,
        .tpl-classic .classic-entry,
        .tpl-classic .classic-list {
            box-shadow: none;
        }

        .tpl-classic .classic-header {
            border-color: #aaa;
        }
    }
</style>

<div class="pf-portfolio-body tpl-classic">

    <div class="classic-container">

        <!-- HEADER -->
        <header class="classic-header">

            <?php if (
                !empty($portfolio['show_profile_image']) &&
                !empty($user['profile_image'])
            ): ?>

                <img
                    src="/PortfolioForge-Clean/uploads/profiles/<?= sanitize($user['profile_image']) ?>"
                    alt="<?= sanitize($user['full_name']) ?>"
                    class="classic-avatar">

            <?php endif; ?>

            <h1 class="classic-name">
                <?= sanitize($user['full_name'] ?? '') ?>
            </h1>

            <?php if (!empty($portfolio['title'])): ?>

                <div class="classic-title">
                    <?= sanitize($portfolio['title']) ?>
                </div>

            <?php endif; ?>

            <div class="classic-contact">

                <?php if (
                    !empty($portfolio['show_email']) &&
                    !empty($user['email'])
                ): ?>

                    <span>
                        <?= sanitize($user['email']) ?>
                    </span>

                <?php endif; ?>

                <?php
                $headerPhone = '';
                $headerLocation = '';

                foreach ($sections as $contactSection) {

                    if (($contactSection['section_type'] ?? '') !== 'contact') {
                        continue;
                    }

                    $contact = classicContent($contactSection);

                    if (!empty($contact['phone'])) {
                        $headerPhone = $contact['phone'];
                    }

                    if (!empty($contact['location'])) {
                        $headerLocation = $contact['location'];
                    }

                    if (!empty($contact['text'])) {

                        $text = $contact['text'];

                        if (
                            preg_match(
                                '/(?:Phone|Mobile|Tel|Telephone)\s*:\s*(.+)/i',
                                $text,
                                $match
                            )
                        ) {
                            $headerPhone = trim($match[1]);
                        }

                        if (
                            preg_match(
                                '/(?:Location|Address|City)\s*:\s*(.+)/i',
                                $text,
                                $match
                            )
                        ) {
                            $headerLocation = trim($match[1]);
                        }
                    }
                }
                ?>

                <?php if (!empty($headerPhone)): ?>

                    <span>
                        <?= sanitize($headerPhone) ?>
                    </span>

                <?php endif; ?>

                <?php if (!empty($headerLocation)): ?>

                    <span>
                        <?= sanitize($headerLocation) ?>
                    </span>

                <?php endif; ?>

                <?php if (
                    !empty($resume) &&
                    !empty($resume['public_download_enabled']) &&
                    !empty($resume['file_path'])
                ): ?>

                    <span>
                        <a
                            href="/PortfolioForge-Clean/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>"
                            download
                            style="font-weight: 600; color: var(--classic-accent);"
                        >
                            📥 Download CV
                        </a>
                    </span>

                <?php endif; ?>

            </div>

        </header>


        <!-- SECTIONS -->
        <main>

            <?php foreach ($sections as $sec) { ?>

                <?php

                if (empty($sec['is_visible'])) {
                    continue;
                }

                $content = classicContent($sec);
                $type = $sec['section_type'] ?? '';
                $title = $sec['title'] ?? '';

                ?>

                <section class="classic-section">

                    <h2 class="classic-section-title">
                        <?= sanitize($title) ?>
                    </h2>

                    <?php if ($type === 'about'): ?>

                        <div class="classic-entry">

                            <p class="classic-text">
                                <?= sanitize(
                                    $content['text']
                                        ?? $content['description']
                                        ?? ''
                                ) ?>
                            </p>

                        </div>


                    <?php elseif ($type === 'skills'): ?>

                        <?php
                        $skills = [];

                        if (
                            isset($content['skills']) &&
                            is_array($content['skills'])
                        ) {
                            $skills = $content['skills'];
                        } elseif (!empty($content['text'])) {
                            $skills = preg_split(
                                '/\r\n|\r|\n|,/',
                                $content['text']
                            );
                        }
                        ?>

                        <ul class="classic-list">

                            <?php foreach ($skills as $skill): ?>

                                <?php if (trim($skill) !== ''): ?>

                                    <li>
                                        <?= sanitize(trim($skill)) ?>
                                    </li>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </ul>


                    <?php elseif ($type === 'education'): ?>

                        <?php
                        $eduItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($eduItems as $item): ?>

                            <div class="classic-entry">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['degree'])): ?>

                                        <h3 class="classic-entry-title">
                                            <?= sanitize($item['degree']) ?>
                                        </h3>

                                    <?php endif; ?>

                                    <div class="classic-meta">

                                        <?php if (!empty($item['institution'])): ?>
                                            <?= sanitize($item['institution']) ?>
                                        <?php endif; ?>

                                        <?php if (
                                            !empty($item['institution']) &&
                                            !empty($item['year'])
                                        ): ?>
                                            |
                                        <?php endif; ?>

                                        <?php if (!empty($item['year'])): ?>
                                            <?= sanitize($item['year']) ?>
                                        <?php endif; ?>

                                    </div>

                                    <?php if (!empty($item['description'])): ?>

                                        <p class="classic-description">
                                            <?= sanitize($item['description']) ?>
                                        </p>

                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>

                                        <p class="classic-description">
                                            <?= sanitize($item['text']) ?>
                                        </p>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <p class="classic-description">
                                        <?= sanitize($item) ?>
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>


                    <?php elseif ($type === 'experience'): ?>

                        <?php
                        $expItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($expItems as $item): ?>

                            <div class="classic-entry">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['job_title'])): ?>

                                        <h3 class="classic-entry-title">
                                            <?= sanitize($item['job_title']) ?>
                                        </h3>

                                    <?php endif; ?>

                                    <div class="classic-meta">

                                        <?php if (!empty($item['company'])): ?>
                                            <?= sanitize($item['company']) ?>
                                        <?php endif; ?>

                                        <?php if (
                                            !empty($item['company']) &&
                                            !empty($item['duration'])
                                        ): ?>
                                            |
                                        <?php endif; ?>

                                        <?php if (!empty($item['duration'])): ?>
                                            <?= sanitize($item['duration']) ?>
                                        <?php endif; ?>

                                    </div>

                                    <?php if (!empty($item['description'])): ?>

                                        <p class="classic-description">
                                            <?= sanitize($item['description']) ?>
                                        </p>

                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>

                                        <p class="classic-description">
                                            <?= sanitize($item['text']) ?>
                                        </p>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <p class="classic-description">
                                        <?= sanitize($item) ?>
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>


                    <?php elseif ($type === 'projects'): ?>

                        <?php
                        $projItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($projItems as $item): ?>

                            <div class="classic-entry">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['project_name'])): ?>

                                        <h3 class="classic-entry-title">
                                            <?= sanitize($item['project_name']) ?>
                                        </h3>

                                    <?php endif; ?>

                                    <?php if (!empty($item['technologies'])): ?>

                                        <div class="classic-meta">
                                            Technologies:
                                            <?= sanitize($item['technologies']) ?>
                                        </div>

                                    <?php endif; ?>

                                    <?php if (!empty($item['description'])): ?>

                                        <p class="classic-description">
                                            <?= sanitize($item['description']) ?>
                                        </p>

                                    <?php endif; ?>

                                    <?php if (!empty($item['link'])): ?>

                                        <p>
                                            <span class="classic-label">Project:</span>
                                            <a
                                                href="<?= sanitize($item['link']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer">
                                                <?= sanitize($item['link']) ?>
                                            </a>
                                        </p>

                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>

                                        <p class="classic-description">
                                            <?= sanitize($item['text']) ?>
                                        </p>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <p class="classic-description">
                                        <?= sanitize($item) ?>
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>


                    <?php elseif ($type === 'certifications'): ?>

                        <?php
                        $certItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($certItems as $item): ?>

                            <div class="classic-entry">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['name'])): ?>

                                        <h3 class="classic-entry-title">
                                            <?= sanitize($item['name']) ?>
                                        </h3>

                                    <?php endif; ?>

                                    <div class="classic-meta">

                                        <?php if (!empty($item['issuer'])): ?>
                                            <?= sanitize($item['issuer']) ?>
                                        <?php endif; ?>

                                        <?php if (
                                            !empty($item['issuer']) &&
                                            !empty($item['year'])
                                        ): ?>
                                            |
                                        <?php endif; ?>

                                        <?php if (!empty($item['year'])): ?>
                                            <?= sanitize($item['year']) ?>
                                        <?php endif; ?>

                                    </div>

                                    <?php if (!empty($item['description'])): ?>

                                        <p class="classic-description">
                                            <?= sanitize($item['description']) ?>
                                        </p>

                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>

                                        <p class="classic-description">
                                            <?= sanitize($item['text']) ?>
                                        </p>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <p class="classic-description">
                                        <?= sanitize($item) ?>
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>


                    <?php elseif ($type === 'languages'): ?>

                        <?php
                        $languages = [];

                        if (
                            isset($content['languages']) &&
                            is_array($content['languages'])
                        ) {
                            $languages = $content['languages'];
                        } elseif (!empty($content['text'])) {
                            $languages = preg_split(
                                '/\r\n|\r|\n|,/',
                                $content['text']
                            );
                        }
                        ?>

                        <ul class="classic-list">

                            <?php foreach ($languages as $language): ?>

                                <?php if (trim($language) !== ''): ?>

                                    <li>
                                        <?= sanitize(trim($language)) ?>
                                    </li>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </ul>


                    <?php elseif ($type === 'interests'): ?>

                        <?php
                        $interests = [];

                        if (
                            isset($content['interests']) &&
                            is_array($content['interests'])
                        ) {
                            $interests = $content['interests'];
                        } elseif (!empty($content['text'])) {
                            $interests = preg_split(
                                '/\r\n|\r|\n|,/',
                                $content['text']
                            );
                        }
                        ?>

                        <ul class="classic-list">

                            <?php foreach ($interests as $interest): ?>

                                <?php if (trim($interest) !== ''): ?>

                                    <li>
                                        <?= sanitize(trim($interest)) ?>
                                    </li>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </ul>


                    <?php elseif ($type === 'contact'): ?>

                        <div class="classic-entry">

                            <p class="classic-text">
                                <?= sanitize($content['text'] ?? '') ?>
                            </p>

                        </div>


                    <?php else: ?>

                        <?php if (!empty($content['text'])): ?>

                            <div class="classic-entry">

                                <p class="classic-text">
                                    <?= sanitize($content['text']) ?>
                                </p>

                            </div>

                        <?php else: ?>

                            <?php foreach ($content as $key => $value): ?>

                                <?php if (
                                    $key === 'text' ||
                                    empty($value)
                                ) {
                                    continue;
                                } ?>

                                <p>
                                    <strong>
                                        <?= sanitize(
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $key
                                                )
                                            )
                                        ) ?>:
                                    </strong>

                                    <?= sanitize(classicValue($value)) ?>
                                </p>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    <?php endif; ?>

                </section>
            <?php } ?>



        </main>

    </div>

</div>