<?php
/**
 * Minimal ATS-Friendly Portfolio Template
 */

$accentColor = $portfolio['accent_color'] ?? '#222222';
$fontFamily  = $portfolio['font_family'] ?? 'Arial, sans-serif';

$sections = $sections ?? [];

function minimalContent($section)
{
    $content = json_decode($section['content'] ?? '', true);

    return is_array($content)
        ? $content
        : ['text' => $section['content'] ?? ''];
}

function minimalValue($value)
{
    return is_array($value)
        ? implode(', ', array_map('strval', $value))
        : (string)$value;
}
?>

<style>
.tpl-minimal {
    --minimal-accent: <?= sanitize($accentColor) ?>;
    --minimal-font: <?= sanitize($fontFamily) ?>;

    font-family: var(--minimal-font);
    color: #222;
    background: #fff;
    line-height: 1.65;
}

.tpl-minimal .minimal-container {
    max-width: 820px;
    margin: 0 auto;
    padding: 60px 45px;
}

.tpl-minimal .minimal-header {
    margin-bottom: 42px;
}

.tpl-minimal .minimal-name {
    margin: 0;
    font-size: 38px;
    font-weight: 600;
    letter-spacing: -0.5px;
}

.tpl-minimal .minimal-title {
    margin-top: 5px;
    font-size: 17px;
    color: #666;
}

.tpl-minimal .minimal-contact {
    margin-top: 15px;
    font-size: 14px;
    color: #555;
}

.tpl-minimal .minimal-contact span {
    margin-right: 14px;
}

.tpl-minimal .minimal-contact a {
    color: inherit;
    text-decoration: underline;
}

.tpl-minimal .minimal-avatar {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 14px;
}

.tpl-minimal .minimal-section {
    margin-bottom: 38px;
}

.tpl-minimal .minimal-section-title {
    margin: 0 0 13px;
    font-size: 15px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.4px;
    color: var(--minimal-accent);
}

.tpl-minimal .minimal-text {
    margin: 0;
    white-space: pre-line;
}

.tpl-minimal .minimal-entry {
    margin-bottom: 23px;
}

.tpl-minimal .minimal-entry:last-child {
    margin-bottom: 0;
}

.tpl-minimal .minimal-entry-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
}

.tpl-minimal .minimal-meta {
    font-size: 14px;
    color: #666;
    margin: 2px 0 7px;
}

.tpl-minimal .minimal-list {
    margin: 0;
    padding-left: 20px;
}

.tpl-minimal .minimal-list li {
    margin-bottom: 5px;
}

@media (max-width: 700px) {

    .tpl-minimal .minimal-container {
        padding: 35px 22px;
    }

    .tpl-minimal .minimal-name {
        font-size: 30px;
    }

    .tpl-minimal .minimal-contact span {
        display: block;
        margin-bottom: 4px;
    }
}

@media print {

    .tpl-minimal .minimal-container {
        max-width: none;
        padding: 30px;
    }
}
</style>

