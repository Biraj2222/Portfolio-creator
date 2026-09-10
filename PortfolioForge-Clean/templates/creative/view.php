    <?php
    // templates/creative/view.php

    $accentColor = $portfolio['accent_color'] ?? '#7c3aed';
    $sections = $sections ?? [];

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
       CREATIVE PORTFOLIO
       ========================================================= */

        .tpl-creative {
            --creative-accent: var(--pf-accent, #7c3aed);
            --creative-bg: #f4f2ed;
            --creative-white: #ffffff;
            --creative-black: #171717;
            --creative-gray: #6d6a65;
            --creative-line: #d9d5cd;

            min-height: 100vh;
            background: var(--creative-bg);
            color: var(--creative-black);
            font-family: Arial, Helvetica, sans-serif;
        }


        /* =========================================================
       MAIN CONTAINER
       ========================================================= */

        .tpl-creative .creative-container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 50px 45px 90px;
        }


        /* =========================================================
       HEADER
       ========================================================= */

        .tpl-creative .creative-header {
            position: relative;
            width: 100vw;
            min-height: 430px;

            margin-left: calc(50% - 50vw);
            margin-bottom: 90px;

            padding: 70px max(45px, calc((100vw - 1180px) / 2 + 45px)) 65px;

            display: flex;
            align-items: flex-end;

            background: var(--creative-black);
            color: #ffffff;

            overflow: hidden;
            box-sizing: border-box;
        }


        /* Giant background letter */

        .tpl-creative .creative-header::before {
            content: "C";

            position: absolute;
            right: -30px;
            bottom: -100px;

            font-family: Georgia, "Times New Roman", serif;
            font-size: 430px;
            font-weight: 700;
            line-height: 1;

            color: var(--creative-accent);
            opacity: 0.16;
        }


        /* Accent block */

        .tpl-creative .creative-header::after {
            content: "";

            position: absolute;
            top: 0;
            left: 70px;

            width: 95px;
            height: 7px;

            background: var(--creative-accent);
        }


        /* Header content */

        .tpl-creative .creative-intro {
            position: relative;
            z-index: 2;

            max-width: 850px;
        }


        /* =========================================================
       PORTFOLIO LABEL
       ========================================================= */

        .tpl-creative .creative-label {
            display: inline-block;

            margin-bottom: 25px;
            padding: 7px 12px;

            color: #ffffff;
            background: var(--creative-accent);

            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.18em;
        }


        /* =========================================================
       NAME
       ========================================================= */

        .tpl-creative .creative-name {
            margin: 0;

            max-width: 850px;

            font-family: Georgia, "Times New Roman", serif;

            font-size: clamp(3.5rem, 8vw, 7.2rem);
            font-weight: 700;

            line-height: 0.9;
            letter-spacing: -0.06em;

            color: #ffffff;
        }


        /* =========================================================
       TITLE
       ========================================================= */

        .tpl-creative .creative-title {
            margin-top: 28px;

            color: #c9c6c0;

            font-size: 1.15rem;
            font-weight: 400;

            letter-spacing: 0.02em;
        }


        /* =========================================================
       CONTACT
       ========================================================= */

        .tpl-creative .creative-contact {
            display: flex;
            align-items: center;
            flex-wrap: wrap;

            gap: 20px;

            margin-top: 35px;

            font-size: 0.85rem;
            color: #bcb9b3;
        }


        .tpl-creative .creative-contact span {
            display: inline-flex;
            align-items: center;
        }


        /* Separators */

        .tpl-creative .creative-contact span:not(:last-child)::after {
            content: "";

            width: 5px;
            height: 5px;

            margin-left: 20px;

            background: var(--creative-accent);
            border-radius: 50%;
        }


        .tpl-creative .creative-contact a {
            color: #ffffff;

            font-weight: 800;
            text-decoration: none;

            padding-bottom: 4px;

            border-bottom: 2px solid var(--creative-accent);

            transition: 0.2s ease;
        }


        .tpl-creative .creative-contact a:hover {
            color: var(--creative-accent);
        }


        /* =========================================================
       SECTIONS
       ========================================================= */

        .tpl-creative main {
            counter-reset: creative-section;
        }


        .tpl-creative .creative-section {
            display: grid;

            grid-template-columns: 100px minmax(0, 1fr);

            gap: 35px;

            margin-bottom: 95px;

            counter-increment: creative-section;
        }


        .tpl-creative .creative-section:last-child {
            margin-bottom: 0;
        }


        /* =========================================================
       SECTION NUMBER
       ========================================================= */

        .tpl-creative .creative-section-marker {
            position: relative;

            padding-top: 4px;
        }


        .tpl-creative .creative-section-marker::before {
            content: counter(creative-section, decimal-leading-zero);

            display: block;

            color: var(--creative-accent);

            font-size: 0.8rem;
            font-weight: 800;

            letter-spacing: 0.08em;
        }


        .tpl-creative .creative-section-marker::after {
            content: "";

            display: block;

            width: 45px;
            height: 2px;

            margin-top: 14px;

            background: var(--creative-black);
        }


        /* =========================================================
       SECTION TITLE
       ========================================================= */

        .tpl-creative .creative-section-title {
            position: relative;

            display: flex;
            align-items: center;

            gap: 20px;

            margin: 0 0 30px;

            color: var(--creative-black);

            font-size: 1rem;
            font-weight: 800;

            letter-spacing: 0.16em;
            text-transform: uppercase;
        }


        .tpl-creative .creative-section-title::after {
            content: "";

            flex: 1;

            height: 1px;

            background: var(--creative-line);
        }



        /* =========================================================
       ABOUT / NORMAL TEXT
       ========================================================= */

        .tpl-creative .creative-text {
            margin: 0;

            color: #4f4c47;

            font-size: 1rem;
            line-height: 1.9;

            white-space: pre-line;
        }

        .tpl-creative .creative-about {
            position: relative;

            width: 100%;

            margin: 0;

            padding: 10px 18px;

            border-left: 4px solid var(--creative-accent);

            box-sizing: border-box;
        }


        /* Hover box */

        .tpl-creative .creative-about::before {
            content: "";

            position: absolute;

            top: 0;
            left: 0;
            right: 0;
            bottom: 0;

            background: var(--creative-white);

            border: 1px solid var(--creative-line);

            opacity: 0;

            transition: opacity 0.2s ease;

            z-index: 0;
        }


        /* Keep text above hover box */

        .tpl-creative .creative-about>.creative-text {
            position: relative;

            z-index: 1;

            transition: transform 0.2s ease;
        }


        /* Hover */

        .tpl-creative .creative-about:hover::before {
            opacity: 1;
        }

        .tpl-creative .creative-about:hover>.creative-text {
            transform: translateX(8px);
        }



        /* =========================================================
       SKILLS
       ========================================================= */
        .tpl-creative .creative-list {
            position: relative;

            width: 100%;

            margin: 0;

            padding: 10px 18px;

            border-left: 4px solid var(--creative-accent);

            box-sizing: border-box;
        }

        .tpl-creative .creative-list::before {
            content: "";

            position: absolute;

            top: 0;
            left: 0;
            right: 0;
            bottom: 0;

            background: var(--creative-white);

            border: 1px solid var(--creative-line);

            opacity: 0;

            transition: opacity 0.2s ease;

            z-index: 0;
        }

        .tpl-creative .creative-list>.creative-text {
            position: relative;

            z-index: 1;

            transition: transform 0.2s ease;
        }

        .tpl-creative .creative-list:hover::before {
            opacity: 1;
        }

        .tpl-creative .creative-list:hover>.creative-text {
            transform: translateX(8px);
        }


        /* =========================================================
       EXPERIENCE / EDUCATION / PROJECTS
       ========================================================= */

        .tpl-creative .creative-entry {
            position: relative;

            width: 100%;

            margin: 0 0 18px;

            padding: 0 0 0 18px;

            color: #4f4c47;

            font-size: 1rem;
            line-height: 1.7;

            white-space: pre-line;

            border-left: 4px solid var(--creative-accent);

            box-sizing: border-box;
        }


        /* Hover box */

        .tpl-creative .creative-entry::before {
            content: "";

            position: absolute;

            top: 0;
            left: 0;
            right: 0;
            bottom: 0;

            background: var(--creative-white);

            border: 1px solid var(--creative-line);

            opacity: 0;

            transition: opacity 0.2s ease;

            z-index: 0;
        }


        /* Keep content above the hover box */

        .tpl-creative .creative-entry>* {
            position: relative;

            z-index: 1;

            transition: transform 0.2s ease;
        }


        /* Hover */

        .tpl-creative .creative-entry:hover::before {
            opacity: 1;
        }


        .tpl-creative .creative-entry:hover>* {
            transform: translateX(8px);
        }


        .tpl-creative .creative-entry:last-child {
            margin-bottom: 0;
        }


        /* =========================================================
       ENTRY HEADER
       ========================================================= */

        .tpl-creative .creative-entry-header {
            display: flex;

            align-items: flex-start;
            justify-content: space-between;

            gap: 25px;

            margin-bottom: 8px;
        }


        .tpl-creative .creative-entry h3,
        .tpl-creative .creative-entry-header h3 {
            margin: 0;

            color: var(--creative-black);

            font-family: Georgia, "Times New Roman", serif;

            font-size: 1.35rem;
            font-weight: 700;

            line-height: 1.3;
        }


        .tpl-creative .creative-entry-header span {
            flex-shrink: 0;

            padding: 5px 10px;

            color: var(--creative-accent);

            background: #f1eef8;

            font-size: 0.75rem;
            font-weight: 800;

            letter-spacing: 0.03em;
        }


        /* =========================================================
       COMPANY / INSTITUTION / TECHNOLOGIES
       ========================================================= */

        .tpl-creative .creative-company {
            margin-bottom: 15px;

            color: var(--creative-accent);

            font-size: 0.82rem;
            font-weight: 800;

            letter-spacing: 0.05em;
            text-transform: uppercase;
        }


        /* =========================================================
       ENTRY DESCRIPTION
       ========================================================= */

        .tpl-creative .creative-entry .creative-text {
            margin-top: 14px;
        }


        /* =========================================================
       PROJECT LINKS
       ========================================================= */

        .tpl-creative .creative-entry a {
            display: inline-block;

            margin-top: 18px;

            padding-bottom: 4px;

            color: var(--creative-black);

            font-size: 0.82rem;
            font-weight: 800;

            text-decoration: none;

            border-bottom: 2px solid var(--creative-accent);

            transition: 0.2s ease;
        }


        .tpl-creative .creative-entry a:hover {
            color: var(--creative-accent);
        }


        /* =========================================================
       GENERIC CONTENT
       ========================================================= */

        .tpl-creative .creative-entry strong {
            color: var(--creative-black);

            font-weight: 800;
        }


        /* =========================================================
       MOBILE
       ========================================================= */

        @media (max-width: 750px) {

            .tpl-creative .creative-container {
                padding: 25px 18px 60px;
            }


            .tpl-creative .creative-header {
                min-height: 400px;

                margin-bottom: 60px;

                padding: 50px 32px 45px;
            }


            .tpl-creative .creative-header::after {
                left: 32px;
            }


            .tpl-creative .creative-header::before {
                right: -80px;
                bottom: -70px;

                font-size: 300px;
            }


            .tpl-creative .creative-name {
                font-size: 3.5rem;
            }


            .tpl-creative .creative-section {
                grid-template-columns: 45px minmax(0, 1fr);

                gap: 18px;

                margin-bottom: 65px;
            }


            .tpl-creative .creative-section-title {
                gap: 12px;

                font-size: 0.82rem;
            }



            .tpl-creative .creative-entry-header {
                flex-direction: column;

                gap: 8px;
            }


            .tpl-creative .creative-entry-header span {
                align-self: flex-start;
            }

        }


        /* =========================================================
       SMALL PHONE
       ========================================================= */

        @media (max-width: 450px) {

            .tpl-creative .creative-container {
                padding-left: 12px;
                padding-right: 12px;
            }


            .tpl-creative .creative-header {
                min-height: 370px;

                padding: 40px 23px 38px;
            }


            .tpl-creative .creative-header::after {
                left: 23px;
            }


            .tpl-creative .creative-name {
                font-size: 2.7rem;
            }


            .tpl-creative .creative-title {
                font-size: 0.98rem;
            }


            .tpl-creative .creative-contact {
                flex-direction: column;

                align-items: flex-start;

                gap: 10px;
            }


            .tpl-creative .creative-contact span:not(:last-child)::after {
                display: none;
            }


            .tpl-creative .creative-section {
                grid-template-columns: 32px minmax(0, 1fr);

                gap: 12px;
            }


            .tpl-creative .creative-section-title {
                font-size: 0.76rem;
            }



            .tpl-creative .creative-entry h3 {
                font-size: 1.15rem;
            }

        }
    </style>

    <div class="pf-portfolio-body tpl-creative"
        style="--pf-accent: <?= sanitize($accentColor) ?>;">

        <div class="pf-container creative-container">

            <!-- HEADER -->
            <header class="creative-header">

                <div class="creative-intro">

                    <div class="creative-label">PORTFOLIO</div>

                    <h1 class="creative-name"><?= sanitize($user['full_name'] ?? '') ?></h1>

                    <?php if (!empty($portfolio['title'])): ?>
                        <div class="creative-title"><?= sanitize($portfolio['title']) ?></div>
                    <?php endif; ?>

                    <div class="creative-contact">

                        <?php if (!empty($portfolio['show_email']) && !empty($user['email'])): ?>
                            <span><?= sanitize($user['email']) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($resume) && !empty($resume['public_download_enabled']) && !empty($resume['file_path'])): ?>
                            <span style="margin-top: 0.5rem; display: inline-block;">
                                <a href="/PortfolioForge-Clean/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>"
                                    download
                                    style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 1rem; background: var(--creative-accent); color: #ffffff; border-radius: 20px; text-decoration: none; font-size: 0.85rem; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: opacity 0.2s;">
                                    Download Resume / CV
                                </a>
                            </span>
                        <?php endif; ?>

                    </div>

                </div>

            </header>


            <main>

                <?php foreach ($sections as $sec): ?>

                    <?php
                    if (!$sec['is_visible']) {
                        continue;
                    }

                    $sectionType = $sec['section_type'] ?? '';
                    $content = json_decode($sec['content'] ?? '', true) ?? [];

                    $sectionHeading =
                        $headingMap[$sectionType]
                        ?? ($sec['title'] ?? 'Section');
                    ?>

                    <section class="creative-section">

                        <div class="creative-section-marker"></div>

                        <div class="creative-section-content">

                            <h2 class="creative-section-title"><?= sanitize($sectionHeading) ?></h2>


                            <!-- ABOUT -->
                            <?php if ($sectionType === 'about'): ?>

                                <?php
                                $aboutText = is_array($content)
                                    ? ($content['text'] ?? $content['description'] ?? '')
                                    : $content;
                                ?>

                                <div class="creative-about">
                                    <p class="creative-text"><?= sanitize($aboutText) ?></p>
                                </div>


                                <!-- SKILLS -->
                            <?php elseif ($sectionType === 'skills'): ?>

                                <?php
                                $skillsList = is_array($content)
                                    ? $content
                                    : explode(',', $content);

                                $skillsList = array_filter(
                                    array_map('trim', $skillsList)
                                );
                                ?>

                                <div class="creative-list">
                                    <p class="creative-text"><?= sanitize(implode(', ', $skillsList)) ?></p>
                                </div>


                                <!-- LANGUAGES -->
                            <?php elseif ($sectionType === 'languages'): ?>

                                <?php
                                if (isset($content['languages'])) {
                                    $languages = $content['languages'];
                                } elseif (isset($content['text'])) {
                                    $languages = explode(',', $content['text']);
                                } else {
                                    $languages = $content;
                                }

                                if (!is_array($languages)) {
                                    $languages = [$languages];
                                }

                                $languages = array_filter(
                                    array_map('trim', $languages)
                                );
                                ?>

                                <div class=" creative-list">
                                    <p class="creative-text"><?= sanitize(implode(', ', $languages)) ?></p>
                                </div>


                                <!-- INTERESTS -->
                            <?php elseif ($sectionType === 'interests'): ?>

                                <?php
                                if (isset($content['interests'])) {
                                    $interests = $content['interests'];
                                } elseif (isset($content['text'])) {
                                    $interests = explode(',', $content['text']);
                                } else {
                                    $interests = $content;
                                }

                                if (!is_array($interests)) {
                                    $interests = [$interests];
                                }

                                $interests = array_filter(
                                    array_map('trim', $interests)
                                );
                                ?>

                                <div class="creative-list">
                                    <p class="creative-text"><?= sanitize(implode(', ', $interests)) ?></p>
                                </div>


                                <!-- EXPERIENCE -->
                            <?php elseif ($sectionType === 'experience'): ?>

                                <?php foreach ((array)$content as $item): ?>

                                    <article class="creative-entry">

                                        <?php if (is_array($item)): ?>

                                            <div class="creative-entry-header">

                                                <?php if (!empty($item['job_title'])): ?>
                                                    <h3><?= sanitize($item['job_title']) ?></h3>
                                                <?php endif; ?>

                                                <?php if (!empty($item['duration'])): ?>
                                                    <span><?= sanitize($item['duration']) ?></span>
                                                <?php endif; ?>

                                            </div>

                                            <?php if (!empty($item['company'])): ?>
                                                <div class="creative-company"><?= sanitize($item['company']) ?></div>
                                            <?php endif; ?>

                                            <?php
                                            $description =
                                                $item['description']
                                                ?? $item['text']
                                                ?? '';
                                            ?>

                                            <?php if (!empty($description)): ?>
                                                <p class="creative-text"><?= sanitize($description) ?></p>
                                            <?php endif; ?>

                                        <?php else: ?>

                                            <p class="creative-text"><?= sanitize($item) ?></p>

                                        <?php endif; ?>

                                    </article>

                                <?php endforeach; ?>


                                <!-- EDUCATION -->
                            <?php elseif ($sectionType === 'education'): ?>

                                <?php foreach ((array)$content as $item): ?>

                                    <article class="creative-entry">

                                        <?php if (is_array($item)): ?>

                                            <div class="creative-entry-header">

                                                <?php if (!empty($item['degree'])): ?>
                                                    <h3><?= sanitize($item['degree']) ?></h3>
                                                <?php endif; ?>

                                                <?php if (!empty($item['year'])): ?>
                                                    <span><?= sanitize($item['year']) ?></span>
                                                <?php endif; ?>

                                            </div>

                                            <?php if (!empty($item['institution'])): ?>
                                                <div class="creative-company"><?= sanitize($item['institution']) ?></div>
                                            <?php endif; ?>

                                            <?php
                                            $description =
                                                $item['description']
                                                ?? $item['text']
                                                ?? '';
                                            ?>

                                            <?php if (!empty($description)): ?>
                                                <p class="creative-text"><?= sanitize($description) ?></p>
                                            <?php endif; ?>

                                        <?php else: ?>

                                            <p class="creative-text"><?= sanitize($item) ?></p>

                                        <?php endif; ?>

                                    </article>

                                <?php endforeach; ?>


                                <!-- PROJECTS -->
                            <?php elseif ($sectionType === 'projects'): ?>

                                <?php foreach ((array)$content as $item): ?>

                                    <article class="creative-entry">

                                        <?php if (is_array($item)): ?>

                                            <?php if (!empty($item['project_name'])): ?>
                                                <h3><?= sanitize($item['project_name']) ?></h3>
                                            <?php endif; ?>

                                            <?php if (!empty($item['technologies'])): ?>
                                                <div class="creative-company"><?= sanitize($item['technologies']) ?></div>
                                            <?php endif; ?>

                                            <?php
                                            $description =
                                                $item['description']
                                                ?? $item['text']
                                                ?? '';
                                            ?>

                                            <?php if (!empty($description)): ?>
                                                <p class="creative-text"><?= sanitize($description) ?></p>
                                            <?php endif; ?>

                                            <?php if (!empty($item['link'])): ?>
                                                <a href="<?= sanitize($item['link']) ?>"
                                                    target="_blank"
                                                    rel="noopener noreferrer">
                                                    View Project
                                                </a>
                                            <?php endif; ?>

                                        <?php else: ?>

                                            <p class="creative-text"><?= sanitize($item) ?></p>

                                        <?php endif; ?>

                                    </article>

                                <?php endforeach; ?>


                                <!-- CERTIFICATIONS -->
                            <?php elseif ($sectionType === 'certifications'): ?>

                                <?php foreach ((array)$content as $item): ?>

                                    <article class="creative-entry">

                                        <?php if (is_array($item)): ?>

                                            <?php if (!empty($item['name'])): ?>
                                                <h3><?= sanitize($item['name']) ?></h3>
                                            <?php endif; ?>

                                            <?php if (!empty($item['issuer'])): ?>
                                                <div class="creative-company"><?= sanitize($item['issuer']) ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($item['year'])): ?>
                                                <span><?= sanitize($item['year']) ?></span>
                                            <?php endif; ?>

                                        <?php else: ?>

                                            <p class="creative-text"><?= sanitize($item) ?></p>

                                        <?php endif; ?>

                                    </article>

                                <?php endforeach; ?>


                                <!-- OTHER SECTIONS -->
                            <?php else: ?>

                                <?php if (is_array($content)): ?>

                                    <?php foreach ($content as $key => $item): ?>

                                        <article class="creative-entry">

                                            <?php if (is_array($item)): ?>

                                                <?php foreach ($item as $subKey => $subValue): ?>

                                                    <?php if (!empty($subValue)): ?>

                                                        <p class="creative-text">
                                                            <strong><?= sanitize(
                                                                        ucwords(
                                                                            str_replace(
                                                                                '_',
                                                                                ' ',
                                                                                $subKey
                                                                            )
                                                                        )
                                                                    ) ?>:</strong>
                                                            <?= sanitize(
                                                                is_array($subValue)
                                                                    ? implode(', ', $subValue)
                                                                    : $subValue
                                                            ) ?>
                                                        </p>

                                                    <?php endif; ?>

                                                <?php endforeach; ?>

                                            <?php else: ?>

                                                <p class="creative-text"><?= sanitize($item) ?></p>

                                            <?php endif; ?>

                                        </article>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <p class="creative-text"><?= sanitize($content) ?></p>

                                <?php endif; ?>

                            <?php endif; ?>

                        </div>

                    </section>

                <?php endforeach; ?>

            </main>

        </div>

    </div>