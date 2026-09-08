<?php
// templates/professional/view.php

$accentColor = $portfolio['accent_color'] ?? '#1f2937';
$fontFamily  = $portfolio['font_family'] ?? 'Inter, sans-serif';
$sections = $sections ?? [];

/*
 * Use standard section names.
 */
$headingMap = [
    'about'          => 'Professional Summary',
    'experience'     => 'Experience',
    'education'      => 'Education',
    'skills'         => 'Skills',
    'projects'       => 'Projects',
    'certifications' => 'Certifications',
    'achievements'   => 'Achievements',
    'languages'      => 'Languages',
    'activities'     => 'Activities',
    'interests'      => 'Interests',
    'contact'        => 'Contact'
];
?>

<style>

    /* =========================================================
   PROFESSIONAL TEMPLATE
   Visual styling only
   ========================================================= */

    .tpl-professional {
        --professional-accent: var(--pf-accent);
        --professional-dark: #172033;
        --professional-text: #526071;
        --professional-muted: #7b8797;
        --professional-border: #e7ebf0;
        --professional-surface: #ffffff;
        --professional-soft: #f6f8fb;

        font-family: var(--pf-font);
        background: #f3f5f8;
        color: var(--professional-dark);
        min-height: 100vh;
    }

    .tpl-professional .professional-container {
        max-width: 1080px;
        margin: 0 auto;
        padding: 42px 28px 70px;
    }


    /* =========================================================
   HEADER / HERO
   ========================================================= */

    .tpl-professional .professional-header {
        position: relative;
        overflow: hidden;

        background: var(--professional-dark);
        color: #ffffff;

        padding: 58px 58px 48px;
        margin-bottom: 52px;

        border-radius: 18px;

        box-shadow:
            0 18px 45px rgba(23, 32, 51, 0.12);
    }

    .tpl-professional .professional-header::before {
        content: "";
        position: absolute;

        width: 260px;
        height: 260px;

        right: -90px;
        top: -110px;

        border-radius: 50%;

        border: 45px solid var(--professional-accent);

        opacity: 0.22;
    }

    .tpl-professional .professional-header::after {
        content: "";
        position: absolute;

        width: 120px;
        height: 120px;

        right: 70px;
        bottom: -70px;

        border-radius: 50%;

        background: var(--professional-accent);

        opacity: 0.12;
    }

    .tpl-professional .professional-header>* {
        position: relative;
        z-index: 2;
    }


    /* Name */

    .tpl-professional .professional-name {
        margin: 0;

        font-size: clamp(2.3rem, 5vw, 4rem);
        line-height: 1.05;

        font-weight: 750;
        letter-spacing: -0.045em;

        max-width: 760px;
    }


    /* Title */

    .tpl-professional .professional-title {
        display: inline-block;

        margin-top: 15px;

        color: #dbe2ec;

        font-size: 1.08rem;
        font-weight: 500;

        letter-spacing: 0.01em;
    }


    /* Accent line */

    .tpl-professional .professional-title::before {
        content: "";

        display: inline-block;

        width: 34px;
        height: 3px;

        margin-right: 12px;

        vertical-align: middle;

        background: var(--professional-accent);

        border-radius: 20px;
    }


    /* Contact */

    .tpl-professional .professional-contact {
        display: flex;
        flex-wrap: wrap;
        align-items: center;

        gap: 10px 24px;

        margin-top: 28px;
        padding-top: 22px;

        border-top: 1px solid rgba(255, 255, 255, 0.13);

        color: #b9c3d0;
        font-size: 0.9rem;
    }

    .tpl-professional .professional-contact span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .tpl-professional .professional-contact span::before {
        content: "";

        width: 5px;
        height: 5px;

        border-radius: 50%;

        background: var(--professional-accent);
    }

    .tpl-professional .professional-contact a {
        color: #ffffff;
        font-weight: 650;
        text-decoration: none;

        border-bottom: 1px solid rgba(255, 255, 255, 0.4);

        padding-bottom: 2px;

        transition: 0.2s ease;
    }

    .tpl-professional .professional-contact a:hover {
        color: var(--professional-accent);
        border-color: var(--professional-accent);
    }


    /* =========================================================
   SECTIONS
   ========================================================= */

    .tpl-professional .professional-section {
        position: relative;

        margin-bottom: 52px;
    }

    .tpl-professional .professional-section:last-child {
        margin-bottom: 0;
    }


    /* Section heading */

    .tpl-professional .professional-section-title {
        position: relative;

        margin: 0 0 24px;

        padding-left: 20px;

        font-size: 1.15rem;
        line-height: 1.3;

        font-weight: 750;

        letter-spacing: 0.045em;
        text-transform: uppercase;

        color: var(--professional-dark);
    }

    .tpl-professional .professional-section-title::before {
        content: "";

        position: absolute;

        left: 0;
        top: 2px;
        bottom: 2px;

        width: 4px;

        border-radius: 10px;

        background: var(--professional-accent);
    }

    .tpl-professional .professional-section-title::after {
        content: "";

        display: block;

        height: 1px;

        margin-top: 14px;

        background: var(--professional-border);
    }


    /* =========================================================
   SUMMARY TEXT
   ========================================================= */

    .tpl-professional .professional-text {
        margin: 0;

        color: var(--professional-text);

        font-size: 0.98rem;
        line-height: 1.85;

        white-space: pre-line;
    }


    /* =========================================================
   SUMMARY / SIMPLE TEXT
   ========================================================= */
    .tpl-professional .professional-section>.professional-text {
        margin: 0;
        padding: 4px 20px 14px;
        background: var(--professional-surface);

        border: 1px solid var(--professional-border);

        border-radius: 10px;

        box-shadow: 0 7px 22px rgba(23, 32, 51, 0.035);
    }


    /* =========================================================
   SKILLS
   ========================================================= */

    .tpl-professional .professional-skills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;

        margin: 0;

        color: var(--professional-dark);

        font-size: 0;
    }

    .tpl-professional .professional-skills {
        background: transparent;
    }

    .tpl-professional .professional-skills::first-line {
        font-size: inherit;
    }


    /*
 * The PHP outputs "skill | skill | skill".
 * Keep the existing data logic untouched.
 */
    .tpl-professional .professional-skills {
        padding: 22px 24px;

        background: var(--professional-surface);

        border: 1px solid var(--professional-border);
        border-radius: 12px;

        font-size: 0.95rem;
        line-height: 2;

        color: var(--professional-text);
    }


    /* =========================================================
   ENTRIES
   ========================================================= */
    .tpl-professional .professional-entry {
        position: relative;

        margin-bottom: 16px;
        padding: 6px 22px 18px 25px;

        background: var(--professional-surface);

        border: 1px solid var(--professional-border);
        border-radius: 13px;

        box-shadow: 0 7px 24px rgba(23, 32, 51, 0.035);

        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            border-color 0.2s ease;
    }

    .tpl-professional .professional-entry:last-child {
        margin-bottom: 0;
    }

    .tpl-professional .professional-entry:hover {
        transform: translateY(-2px);

        border-color: color-mix(in srgb,
                var(--professional-accent) 35%,
                var(--professional-border));

        box-shadow: 0 12px 30px rgba(23, 32, 51, 0.07);
    }


    /* Entry accent */

    .tpl-professional .professional-entry::before {
        content: "";

        position: absolute;

        left: 0;
        top: 24px;
        bottom: 24px;

        width: 3px;

        border-radius: 0 5px 5px 0;

        background: var(--professional-accent);
    }


    /* Entry title */

    .tpl-professional .professional-entry h3 {
        margin: 0 0 4px;

        color: var(--professional-dark);

        font-size: 1.08rem;
        line-height: 1.4;

        font-weight: 700;
    }


    /* Company / secondary information */

    .tpl-professional .professional-company {
        color: var(--professional-accent);

        font-size: 0.92rem;
        font-weight: 650;

        margin-bottom: 2px;
    }


    /* Duration */

    .tpl-professional .professional-duration {
        color: var(--professional-muted);
        font-size: 0.82rem;
        font-weight: 600;
        margin: 0;
    }


    /* Entry description */
    .tpl-professional .professional-entry .professional-text {
        margin: 0;
    }

    /* Project link */

    .tpl-professional .professional-entry a {
        display: inline-flex;

        margin-top: 15px;

        color: var(--professional-accent);

        font-size: 0.88rem;
        font-weight: 700;

        text-decoration: none;

        border-bottom: 1px solid transparent;

        transition: 0.2s ease;
    }

    .tpl-professional .professional-entry a:hover {
        border-bottom-color: var(--professional-accent);
    }


    /* =========================================================
   MOBILE
   ========================================================= */

    @media (max-width: 700px) {

        .tpl-professional .professional-container {
            padding: 18px 14px 45px;
        }

        .tpl-professional .professional-header {
            padding: 40px 26px 30px;
            margin-bottom: 38px;

            border-radius: 14px;
        }

        .tpl-professional .professional-name {
            font-size: 2.35rem;
        }

        .tpl-professional .professional-contact {
            align-items: flex-start;
            flex-direction: column;
            gap: 10px;
        }

        .tpl-professional .professional-section {
            margin-bottom: 38px;
        }

        .tpl-professional .professional-entry {
            padding: 21px 20px 21px 24px;
        }
    }

    @media (max-width: 430px) {

        .tpl-professional .professional-header {
            padding: 34px 21px 27px;
        }

        .tpl-professional .professional-name {
            font-size: 2rem;
        }

        .tpl-professional .professional-section-title {
            font-size: 1rem;
        }
    }
