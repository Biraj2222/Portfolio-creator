<?php
/**
 * Variables provided by the parent portfolio page:
 *
 * @var array $user
 * @var array $portfolio
 * @var array $sections
 * @var array|null $resume
 */

$accentColor = $portfolio['accent_color'] ?? '#2563eb';
$fontFamily  = $portfolio['font_family'] ?? 'Inter, sans-serif';

$sections = $sections ?? [];

?>

<div
    class="pf-portfolio-body tpl-modern"
    style="
        --pf-accent: <?= sanitize($accentColor) ?>;
        --pf-font: <?= sanitize($fontFamily) ?>;
    "
>

    <div class="pf-container">


        <!-- ========================================================= -->
        <!-- HEADER -->
        <!-- ========================================================= -->

        <header class="pf-header">

            <?php if (
                !empty($portfolio['show_profile_image']) &&
                !empty($user['profile_image'])
            ): ?>

                <img
                    src="/PortfolioForge-Clean/uploads/profiles/<?= sanitize($user['profile_image']) ?>"
                    alt="<?= sanitize($user['full_name']) ?>"
                    class="pf-avatar"
                >

            <?php endif; ?>


            <div>

                <h1 class="pf-name">
                    <?= sanitize($user['full_name']) ?>
                </h1>


                <div class="pf-title">
                    <?= sanitize($portfolio['title'] ?? '') ?>
                </div>


                <!-- CONTACT HEADER -->

                <div class="pf-contact-list">


                    <?php if (
                        !empty($portfolio['show_email']) &&
                        !empty($user['email'])
                    ): ?>

                        <span>
                            📧
                            <?= sanitize($user['email']) ?>
                        </span>

                    <?php endif; ?>


                    <?php
                    /*
                     * Get contact information.
                     *
                     * The Sections Manager now stores contact
                     * information as:
                     *
                     * {
                     *     "text": "Phone: ...\nLocation: ...\nEmail: ..."
                     * }
                     *
                     * Older sections may still contain:
                     *
                     * {
                     *     "phone": "...",
                     *     "location": "...",
                     *     "email": "..."
                     * }
                     */

                    $portfolioPhone = '';
                    $portfolioLocation = '';

                    foreach ($sections as $contactSection) {

                        if (
                            ($contactSection['section_type'] ?? '') !== 'contact'
                        ) {
                            continue;
                        }


                        $contactData = json_decode(
                            $contactSection['content'] ?? '',
                            true
                        );


                        if (!is_array($contactData)) {
                            continue;
                        }


                        /*
                         * New format.
                         */

                        if (!empty($contactData['text'])) {

                            $contactText =
                                trim($contactData['text']);

                            /*
                             * Try to find phone.
                             */

                            if (
                                preg_match(
                                    '/(?:Phone|Mobile|Tel|Telephone)\s*:\s*(.+)/i',
                                    $contactText,
                                    $matches
                                )
                            ) {

                                $portfolioPhone =
                                    trim($matches[1]);
                            }


                            /*
                             * Try to find location.
                             */

                            if (
                                preg_match(
                                    '/(?:Location|Address|City)\s*:\s*(.+)/i',
                                    $contactText,
                                    $matches
                                )
                            ) {

                                $portfolioLocation =
                                    trim($matches[1]);
                            }

                        }


                        /*
                         * Older structured format.
                         */

                        if (
                            empty($portfolioPhone) &&
                            !empty($contactData['phone'])
                        ) {

                            $portfolioPhone =
                                trim($contactData['phone']);
                        }


                        if (
                            empty($portfolioLocation) &&
                            !empty($contactData['location'])
                        ) {

                            $portfolioLocation =
                                trim($contactData['location']);
                        }
                    }
                    ?>


                    <?php if (!empty($portfolioPhone)): ?>

                        <span>
                            📞
                            <?= sanitize($portfolioPhone) ?>
                        </span>

                    <?php endif; ?>


                    <?php if (!empty($portfolioLocation)): ?>

                        <span>
                            📍
                            <?= sanitize($portfolioLocation) ?>
                        </span>

                    <?php endif; ?>


                    <?php if (
                        !empty($resume) &&
                        !empty($resume['public_download_enabled'])
                    ): ?>

                        <span>

                            📄

                            <a
                                href="/PortfolioForge-Clean/uploads/resumes/<?= sanitize(basename($resume['file_path'])) ?>"
                                download
                                style="
                                    color:#93c5fd;
                                    font-weight:600;
                                    text-decoration:underline;
                                "
                            >
                                Download Resume
                            </a>

                        </span>

                    <?php endif; ?>


                </div>

            </div>

        </header>



        <!-- ========================================================= -->
        <!-- MAIN -->
        <!-- ========================================================= -->

        <main>


            <?php foreach ($sections as $sec): ?>


                <?php

                if (empty($sec['is_visible'])) {
                    continue;
                }


                $content =
                    json_decode(
                        $sec['content'] ?? '',
                        true
                    );


                if (!is_array($content)) {

                    $content = [
                        'text' =>
                            $sec['content'] ?? ''
                    ];
                }


                $sectionType =
                    $sec['section_type'] ?? '';


                $sectionText =
                    $content['text']
                    ?? $content['description']
                    ?? '';

                ?>


                <section class="pf-section">


                    <!-- SECTION TITLE -->

                    <h2 class="pf-section-title">

                        <span style="color:var(--pf-accent);">
                            ✦
                        </span>

                        <?= sanitize($sec['title'] ?? '') ?>

                    </h2>



                    <!-- ================================================= -->
                    <!-- ABOUT -->
                    <!-- ================================================= -->

                    <?php if ($sectionType === 'about'): ?>


                       <div class="pf-card" style="padding-top:0.75rem;">

                         <p style="white-space:pre-line; margin:0;"><?= sanitize(
                           $content['text']
                         ?? $content['description']
                          ?? ''
                          ) ?></p>

