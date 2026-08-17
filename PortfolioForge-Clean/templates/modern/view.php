<?php
// templates/modern/view.php
// Variables available: $user, $portfolio, $sections, $resume
?>
<div class="pf-portfolio-body tpl-modern" style="--pf-accent: <?= sanitize($portfolio['accent_color'] ?? '#2563eb') ?>; --pf-font: <?= sanitize($portfolio['font_family'] ?? 'Inter, sans-serif') ?>;">
    <div class="pf-container">
        <header class="pf-header">
            <?php if ($portfolio['show_profile_image'] && !empty($user['profile_image'])): ?>
                <img src="/uploads/profiles/<?= sanitize($user['profile_image']) ?>" alt="<?= sanitize($user['full_name']) ?>" class="pf-avatar">
            <?php endif; ?>
            <div>
                <h1 class="pf-name"><?= sanitize($user['full_name']) ?></h1>
                <div class="pf-title"><?= sanitize($portfolio['title']) ?></div>
               <div class="pf-contact-list">

    <?php if ($portfolio['show_email'] && !empty($user['email'])): ?>
        <span>📧 <?= sanitize($user['email']) ?></span>
    <?php endif; ?>

    <?php
    // Get phone and location from the portfolio Contact section
    $portfolioPhone = '';
    $portfolioLocation = '';

    foreach ($sections as $section) {
        if ($section['section_type'] === 'contact') {
            $contactData = json_decode($section['content'] ?? '', true);

            if (is_array($contactData)) {
                $portfolioPhone = $contactData['phone'] ?? '';
                $portfolioLocation = $contactData['location'] ?? '';
            }
        }
    }
    ?>

    <?php if (!empty($portfolioPhone)): ?>
        <span>📞 <?= sanitize($portfolioPhone) ?></span>
    <?php endif; ?>

    <?php if (!empty($portfolioLocation)): ?>
        <span>📍 <?= sanitize($portfolioLocation) ?></span>
    <?php endif; ?>

    <?php if ($resume && $resume['public_download_enabled']): ?>
        <span>
            📄
            <a href="/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>"
               download
               style="color: #93c5fd; font-weight: 600; text-decoration: underline;">
                Download Resume PDF
            </a>
        </span>
    <?php endif; ?>

</div>
            </div>
        </header>

       <main>

<?php foreach ($sections as $sec):

    if (!$sec['is_visible']) continue;

    $content = json_decode($sec['content'] ?? '', true);

    if (!is_array($content)) {
        $content = ['text' => $sec['content'] ?? ''];
    }

?>

