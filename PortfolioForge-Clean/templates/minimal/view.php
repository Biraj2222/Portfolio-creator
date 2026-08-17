<?php
// templates/minimal/view.php
?>
<div class="pf-portfolio-body tpl-minimal" style="--pf-accent: <?= sanitize($portfolio['accent_color'] ?? '#2563eb') ?>; --pf-font: <?= sanitize($portfolio['font_family'] ?? 'Inter, sans-serif') ?>;">
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
                        <span><?= sanitize($user['email']) ?></span>
                    <?php endif; ?>
                    <?php if ($resume && $resume['public_download_enabled']): ?>
                        <span><a href="/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>" download style="color: var(--pf-accent); font-weight: 600; text-decoration: underline;">Download Resume</a></span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main>
            <?php foreach ($sections as $sec): 
                if (!$sec['is_visible']) continue;
                $content = json_decode($sec['content'] ?? '', true) ?? [];
            ?>
                <section class="pf-section">
                    <h2 class="pf-section-title" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; color: #334155;">
                        <?= sanitize($sec['title']) ?>
                    </h2>

                    <?php if ($sec['section_type'] === 'skills'): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <?php 
                            $skillsList = is_array($content) ? $content : explode(',', $content['text'] ?? '');
                            foreach ($skillsList as $sk): 
                                if (empty(trim($sk))) continue;
                            ?>
                                <span class="pf-pill" style="background: #f1f5f9; color: #334155;"><?= sanitize(trim($sk)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($sec['section_type'] === 'about'): ?>
                        <div style="padding: 0.5rem 0; color: #475569;">
                            <p style="white-space: pre-line;"><?= sanitize(is_array($content) ? ($content['text'] ?? '') : $content) ?></p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php if (is_array($content)): ?>
                                <?php foreach ($content as $item): ?>
                                    <div class="pf-card">
                                        <p style="white-space: pre-line;"><?= sanitize(is_array($item) ? json_encode($item) : $item) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="pf-card">
                                    <p style="white-space: pre-line;"><?= sanitize($content) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </main>
    </div>
</div>

