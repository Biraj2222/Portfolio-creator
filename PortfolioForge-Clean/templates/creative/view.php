<?php
// templates/creative/view.php
?>
<div class="pf-portfolio-body tpl-creative" style="--pf-accent: <?= sanitize($portfolio['accent_color'] ?? '#2563eb') ?>; --pf-font: <?= sanitize($portfolio['font_family'] ?? 'Inter, sans-serif') ?>;">
    <div class="pf-container">
        <header class="pf-header">
            <?php if ($portfolio['show_profile_image'] && !empty($user['profile_image'])): ?>
                <img src="/uploads/profiles/<?= sanitize($user['profile_image']) ?>" alt="<?= sanitize($user['full_name']) ?>" class="pf-avatar">
            <?php endif; ?>
            <div>
                <h1 class="pf-name" style="color: var(--pf-accent);"><?= sanitize($user['full_name']) ?></h1>
                <div class="pf-title"><?= sanitize($portfolio['title']) ?></div>
                <div class="pf-contact-list">
                    <?php if ($portfolio['show_email'] && !empty($user['email'])): ?>
                        <span>Γ£¿ <?= sanitize($user['email']) ?></span>
                    <?php endif; ?>
                    <?php if ($resume && $resume['public_download_enabled']): ?>
                        <span>≡ƒôü <a href="/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>" download style="color: var(--pf-accent); font-weight: 700; text-decoration: underline;">Get My Resume PDF</a></span>
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
                    <h2 class="pf-section-title" style="color: var(--pf-accent); font-size: 1.6rem;">
                        ΓÜí <?= sanitize($sec['title']) ?>
                    </h2>

                    <?php if ($sec['section_type'] === 'skills'): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.6rem;">
                            <?php 
                            $skillsList = is_array($content) ? $content : explode(',', $content['text'] ?? '');
                            foreach ($skillsList as $sk): 
                                if (empty(trim($sk))) continue;
                            ?>
                                <span class="pf-pill" style="background: var(--pf-accent); color: #fff; font-size: 0.95rem;"><?= sanitize(trim($sk)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($sec['section_type'] === 'about'): ?>
                        <div class="pf-card">
                            <p style="white-space: pre-line; font-size: 1.05rem; color: #334155;"><?= sanitize(is_array($content) ? ($content['text'] ?? '') : $content) ?></p>
                        </div>
                    <?php else: ?>
                        <div class="pf-card-grid">
                            <?php if (is_array($content)): ?>
                                <?php foreach ($content as $item): ?>
                                    <div class="pf-card" style="border-top: 3px solid var(--pf-accent);">
                                        <p style="white-space: pre-line;"><?= sanitize(is_array($item) ? json_encode($item) : $item) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="pf-card" style="border-top: 3px solid var(--pf-accent);">
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