</style>


<div
    class="pf-portfolio-body tpl-professional"
    style="
        --pf-accent: <?= sanitize($accentColor) ?>;
        --pf-font: <?= sanitize($portfolio['font_family'] ?? 'Inter, sans-serif') ?>;
    ">

    <div class="pf-container professional-container">

        <!-- HEADER -->
        <header class="professional-header">

            <h1 class="professional-name">
                <?= sanitize($user['full_name'] ?? '') ?>
            </h1>

            <?php if (!empty($portfolio['title'])): ?>

                <div class="professional-title">
                    <?= sanitize($portfolio['title']) ?>
                </div>

            <?php endif; ?>


            <div class="professional-contact">

                <?php if (
                    !empty($portfolio['show_email']) &&
                    !empty($user['email'])
                ): ?>

                    <span>
                        <?= sanitize($user['email']) ?>
                    </span>

                <?php endif; ?>


                <?php if (
                    !empty($resume) &&
                    !empty($resume['public_download_enabled'])
                ): ?>

                    <span>

                        <a
                            href="/PortfolioForge-Clean/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>"
                            download>
                            Download Resume
                        </a>

                    </span>

                <?php endif; ?>

            </div>

        </header>


        <!-- CONTENT -->

        <main>

            <?php foreach ($sections as $sec): ?>

                <?php

                if (!$sec['is_visible']) {
                    continue;
                }

                $sectionType = $sec['section_type'] ?? '';

                $content =
                    json_decode(
                        $sec['content'] ?? '',
                        true
                    ) ?? [];

                $sectionHeading =
                    $headingMap[$sectionType]
                    ?? ($sec['title'] ?? 'Section');

                ?>

                <section class="professional-section">

                    <h2 class="professional-section-title">
                        <?= sanitize($sectionHeading) ?>
                    </h2>


                    <!-- ABOUT -->

                    <?php if ($sectionType === 'about'): ?>

                        <?php

                        $aboutText =
                            is_array($content)
                            ? (
                                $content['text']
                                ?? $content['description']
                                ?? ''
                            )
                            : $content;

                        ?>

                        <p class="professional-text">
                            <?= sanitize($aboutText) ?>
                        </p>


                        <!-- SKILLS -->

                    <?php elseif ($sectionType === 'skills'): ?>

                        <?php

                        $skillsList =
                            is_array($content)
                            ? $content
                            : explode(',', $content);

                        $skillsList =
                            array_filter(
                                array_map(
                                    'trim',
                                    $skillsList
                                )
                            );

                        ?>

                        <p class="professional-skills">
                            <?= sanitize(
                                implode(
                                    ' | ',
                                    $skillsList
                                )
                            ) ?>
                        </p>


                        <!-- LANGUAGES -->

                    <?php elseif ($sectionType === 'languages'): ?>

                        <?php

                        if (isset($content['languages'])) {

                            $languages =
                                $content['languages'];
                        } elseif (isset($content['text'])) {

                            $languages =
                                explode(
                                    ',',
                                    $content['text']
                                );
                        } else {

                            $languages =
                                $content;
                        }

                        if (!is_array($languages)) {
                            $languages = [$languages];
                        }

                        $languages =
                            array_filter(
                                array_map(
                                    'trim',
                                    $languages
                                )
                            );

                        ?>

                        <p class="professional-text">
                            <?= sanitize(
                                implode(
                                    ' | ',
                                    $languages
                                )
                            ) ?>
                        </p>


                        <!-- INTERESTS -->

                    <?php elseif ($sectionType === 'interests'): ?>

                        <?php

                        if (isset($content['interests'])) {

                            $interests =
                                $content['interests'];
                        } elseif (isset($content['text'])) {

                            $interests =
                                explode(
                                    ',',
                                    $content['text']
                                );
                        } else {

                            $interests =
                                $content;
                        }

                        if (!is_array($interests)) {
                            $interests = [$interests];
                        }

                        $interests =
                            array_filter(
                                array_map(
                                    'trim',
                                    $interests
                                )
                            );

                        ?>

                        <p class="professional-text">
                            <?= sanitize(
                                implode(
                                    ' | ',
                                    $interests
                                )
                            ) ?>
                        </p>


                        <!-- EXPERIENCE -->

                    <?php elseif ($sectionType === 'experience'): ?>

                        <?php if (
                            is_array($content) &&
                            array_keys($content) !==
                            range(0, count($content) - 1)
                        ): ?>

                            <article class="professional-entry">

                                <?php if (!empty($content['job_title'])): ?>

                                    <h3>
                                        <?= sanitize(
                                            $content['job_title']
                                        ) ?>
                                    </h3>

                                <?php endif; ?>


                                <?php if (!empty($content['company'])): ?>

                                    <div class="professional-company">
                                        <?= sanitize(
                                            $content['company']
                                        ) ?>
                                    </div>

                                <?php endif; ?>


                                <?php if (!empty($content['duration'])): ?>

                                    <div class="professional-duration">
                                        <?= sanitize(
                                            $content['duration']
                                        ) ?>
                                    </div>

                                <?php endif; ?>


                                <?php

                                $description =
                                    $content['description']
                                    ?? $content['text']
                                    ?? '';

                                ?>

                                <?php if (!empty($description)): ?>

                                    <p class="professional-text">
                                        <?= sanitize(
                                            $description
                                        ) ?>
                                    </p>

                                <?php endif; ?>

                            </article>


                        <?php else: ?>


                            <?php foreach (
                                (array)$content
                                as $item
                            ): ?>

                                <article class="professional-entry">

                                    <?php if (is_array($item)): ?>

                                        <?php if (
                                            !empty($item['job_title'])
                                        ): ?>

                                            <h3>
                                                <?= sanitize(
                                                    $item['job_title']
                                                ) ?>
                                            </h3>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty($item['company'])
                                        ): ?>

                                            <div class="professional-company">
                                                <?= sanitize(
                                                    $item['company']
                                                ) ?>
                                            </div>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty($item['duration'])
                                        ): ?>

                                            <div class="professional-duration">
                                                <?= sanitize(
                                                    $item['duration']
                                                ) ?>
                                            </div>

                                        <?php endif; ?>


                                        <?php

                                        $description =
                                            $item['description']
                                            ?? $item['text']
                                            ?? '';

                                        ?>

                                        <?php if (
                                            !empty($description)
                                        ): ?>

                                            <p class="professional-text">
                                                <?= sanitize(
                                                    $description
                                                ) ?>
                                            </p>

                                        <?php endif; ?>


                                    <?php else: ?>

                                        <p class="professional-text">
                                            <?= sanitize($item) ?>
                                        </p>

                                    <?php endif; ?>

                                </article>

                            <?php endforeach; ?>

                        <?php endif; ?>


                        <!-- EDUCATION -->

                    <?php elseif ($sectionType === 'education'): ?>

                        <?php if (
                            is_array($content) &&
                            array_keys($content) !==
                            range(0, count($content) - 1)
                        ): ?>

                            <article class="professional-entry">

                                <?php if (
                                    !empty($content['degree'])
                                ): ?>

                                    <h3>
                                        <?= sanitize(
                                            $content['degree']
                                        ) ?>
                                    </h3>

                                <?php endif; ?>


                                <?php if (
                                    !empty($content['institution'])
                                ): ?>

                                    <div class="professional-company">
                                        <?= sanitize(
                                            $content['institution']
                                        ) ?>
                                    </div>

                                <?php endif; ?>


                                <?php if (
                                    !empty($content['year'])
                                ): ?>

                                    <div class="professional-duration">
                                        <?= sanitize(
                                            $content['year']
                                        ) ?>
                                    </div>

                                <?php endif; ?>


                                <?php

                                $description =
                                    $content['description']
                                    ?? $content['text']
                                    ?? '';

                                ?>

                                <?php if (
                                    !empty($description)
                                ): ?>

                                    <p class="professional-text">
                                        <?= sanitize(
                                            $description
                                        ) ?>
                                    </p>

                                <?php endif; ?>

                            </article>


                        <?php else: ?>


                            <?php foreach (
                                (array)$content
                                as $item
                            ): ?>

                                <article class="professional-entry">

                                    <?php if (is_array($item)): ?>

                                        <?php if (
                                            !empty($item['degree'])
                                        ): ?>

                                            <h3>
                                                <?= sanitize(
                                                    $item['degree']
                                                ) ?>
                                            </h3>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty($item['institution'])
                                        ): ?>

                                            <div class="professional-company">
                                                <?= sanitize(
                                                    $item['institution']
                                                ) ?>
                                            </div>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty($item['year'])
                                        ): ?>

                                            <div class="professional-duration">
                                                <?= sanitize(
                                                    $item['year']
                                                ) ?>
                                            </div>

                                        <?php endif; ?>

                                    <?php else: ?>

                                        <p class="professional-text">
                                            <?= sanitize($item) ?>
                                        </p>

                                    <?php endif; ?>

                                </article>

                            <?php endforeach; ?>

                        <?php endif; ?>


                        <!-- PROJECTS -->

                    <?php elseif ($sectionType === 'projects'): ?>

                        <?php foreach (
                            (array)$content
                            as $item
                        ): ?>

                            <article class="professional-entry">

                                <?php if (is_array($item)): ?>

                                    <?php if (
                                        !empty($item['project_name'])
                                    ): ?>

                                        <h3>
                                            <?= sanitize(
                                                $item['project_name']
                                            ) ?>
                                        </h3>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty($item['technologies'])
                                    ): ?>

                                        <div class="professional-company">
                                            <?= sanitize(
                                                $item['technologies']
                                            ) ?>
                                        </div>

                                    <?php endif; ?>


                                    <?php

                                    $description =
                                        $item['description']
                                        ?? $item['text']
                                        ?? '';

                                    ?>

                                    <?php if (
                                        !empty($description)
                                    ): ?>

                                        <p class="professional-text">
                                            <?= sanitize(
                                                $description
                                            ) ?>
                                        </p>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty($item['link'])
                                    ): ?>

                                        <a
                                            href="<?= sanitize($item['link']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer">
                                            View Project
                                        </a>

                                    <?php endif; ?>


                                <?php else: ?>

                                    <p class="professional-text">
                                        <?= sanitize($item) ?>
                                    </p>

                                <?php endif; ?>

                            </article>

                        <?php endforeach; ?>


                        <!-- CERTIFICATIONS -->

                    <?php elseif ($sectionType === 'certifications'): ?>

                        <?php foreach (
                            (array)$content
                            as $item
                        ): ?>

                            <article class="professional-entry">

                                <?php if (is_array($item)): ?>

                                    <?php if (
                                        !empty($item['name'])
                                    ): ?>

                                        <h3>
                                            <?= sanitize(
                                                $item['name']
                                            ) ?>
                                        </h3>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty($item['issuer'])
                                    ): ?>

                                        <div class="professional-company">
                                            <?= sanitize(
                                                $item['issuer']
                                            ) ?>
                                        </div>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty($item['year'])
                                    ): ?>

                                        <div class="professional-duration">
                                            <?= sanitize(
                                                $item['year']
                                            ) ?>
                                        </div>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <p class="professional-text">
                                        <?= sanitize($item) ?>
                                    </p>

                                <?php endif; ?>

                            </article>

                        <?php endforeach; ?>


                        <!-- ACHIEVEMENTS / ACTIVITIES / OTHER -->

                    <?php else: ?>

                        <?php if (is_array($content)): ?>

                            <?php foreach (
                                $content
                                as $key => $item
                            ): ?>

                                <article class="professional-entry">

                                    <?php if (is_array($item)): ?>

                                        <?php foreach (
                                            $item
                                            as $subKey => $subValue
                                        ): ?>

                                            <?php if (
                                                !empty($subValue)
                                            ): ?>

                                                <p class="professional-text">
                                                    <strong><?= sanitize(ucwords(str_replace('_', ' ', $subKey))) ?>:</strong>
                                                    <?= sanitize(is_array($subValue) ? implode(', ', $subValue) : $subValue) ?>
                                                </p>

                                            <?php endif; ?>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <p class="professional-text">
                                            <?= sanitize($item) ?>
                                        </p>

                                    <?php endif; ?>

                                </article>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <p class="professional-text">
                                <?= sanitize($content) ?>
                            </p>

                        <?php endif; ?>

                    <?php endif; ?>

                </section>

            <?php endforeach; ?>

        </main>

    </div>

</div>