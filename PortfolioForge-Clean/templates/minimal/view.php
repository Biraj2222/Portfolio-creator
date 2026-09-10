<?php

/**
 * Minimal ATS-Friendly Portfolio Template
 * @var array $user
 * @var array $portfolio
 * @var array $sections
 * @var array|null $resume
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
        padding: 20px 60px 45px 60px;
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
        padding-left: 40px;

    }

    .tpl-minimal .minimal-entry {
        margin-bottom: 23px;
        padding-right: 20px;
        background: #fafafa;
        border-radius: 6px;
        border: 1px solid #f0f0f0;
        text-align: left;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .tpl-minimal .minimal-entry:hover {
        transform: translateY(-2px);
        background: #ffffff;
        border-color: var(--minimal-accent);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    }

    .tpl-minimal .minimal-entry:last-child {
        margin-bottom: 0;
    }

   .tpl-minimal .minimal-entry-title {
    margin: 0 0 8px;
    font-size: 16px;
    font-weight: 700;
}

    .tpl-minimal .minimal-meta {
        font-size: 14px;
        color: #666;
        margin: 2px 0 7px;
    }

    .tpl-minimal.minimal-list {
        margin-bottom: 23px;
        padding: 16px 20px;
        background: #fafafa;
        border-radius: 6px;
        border: 1px solid #f0f0f0;
        text-align: left;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .tpl-minimal .minimal-list {
        margin: 0;
        padding-left: 40px;
        background: #fafafa;
        border-radius: 6px;
        border: 1px solid #f0f0f0;
        text-align: left;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .tpl-minimal .minimal-list:hover {
        transform: translateY(-2px);
        background: #ffffff;
        border-color: var(--minimal-accent);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);

    }

    .tpl-minimal .minimal-list:last-child {
        margin-bottom: 0;
    }

    .tpl-minimal .minimal-entry-title {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
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
                    class="minimal-avatar">

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

                <?php if (
                    !empty($resume) &&
                    !empty($resume['public_download_enabled']) &&
                    !empty($resume['file_path'])
                ): ?>

                    <span style="display: block; margin-top: 0.6rem;">
                        <a href="/PortfolioForge-Clean/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>"
                            download
                            style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.9rem; border: 1.5px solid var(--minimal-accent); color: var(--minimal-accent); border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: background-color 0.2s;">
                            📄 Download CV / Resume
                        </a>
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

                        <?php
                        $expItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($expItems as $item): ?>

                            <div class="minimal-entry">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['job_title'])): ?>

                                        <h3 class="minimal-entry-title">
                                            <?= sanitize($item['job_title']) ?>
                                        </h3>

                                    <?php endif; ?>

                                    <div class="minimal-meta">

                                        <?php if (!empty($item['company'])): ?>
                                            <?= sanitize($item['company']) ?>
                                        <?php endif; ?>

                                        <?php if (
                                            !empty($item['company']) &&
                                            !empty($item['duration'])
                                        ): ?>
                                            ·
                                        <?php endif; ?>

                                        <?php if (!empty($item['duration'])): ?>
                                            <?= sanitize($item['duration']) ?>
                                        <?php endif; ?>

                                    </div>

                                    <?php if (!empty($item['description'])): ?>

                                        <p class="minimal-text">
                                            <?= sanitize($item['description']) ?>
                                        </p>

                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>

                                        <p class="minimal-text">
                                            <?= sanitize($item['text']) ?>
                                        </p>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <p class="minimal-text">
                                        <?= sanitize($item) ?>
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>


                    <?php elseif ($type === 'education'): ?>

                        <?php
                        $eduItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($eduItems as $item): ?>

                            <div class="minimal-entry">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['degree'])): ?>

                                        <h3 class="minimal-entry-title">
                                            <?= sanitize($item['degree']) ?>
                                        </h3>

                                    <?php endif; ?>

                                    <div class="minimal-meta">

                                        <?php if (!empty($item['institution'])): ?>
                                            <?= sanitize($item['institution']) ?>
                                        <?php endif; ?>

                                        <?php if (
                                            !empty($item['institution']) &&
                                            !empty($item['year'])
                                        ): ?>
                                            ·
                                        <?php endif; ?>

                                        <?php if (!empty($item['year'])): ?>
                                            <?= sanitize($item['year']) ?>
                                        <?php endif; ?>

                                    </div>

                                    <?php if (!empty($item['description'])): ?>

                                        <p class="minimal-text">
                                            <?= sanitize($item['description']) ?>
                                        </p>

                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>

                                        <p class="minimal-text">
                                            <?= sanitize($item['text']) ?>
                                        </p>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <p class="minimal-text">
                                        <?= sanitize($item) ?>
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>


                    <?php elseif ($type === 'projects'): ?>

    <div class="minimal-entry">

        <p class="minimal-text">
            <?= nl2br(sanitize($content['text'] ?? '')) ?>
        </p>

    </div>


                    <?php elseif ($type === 'certifications'): ?>

                        <?php
                        $certItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($certItems as $item): ?>

                            <div class="minimal-entry">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['name'])): ?>

                                        <h3 class="minimal-entry-title">
                                            <?= sanitize($item['name']) ?>
                                        </h3>

                                    <?php endif; ?>

                                    <div class="minimal-meta">

                                        <?= sanitize(
                                            $item['issuer'] ?? ''
                                        ) ?>

                                        <?php if (
                                            !empty($item['issuer']) &&
                                            !empty($item['year'])
                                        ): ?>
                                            ·
                                        <?php endif; ?>

                                        <?= sanitize(
                                            $item['year'] ?? ''
                                        ) ?>

                                    </div>

                                    <?php if (!empty($item['description'])): ?>

                                        <p class="minimal-text">
                                            <?= sanitize($item['description']) ?>
                                        </p>

                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>

                                        <p class="minimal-text">
                                            <?= sanitize($item['text']) ?>
                                        </p>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <p class="minimal-text">
                                        <?= sanitize($item) ?>
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>


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