<div class="pf-portfolio-body tpl-minimal">

    <div class="minimal-container">

        <header class="minimal-header">

            <?php if (
                !empty($portfolio['show_profile_image']) &&
                !empty($user['profile_image'])
            ): ?>

                <img
                    src="/PortfolioForge-Clean/uploads/profiles/<?= sanitize($user['profile_image']) ?>"
                    alt="<?= sanitize($user['full_name']) ?>"
                    class="minimal-avatar"
                >

            <?php endif; ?>

            <h1 class="minimal-name">
                <?= sanitize($user['full_name'] ?? '') ?>
            </h1>

            <?php if (!empty($portfolio['title'])): ?>

                <div class="minimal-title">
                    <?= sanitize($portfolio['title']) ?>
                </div>

            <?php endif; ?>

            <div class="minimal-contact">

                <?php if (
                    !empty($portfolio['show_email']) &&
                    !empty($user['email'])
                ): ?>

                    <span>
                        <?= sanitize($user['email']) ?>
                    </span>

                <?php endif; ?>

                <?php
                $phone = '';
                $location = '';

                foreach ($sections as $section) {

                    if (($section['section_type'] ?? '') !== 'contact') {
                        continue;
                    }

                    $contact = minimalContent($section);

                    if (!empty($contact['phone'])) {
                        $phone = $contact['phone'];
                    }

                    if (!empty($contact['location'])) {
                        $location = $contact['location'];
                    }

                    if (!empty($contact['text'])) {

                        if (
                            preg_match(
                                '/(?:Phone|Mobile|Tel|Telephone)\s*:\s*(.+)/i',
                                $contact['text'],
                                $m
                            )
                        ) {
                            $phone = trim($m[1]);
                        }

                        if (
                            preg_match(
                                '/(?:Location|Address|City)\s*:\s*(.+)/i',
                                $contact['text'],
                                $m
                            )
                        ) {
                            $location = trim($m[1]);
                        }
                    }
                }
                ?>

                <?php if (!empty($phone)): ?>

                    <span>
                        <?= sanitize($phone) ?>
                    </span>

                <?php endif; ?>

                <?php if (!empty($location)): ?>

                    <span>
                        <?= sanitize($location) ?>
                    </span>

                <?php endif; ?>

            </div>

        </header>


        <main>

            <?php foreach ($sections as $sec): ?>

                <?php

                if (empty($sec['is_visible'])) {
                    continue;
                }

                $content = minimalContent($sec);
                $type = $sec['section_type'] ?? '';

                ?>

                <section class="minimal-section">

                    <h2 class="minimal-section-title">
                        <?= sanitize($sec['title'] ?? '') ?>
                    </h2>


                    <?php if ($type === 'about'): ?>

                        <p class="minimal-text">
                            <?= sanitize(
                                $content['text']
                                ?? $content['description']
                                ?? ''
                            ) ?>
                        </p>


                    <?php elseif ($type === 'skills'): ?>

                        <?php
                        $skills =
                            isset($content['skills']) &&
                            is_array($content['skills'])
                                ? $content['skills']
                                : preg_split(
                                    '/\r\n|\r|\n|,/',
                                    $content['text'] ?? ''
                                );
                        ?>

                        <ul class="minimal-list">

                            <?php foreach ($skills as $skill): ?>

                                <?php if (trim($skill) !== ''): ?>

                                    <li>
                                        <?= sanitize(trim($skill)) ?>
                                    </li>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </ul>


                    <?php elseif ($type === 'experience'): ?>

                        <div class="minimal-entry">

                            <?php if (!empty($content['job_title'])): ?>

                                <h3 class="minimal-entry-title">
                                    <?= sanitize($content['job_title']) ?>
                                </h3>

                            <?php endif; ?>

                            <div class="minimal-meta">

                                <?php if (!empty($content['company'])): ?>
                                    <?= sanitize($content['company']) ?>
                                <?php endif; ?>

                                <?php if (
                                    !empty($content['company']) &&
                                    !empty($content['duration'])
                                ): ?>
                                    ·
                                <?php endif; ?>

                                <?php if (!empty($content['duration'])): ?>
                                    <?= sanitize($content['duration']) ?>
                                <?php endif; ?>

                            </div>

                            <?php if (!empty($content['description'])): ?>

                                <p class="minimal-text">
                                    <?= sanitize($content['description']) ?>
                                </p>

                            <?php endif; ?>

                            <?php if (!empty($content['text'])): ?>

                                <p class="minimal-text">
                                    <?= sanitize($content['text']) ?>
                                </p>

                            <?php endif; ?>

                        </div>


                    <?php elseif ($type === 'education'): ?>

                        <div class="minimal-entry">

                            <?php if (!empty($content['degree'])): ?>

                                <h3 class="minimal-entry-title">
                                    <?= sanitize($content['degree']) ?>
                                </h3>

                            <?php endif; ?>

                            <div class="minimal-meta">

                                <?php if (!empty($content['institution'])): ?>
                                    <?= sanitize($content['institution']) ?>
                                <?php endif; ?>

                                <?php if (
                                    !empty($content['institution']) &&
                                    !empty($content['year'])
                                ): ?>
                                    ·
                                <?php endif; ?>

                                <?php if (!empty($content['year'])): ?>
                                    <?= sanitize($content['year']) ?>
                                <?php endif; ?>

                            </div>

                            <?php if (!empty($content['description'])): ?>

                                <p class="minimal-text">
                                    <?= sanitize($content['description']) ?>
                                </p>

                            <?php endif; ?>

                            <?php if (!empty($content['text'])): ?>

                                <p class="minimal-text">
                                    <?= sanitize($content['text']) ?>
                                </p>

                            <?php endif; ?>

                        </div>


                    <?php elseif ($type === 'projects'): ?>

                        <div class="minimal-entry">

                            <?php if (!empty($content['project_name'])): ?>

                                <h3 class="minimal-entry-title">
                                    <?= sanitize($content['project_name']) ?>
                                </h3>

                            <?php endif; ?>

                            <?php if (!empty($content['technologies'])): ?>

                                <div class="minimal-meta">
                                    <?= sanitize($content['technologies']) ?>
                                </div>

                            <?php endif; ?>

                            <?php if (!empty($content['description'])): ?>

                                <p class="minimal-text">
                                    <?= sanitize($content['description']) ?>
                                </p>

                            <?php endif; ?>

                            <?php if (!empty($content['link'])): ?>

                                <p>
                                    <a
                                        href="<?= sanitize($content['link']) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <?= sanitize($content['link']) ?>
                                    </a>
                                </p>

                            <?php endif; ?>

                        </div>


                    <?php elseif ($type === 'certifications'): ?>

                        <div class="minimal-entry">

                            <?php if (!empty($content['name'])): ?>

                                <h3 class="minimal-entry-title">
                                    <?= sanitize($content['name']) ?>
                                </h3>

                            <?php endif; ?>

                            <div class="minimal-meta">

                                <?= sanitize(
                                    $content['issuer'] ?? ''
                                ) ?>

                                <?php if (
                                    !empty($content['issuer']) &&
                                    !empty($content['year'])
                                ): ?>
                                    ·
                                <?php endif; ?>

                                <?= sanitize(
                                    $content['year'] ?? ''
                                ) ?>

                            </div>

                            <?php if (!empty($content['text'])): ?>

                                <p class="minimal-text">
                                    <?= sanitize($content['text']) ?>
                                </p>

                            <?php endif; ?>

                        </div>


                    <?php elseif ($type === 'languages'): ?>

                        <?php
                        $languages =
                            isset($content['languages']) &&
                            is_array($content['languages'])
                                ? $content['languages']
                                : preg_split(
                                    '/\r\n|\r|\n|,/',
                                    $content['text'] ?? ''
                                );
                        ?>

                        <ul class="minimal-list">

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
                        $interests =
                            isset($content['interests']) &&
                            is_array($content['interests'])
                                ? $content['interests']
                                : preg_split(
                                    '/\r\n|\r|\n|,/',
                                    $content['text'] ?? ''
                                );
                        ?>

                        <ul class="minimal-list">

                            <?php foreach ($interests as $interest): ?>

                                <?php if (trim($interest) !== ''): ?>

                                    <li>
                                        <?= sanitize(trim($interest)) ?>
                                    </li>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </ul>


                    <?php elseif ($type === 'contact'): ?>

                        <p class="minimal-text">
                            <?= sanitize($content['text'] ?? '') ?>
                        </p>


                    <?php else: ?>

                        <?php if (!empty($content['text'])): ?>

                            <p class="minimal-text">
                                <?= sanitize($content['text']) ?>
                            </p>

                        <?php else: ?>

                            <?php foreach ($content as $key => $value): ?>

                                <?php if (empty($value)) continue; ?>

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

                                    <?= sanitize(
                                        minimalValue($value)
                                    ) ?>

                                </p>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    <?php endif; ?>

                </section>

            <?php endforeach; ?>

        </main>

    </div>

</div>