</div>



                    <!-- ================================================= -->
                    <!-- CONTACT -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'contact'): ?>


                        <?php

                        /*
                         * CONTACT NOW USES THE SAME TEXT FORMAT
                         * AS THE SECTIONS MANAGER.
                         */

                        $contactText =
                            $content['text']
                            ?? '';


                        /*
                         * If the old structured format exists,
                         * build readable contact text from it.
                         */

                        if (
                            empty(trim($contactText)) &&
                            (
                                !empty($content['phone']) ||
                                !empty($content['location']) ||
                                !empty($content['email']) ||
                                !empty($content['linkedin']) ||
                                !empty($content['github'])
                            )
                        ) {

                            $contactLines = [];


                            if (!empty($content['phone'])) {

                                $contactLines[] =
                                    'Phone: ' .
                                    $content['phone'];
                            }


                            if (!empty($content['location'])) {

                                $contactLines[] =
                                    'Location: ' .
                                    $content['location'];
                            }


                            if (!empty($content['email'])) {

                                $contactLines[] =
                                    'Email: ' .
                                    $content['email'];
                            }


                            if (!empty($content['linkedin'])) {

                                $contactLines[] =
                                    'LinkedIn: ' .
                                    $content['linkedin'];
                            }


                            if (!empty($content['github'])) {

                                $contactLines[] =
                                    'GitHub: ' .
                                    $content['github'];
                            }


                            $contactText =
                                implode(
                                    "\n",
                                    $contactLines
                                );
                        }

                        ?>


                        <div class="pf-card">

                            <?php if (
                                !empty(trim($contactText))
                            ): ?>

                                <p
                                    style="
                                        white-space:pre-line;
                                        margin:0;
                                    "
                                >
                                    <?= sanitize($contactText) ?>
                                </p>

                            <?php endif; ?>


                            <?php if (
                                empty(trim($contactText))
                            ): ?>

                                <p
                                    style="
                                        color:var(--text-secondary);
                                        margin:0;
                                    "
                                >
                                    Contact information has not
                                    been added yet.
                                </p>

                            <?php endif; ?>

                        </div>



                    <!-- ================================================= -->
                    <!-- SKILLS -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'skills'): ?>


                        <?php

                        $skills = [];


                        if (
                            isset($content['skills']) &&
                            is_array($content['skills'])
                        ) {

                            $skills =
                                $content['skills'];

                        } elseif (
                            !empty($content['text'])
                        ) {

                            $skills =
                                preg_split(
                                    '/\r\n|\r|\n|,/',
                                    $content['text']
                                );
                        }

                        ?>


                        <div
                            style="
                                display:flex;
                                flex-wrap:wrap;
                                gap:0.5rem;
                            "
                        >

                            <?php foreach ($skills as $skill): ?>

                                <?php if (
                                    trim($skill) !== ''
                                ): ?>

                                    <span class="pf-pill">

                                        <?= sanitize(
                                            trim($skill)
                                        ) ?>

                                    </span>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>



                    <!-- ================================================= -->
                    <!-- EDUCATION -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'education'): ?>

                        <?php
                        $eduItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($eduItems as $item): ?>

                            <div class="pf-card">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['degree'])): ?>
                                        <p><strong>Degree:</strong> <?= sanitize($item['degree']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['institution'])): ?>
                                        <p><strong>Institution:</strong> <?= sanitize($item['institution']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['year'])): ?>
                                        <p><strong>Year:</strong> <?= sanitize($item['year']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['description'])): ?>
                                        <p style="white-space:pre-line;"><strong>Description:</strong><br><?= sanitize($item['description']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>
                                        <p style="white-space:pre-line; margin:0;"><?= sanitize($item['text']) ?></p>
                                    <?php endif; ?>

                                <?php else: ?>

                                    <p style="white-space:pre-line; margin:0;"><?= sanitize($item) ?></p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>



                    <!-- ================================================= -->
                    <!-- EXPERIENCE -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'experience'): ?>

                        <?php
                        $expItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($expItems as $item): ?>

                            <div class="pf-card">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['job_title'])): ?>
                                        <p><strong>Job Title:</strong> <?= sanitize($item['job_title']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['company'])): ?>
                                        <p><strong>Company:</strong> <?= sanitize($item['company']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['duration'])): ?>
                                        <p><strong>Duration:</strong> <?= sanitize($item['duration']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['description'])): ?>
                                        <p style="white-space:pre-line;"><strong>Description:</strong><br><?= sanitize($item['description']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>
                                        <p style="white-space:pre-line; margin:0;"><?= sanitize($item['text']) ?></p>
                                    <?php endif; ?>

                                <?php else: ?>

                                    <p style="white-space:pre-line; margin:0;"><?= sanitize($item) ?></p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>



                    <!-- ================================================= -->
                    <!-- PROJECTS -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'projects'): ?>

                        <?php
                        $projItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($projItems as $item): ?>

                            <div class="pf-card">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['project_name'])): ?>
                                        <p><strong>Project:</strong> <?= sanitize($item['project_name']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['technologies'])): ?>
                                        <p><strong>Technologies:</strong> <?= sanitize($item['technologies']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['description'])): ?>
                                        <p style="white-space:pre-line;"><strong>Description:</strong><br><?= sanitize($item['description']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['link'])): ?>
                                        <p><strong>Link:</strong> <a href="<?= sanitize($item['link']) ?>" target="_blank" rel="noopener noreferrer">View Project</a></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>
                                        <p style="white-space:pre-line; margin:0;"><?= sanitize($item['text']) ?></p>
                                    <?php endif; ?>

                                <?php else: ?>

                                    <p style="white-space:pre-line; margin:0;"><?= sanitize($item) ?></p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>



                    <!-- ================================================= -->
                    <!-- CERTIFICATIONS -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'certifications'): ?>

                        <?php
                        $certItems = isset($content[0]) && is_array($content) ? $content : [$content];
                        ?>

                        <?php foreach ($certItems as $item): ?>

                            <div class="pf-card">

                                <?php if (is_array($item)): ?>

                                    <?php if (!empty($item['name'])): ?>
                                        <p><strong>Certificate:</strong> <?= sanitize($item['name']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['issuer'])): ?>
                                        <p><strong>Issued By:</strong> <?= sanitize($item['issuer']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['year'])): ?>
                                        <p><strong>Year:</strong> <?= sanitize($item['year']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['text'])): ?>
                                        <p style="white-space:pre-line; margin:0;"><?= sanitize($item['text']) ?></p>
                                    <?php endif; ?>

                                <?php else: ?>

                                    <p style="white-space:pre-line; margin:0;"><?= sanitize($item) ?></p>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>



                    <!-- ================================================= -->
                    <!-- ACHIEVEMENTS -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'achievements'): ?>


                        <div class="pf-card">


                            <?php if (
                                !empty($content['title'])
                            ): ?>

                                <p>

                                    <strong>
                                        Achievement:
                                    </strong>

                                    <?= sanitize(
                                        $content['title']
                                    ) ?>

                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty($content['description'])
                            ): ?>

                                <p
                                    style="
                                        white-space:pre-line;
                                    "
                                >
                                    <?= sanitize(
                                        $content['description']
                                    ) ?>
                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty($content['text'])
                            ): ?>

                                <p
                                    style="
                                        white-space:pre-line;
                                        margin:0;
                                    "
                                >
                                    <?= sanitize(
                                        $content['text']
                                    ) ?>
                                </p>

                            <?php endif; ?>


                        </div>



                    <!-- ================================================= -->
                    <!-- LANGUAGES -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'languages'): ?>


                        <?php

                        $languages = [];


                        if (
                            isset($content['languages']) &&
                            is_array($content['languages'])
                        ) {

                            $languages =
                                $content['languages'];

                        } elseif (
                            !empty($content['text'])
                        ) {

                            $languages =
                                preg_split(
                                    '/\r\n|\r|\n|,/',
                                    $content['text']
                                );
                        }

                        ?>


                        <div
                            style="
                                display:flex;
                                flex-wrap:wrap;
                                gap:0.5rem;
                            "
                        >

                            <?php foreach (
                                $languages
                                as $language
                            ): ?>

                                <?php if (
                                    trim($language) !== ''
                                ): ?>

                                    <span class="pf-pill">

                                        <?= sanitize(
                                            trim($language)
                                        ) ?>

                                    </span>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>



                    <!-- ================================================= -->
                    <!-- ACTIVITIES -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'activities'): ?>


                        <div class="pf-card">


                            <?php if (
                                !empty($content['activity'])
                            ): ?>

                                <p>

                                    <strong>
                                        Activity:
                                    </strong>

                                    <?= sanitize(
                                        $content['activity']
                                    ) ?>

                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty($content['description'])
                            ): ?>

                                <p
                                    style="
                                        white-space:pre-line;
                                    "
                                >
                                    <?= sanitize(
                                        $content['description']
                                    ) ?>
                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty($content['text'])
                            ): ?>

                                <p
                                    style="
                                        white-space:pre-line;
                                        margin:0;
                                    "
                                >
                                    <?= sanitize(
                                        $content['text']
                                    ) ?>
                                </p>

                            <?php endif; ?>


                        </div>



                    <!-- ================================================= -->
                    <!-- INTERESTS -->
                    <!-- ================================================= -->

                    <?php elseif ($sectionType === 'interests'): ?>


                        <?php

                        $interests = [];


                        if (
                            isset($content['interests']) &&
                            is_array($content['interests'])
                        ) {

                            $interests =
                                $content['interests'];

                        } elseif (
                            !empty($content['text'])
                        ) {

                            $interests =
                                preg_split(
                                    '/\r\n|\r|\n|,/',
                                    $content['text']
                                );
                        }

                        ?>


                        <div
                            style="
                                display:flex;
                                flex-wrap:wrap;
                                gap:0.5rem;
                            "
                        >

                            <?php foreach (
                                $interests
                                as $interest
                            ): ?>

                                <?php if (
                                    trim($interest) !== ''
                                ): ?>

                                    <span class="pf-pill">

                                        <?= sanitize(
                                            trim($interest)
                                        ) ?>

                                    </span>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>



                    <!-- ================================================= -->
<!-- FALLBACK -->
<!-- ================================================= -->

<?php else: ?>

    <div class="pf-card">

        <?php if (!empty($content['text'])): ?>

            <p
                style="
                    white-space:pre-line;
                    margin:0;
                "
            >
                <?= sanitize($content['text']) ?>
            </p>

        <?php else: ?>

            <?php foreach ($content as $key => $value): ?>

                <?php
                    $label = ucwords(
                        str_replace(
                            '_',
                            ' ',
                            (string)$key
                        )
                    );
                ?>

                <?php if (is_array($value)): ?>

                    <p>
                        <strong>
                            <?= sanitize($label) ?>:
                        </strong>

                        <?= sanitize(
                            implode(
                                ', ',
                                array_map(
                                    'strval',
                                    $value
                                )
                            )
                        ) ?>
                    </p>

                <?php else: ?>

                    <p style="white-space:pre-line;">

                        <strong>
                            <?= sanitize($label) ?>:
                        </strong>

                        <?= sanitize((string)$value) ?>

                    </p>

                <?php endif; ?>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

<?php endif; ?>


                </section>


            <?php endforeach; ?>


        </main>


    </div>

</div>