<section class="pf-section">

    <h2 class="pf-section-title">
        <span style="color: var(--pf-accent);">✦</span>
        <?= sanitize($sec['title']) ?>
    </h2>


    <!-- ABOUT -->
    <?php if ($sec['section_type'] === 'about'): ?>

        <div class="pf-card">
            <p style="white-space: pre-line;">
                <?= sanitize($content['description'] ?? $content['text'] ?? '') ?>
            </p>
        </div>


    <!-- CONTACT -->
    <?php elseif ($sec['section_type'] === 'contact'): ?>

        <div class="pf-card-grid">

            <?php if (!empty($content['phone'])): ?>
                <div class="pf-card">
                    <strong>Phone</strong>
                    <p><?= sanitize($content['phone']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($content['location'])): ?>
                <div class="pf-card">
                    <strong>Location</strong>
                    <p><?= sanitize($content['location']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($content['email'])): ?>
                <div class="pf-card">
                    <strong>Email</strong>
                    <p><?= sanitize($content['email']) ?></p>
                </div>
            <?php endif; ?>

        </div>


    <!-- SKILLS -->
    <?php elseif ($sec['section_type'] === 'skills'): ?>

        <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">

            <?php foreach (($content['skills'] ?? []) as $skill): ?>

                <?php if (trim($skill) !== ''): ?>

                    <span class="pf-pill">
                        <?= sanitize(trim($skill)) ?>
                    </span>

                <?php endif; ?>

            <?php endforeach; ?>

        </div>


    <!-- EDUCATION -->
    <?php elseif ($sec['section_type'] === 'education'): ?>

        <div class="pf-card">

            <?php if (!empty($content['degree'])): ?>
                <p>
                    <strong>Degree:</strong>
                    <?= sanitize($content['degree']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['institution'])): ?>
                <p>
                    <strong>Institution:</strong>
                    <?= sanitize($content['institution']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['year'])): ?>
                <p>
                    <strong>Year:</strong>
                    <?= sanitize($content['year']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['description'])): ?>
                <p style="white-space:pre-line;">
                    <strong>Description:</strong><br>
                    <?= sanitize($content['description']) ?>
                </p>
            <?php endif; ?>

        </div>


    <!-- EXPERIENCE -->
    <?php elseif ($sec['section_type'] === 'experience'): ?>

        <div class="pf-card">

            <?php if (!empty($content['job_title'])): ?>
                <p>
                    <strong>Job Title:</strong>
                    <?= sanitize($content['job_title']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['company'])): ?>
                <p>
                    <strong>Company:</strong>
                    <?= sanitize($content['company']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['duration'])): ?>
                <p>
                    <strong>Duration:</strong>
                    <?= sanitize($content['duration']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['description'])): ?>
                <p style="white-space:pre-line;">
                    <strong>Description:</strong><br>
                    <?= sanitize($content['description']) ?>
                </p>
            <?php endif; ?>

        </div>


    <!-- PROJECTS -->
    <?php elseif ($sec['section_type'] === 'projects'): ?>

        <div class="pf-card">

            <?php if (!empty($content['project_name'])): ?>
                <p>
                    <strong>Project:</strong>
                    <?= sanitize($content['project_name']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['technologies'])): ?>
                <p>
                    <strong>Technologies:</strong>
                    <?= sanitize($content['technologies']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['description'])): ?>
                <p style="white-space:pre-line;">
                    <strong>Description:</strong><br>
                    <?= sanitize($content['description']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['link'])): ?>
                <p>
                    <strong>Link:</strong>
                    <a href="<?= sanitize($content['link']) ?>" target="_blank">
                        View Project
                    </a>
                </p>
            <?php endif; ?>

        </div>


    <!-- CERTIFICATIONS -->
    <?php elseif ($sec['section_type'] === 'certifications'): ?>

        <div class="pf-card">

            <?php if (!empty($content['name'])): ?>
                <p>
                    <strong>Certificate:</strong>
                    <?= sanitize($content['name']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['issuer'])): ?>
                <p>
                    <strong>Issued By:</strong>
                    <?= sanitize($content['issuer']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['year'])): ?>
                <p>
                    <strong>Year:</strong>
                    <?= sanitize($content['year']) ?>
                </p>
            <?php endif; ?>

        </div>


    <!-- ACHIEVEMENTS -->
    <?php elseif ($sec['section_type'] === 'achievements'): ?>

        <div class="pf-card">

            <?php if (!empty($content['title'])): ?>
                <p>
                    <strong>Achievement:</strong>
                    <?= sanitize($content['title']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['description'])): ?>
                <p style="white-space:pre-line;">
                    <?= sanitize($content['description']) ?>
                </p>
            <?php endif; ?>

        </div>


    <!-- LANGUAGES -->
    <?php elseif ($sec['section_type'] === 'languages'): ?>

        <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">

            <?php foreach (($content['languages'] ?? []) as $language): ?>

                <?php if (trim($language) !== ''): ?>

                    <span class="pf-pill">
                        <?= sanitize(trim($language)) ?>
                    </span>

                <?php endif; ?>

            <?php endforeach; ?>

        </div>


    <!-- ACTIVITIES -->
    <?php elseif ($sec['section_type'] === 'activities'): ?>

        <div class="pf-card">

            <?php if (!empty($content['activity'])): ?>
                <p>
                    <strong>Activity:</strong>
                    <?= sanitize($content['activity']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($content['description'])): ?>
                <p style="white-space:pre-line;">
                    <?= sanitize($content['description']) ?>
                </p>
            <?php endif; ?>

        </div>


    <!-- INTERESTS -->
    <?php elseif ($sec['section_type'] === 'interests'): ?>

        <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">

            <?php foreach (($content['interests'] ?? []) as $interest): ?>

                <?php if (trim($interest) !== ''): ?>

                    <span class="pf-pill">
                        <?= sanitize(trim($interest)) ?>
                    </span>

                <?php endif; ?>

            <?php endforeach; ?>

        </div>


    <!-- FALLBACK -->
    <?php else: ?>

        <div class="pf-card">

            <?php foreach ($content as $key => $value): ?>

                <?php if (is_array($value)): ?>

                    <p>
                        <strong><?= sanitize(ucwords(str_replace('_', ' ', $key))) ?>:</strong>
                        <?= sanitize(implode(', ', $value)) ?>
                    </p>

                <?php else: ?>

                    <p style="white-space:pre-line;">
                        <strong><?= sanitize(ucwords(str_replace('_', ' ', $key))) ?>:</strong>
                        <?= sanitize($value) ?>
                    </p>

                <?php endif; ?>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<?php endforeach; ?>

</main>
    </div>
</div>

