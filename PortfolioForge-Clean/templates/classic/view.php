<?php
// templates/classic/view.php
?>
<div class="pf-portfolio-body tpl-classic" style="--pf-accent: <?= sanitize($portfolio['accent_color'] ?? '#2563eb') ?>; --pf-font: <?= sanitize($portfolio['font_family'] ?? 'Georgia, serif') ?>;">
    <div class="pf-container" style="background: #ffffff; padding: 3rem; border: 1px solid #d1d5db; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <header class="pf-header">
            <?php if ($portfolio['show_profile_image'] && !empty($user['profile_image'])): ?>
                <img src="/uploads/profiles/<?= sanitize($user['profile_image']) ?>" alt="<?= sanitize($user['full_name']) ?>" class="pf-avatar" style="border-radius: 4px;">
            <?php endif; ?>
            <div>
                <h1 class="pf-name" style="font-family: Georgia, serif; font-size: 2.2rem;"><?= sanitize($user['full_name']) ?></h1>
                <div class="pf-title" style="color: #4b5563; font-style: italic; font-size: 1.1rem;"><?= sanitize($portfolio['title']) ?></div>
                <div class="pf-contact-list" style="margin-top: 0.5rem;">
                    <?php if ($portfolio['show_email'] && !empty($user['email'])): ?>
                        <span>Email: <?= sanitize($user['email']) ?></span>
                    <?php endif; ?>
                    <?php if ($resume && $resume['public_download_enabled']): ?>
                        <span>| <a href="/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>" download style="color: #111827; text-decoration: underline;">Download Resume PDF</a></span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main>
            <?php foreach ($sections as $sec): 
                if (!$sec['is_visible']) continue;
                $content = json_decode($sec['content'] ?? '', true) ?? [];
            ?>
                <section class="pf-section" style="margin-bottom: 2rem;">
                    <h2 class="pf-section-title" style="font-family: Georgia, serif; font-size: 1.3rem; text-transform: uppercase; border-bottom: 1px solid #111827; padding-bottom: 0.25rem;">
                        <?= sanitize($sec['title']) ?>
                    </h2>

                    <?php if ($sec['section_type'] === 'skills'): ?>
                        <div style="margin-top: 0.5rem; font-size: 1rem; color: #1f2937;">
                            <?php 
                            $skillsList = is_array($content) ? $content : explode(',', $content['text'] ?? '');
                            echo sanitize(implode(' ΓÇó ', array_map('trim', $skillsList)));
                            ?>
                        </div>
                    <?php elseif ($sec['section_type'] === 'about'): ?>
                        <div style="margin-top: 0.5rem; color: #1f2937; line-height: 1.7;">
                            <p style="white-space: pre-line;"><?= sanitize(is_array($content) ? ($content['text'] ?? '') : $content) ?></p>
                        </div>
                    <?php else: ?>
                        <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
                            <?php if (is_array($content)): ?>
                                <?php foreach ($content as $item): ?>
                                    <div style="padding-bottom: 0.5rem; border-bottom: 1px dashed #e5e7eb;">
                                        <p style="white-space: pre-line; color: #1f2937;"><?= sanitize(is_array($item) ? json_encode($item) : $item) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding-bottom: 0.5rem; border-bottom: 1px dashed #e5e7eb;">
                                    <p style="white-space: pre-line; color: #1f2937;"><?= sanitize($content) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </main>
    </div>
</div>

