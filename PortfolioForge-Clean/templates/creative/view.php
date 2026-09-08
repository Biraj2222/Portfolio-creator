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

<div class="pf-portfolio-body tpl-creative"
    style="--pf-accent: <?= sanitize($accentColor) ?>;">

    <div class="pf-container creative-container">

        <!-- HEADER -->
        <header class="creative-header">

            <div class="creative-intro">

                <div class="creative-label">
                    PORTFOLIO
                </div>

                <h1 class="creative-name">
                    <?= sanitize($user['full_name'] ?? '') ?>
                </h1>

                <?php if (!empty($portfolio['title'])): ?>
                    <div class="creative-title">
                        <?= sanitize($portfolio['title']) ?>
                    </div>
                <?php endif; ?>

                <div class="creative-contact">

                    <?php if (!empty($portfolio['show_email']) && !empty($user['email'])): ?>
                        <span><?= sanitize($user['email']) ?></span>
                    <?php endif; ?>

                    <?php if (!empty($resume) && !empty($resume['public_download_enabled'])): ?>
                        <span>
                            <a href="/PortfolioForge-Clean/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>"
                                download>
                                Download Resume
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

                        <h2 class="creative-section-title">
                            <?= sanitize($sectionHeading) ?>
                        </h2>


                        <!-- ABOUT -->
                        <?php if ($sectionType === 'about'): ?>

                            <?php
                            $aboutText = is_array($content)
                                ? ($content['text'] ?? $content['description'] ?? '')
                                : $content;
                            ?>

                            <p class="creative-text">
                                <?= sanitize($aboutText) ?>
                            </p>


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

                            <p class="creative-skills">
                                <?= sanitize(implode(', ', $skillsList)) ?>
                            </p>


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

                            <p class="creative-text">
                                <?= sanitize(implode(', ', $languages)) ?>
                            </p>


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

                            <p class="creative-text">
                                <?= sanitize(implode(', ', $interests)) ?>
                            </p>


                            <!-- EXPERIENCE -->
                        <?php elseif ($sectionType === 'experience'): ?>

                            <?php foreach ((array)$content as $item): ?>

                                <article class="creative-entry">

                                    <?php if (is_array($item)): ?>

                                        <div class="creative-entry-header">

                                            <?php if (!empty($item['job_title'])): ?>
                                                <h3>
                                                    <?= sanitize($item['job_title']) ?>
                                                </h3>
                                            <?php endif; ?>

                                            <?php if (!empty($item['duration'])): ?>
                                                <span>
                                                    <?= sanitize($item['duration']) ?>
                                                </span>
                                            <?php endif; ?>

                                        </div>

                                        <?php if (!empty($item['company'])): ?>
                                            <div class="creative-company">
                                                <?= sanitize($item['company']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        $description =
                                            $item['description']
                                            ?? $item['text']
                                            ?? '';
                                        ?>

                                        <?php if (!empty($description)): ?>
                                            <p class="creative-text">
                                                <?= sanitize($description) ?>
                                            </p>
                                        <?php endif; ?>

                                    <?php else: ?>

                                        <p class="creative-text">
                                            <?= sanitize($item) ?>
                                        </p>

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
                                                <h3>
                                                    <?= sanitize($item['degree']) ?>
                                                </h3>
                                            <?php endif; ?>

                                            <?php if (!empty($item['year'])): ?>
                                                <span>
                                                    <?= sanitize($item['year']) ?>
                                                </span>
                                            <?php endif; ?>

                                        </div>

                                        <?php if (!empty($item['institution'])): ?>
                                            <div class="creative-company">
                                                <?= sanitize($item['institution']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        $description =
                                            $item['description']
                                            ?? $item['text']
                                            ?? '';
                                        ?>

                                        <?php if (!empty($description)): ?>
                                            <p class="creative-text">
                                                <?= sanitize($description) ?>
                                            </p>
                                        <?php endif; ?>

                                    <?php else: ?>

                                        <p class="creative-text">
                                            <?= sanitize($item) ?>
                                        </p>

                                    <?php endif; ?>

                                </article>

                            <?php endforeach; ?>


                            <!-- PROJECTS -->
                        <?php elseif ($sectionType === 'projects'): ?>

                            <?php foreach ((array)$content as $item): ?>

                                <article class="creative-entry">

                                    <?php if (is_array($item)): ?>

                                        <?php if (!empty($item['project_name'])): ?>
                                            <h3>
                                                <?= sanitize($item['project_name']) ?>
                                            </h3>
                                        <?php endif; ?>

                                        <?php if (!empty($item['technologies'])): ?>
                                            <div class="creative-company">
                                                <?= sanitize($item['technologies']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        $description =
                                            $item['description']
                                            ?? $item['text']
                                            ?? '';
                                        ?>

                                        <?php if (!empty($description)): ?>
                                            <p class="creative-text">
                                                <?= sanitize($description) ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($item['link'])): ?>
                                            <a href="<?= sanitize($item['link']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer">
                                                View Project
                                            </a>
                                        <?php endif; ?>

                                    <?php else: ?>

                                        <p class="creative-text">
                                            <?= sanitize($item) ?>
                                        </p>

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
                                            <div class="creative-company">
                                                <?= sanitize($item['issuer']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($item['year'])): ?>
                                            <span>
                                                <?= sanitize($item['year']) ?>
                                            </span>
                                        <?php endif; ?>

                                    <?php else: ?>

                                        <p class="creative-text">
                                            <?= sanitize($item) ?>
                                        </p>

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
                                                        <strong>
                                                            <?= sanitize(
                                                                ucwords(
                                                                    str_replace(
                                                                        '_',
                                                                        ' ',
                                                                        $subKey
                                                                    )
                                                                )
                                                            ) ?>:
                                                        </strong>

                                                        <?= sanitize(
                                                            is_array($subValue)
                                                                ? implode(', ', $subValue)
                                                                : $subValue
                                                        ) ?>
                                                    </p>

                                                <?php endif; ?>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <p class="creative-text">
                                                <?= sanitize($item) ?>
                                            </p>

                                        <?php endif; ?>

                                    </article>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <p class="creative-text">
                                    <?= sanitize($content) ?>
                                </p>

                            <?php endif; ?>

                        <?php endif; ?>

                    </div>

                </section>

            <?php endforeach; ?>

        </main>

    </div>

</div>