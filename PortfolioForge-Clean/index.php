<?php
// index.php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM templates WHERE is_active = 1");
$templates = $stmt->fetchAll();

$pageTitle = 'Portfolio Forge - Build Your Professional Portfolio';
require_once __DIR__ . '/includes/header.php';
?>

<div class="landing-hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 5rem 1.5rem; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1.2rem; line-height: 1.2;">Build Your Professional Portfolio</h1>
        <p style="font-size: 1.25rem; color: #94a3b8; margin-bottom: 2.5rem; line-height: 1.6;">Create, customize, and share your professional portfolio without writing code.</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="/PortfolioForge-Clean/register.php" class="nav-btn btn-primary" style="padding: 0.9rem 2rem; font-size: 1.1rem;">Create Your Portfolio</a>
            <a href="#templates" class="nav-btn btn-outline" style="padding: 0.9rem 2rem; font-size: 1.1rem; color: #fff !important; border-color: #475569;">Explore Templates</a>
        </div>
    </div>
</div>

<section id="features" style="max-width: 1200px; margin: 5rem auto; padding: 0 1.5rem;">
    <div style="text-align: center; margin-bottom: 3.5rem;">
        <h2 style="font-size: 2.2rem; color: var(--secondary-color); margin-bottom: 0.5rem;">Powerful Features</h2>
        <p style="color: var(--text-secondary); font-size: 1.1rem;">Everything you need to showcase your talent and accomplishments.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
        <div style="background: #fff; padding: 2rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">📄</div>
            <h3 style="margin-bottom: 0.75rem; color: var(--secondary-color);">Resume-Based Portfolio Creation</h3>
            <p style="color: var(--text-secondary);">Upload your PDF resume to automatically extract text and pre-fill your portfolio sections instantly.</p>
        </div>

        <div style="background: #fff; padding: 2rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">✏️</div>
            <h3 style="margin-bottom: 0.75rem; color: var(--secondary-color);">Fully Editable Portfolio Information</h3>
            <p style="color: var(--text-secondary);">Maintain complete control over all data. Edit, add missing information, or delete inaccuracies at any time.</p>
        </div>

        <div style="background: #fff; padding: 2rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎨</div>
            <h3 style="margin-bottom: 0.75rem; color: var(--secondary-color);">Customizable Templates</h3>
            <p style="color: var(--text-secondary);">Choose from five general-purpose design styles. Switch templates seamlessly without losing any content.</p>
        </div>

        <div style="background: #fff; padding: 2rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">📑</div>
            <h3 style="margin-bottom: 0.75rem; color: var(--secondary-color);">Flexible Portfolio Sections</h3>
            <p style="color: var(--text-secondary);">Add core sections like Experience & Projects, plus optional sections like Certifications, Achievements & Languages.</p>
        </div>

        <div style="background: #fff; padding: 2rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">🔗</div>
            <h3 style="margin-bottom: 0.75rem; color: var(--secondary-color);">Personalized Portfolio URL</h3>
            <p style="color: var(--text-secondary);">Get a custom, clean slug link to share your portfolio publicly with recruiters, peers, and visitors.</p>
        </div>

        <div style="background: #fff; padding: 2rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">📊</div>
            <h3 style="margin-bottom: 0.75rem; color: var(--secondary-color);">Portfolio View Statistics</h3>
            <p style="color: var(--text-secondary);">Track public interest with view counting metrics showing overall visits, views today, and views this week.</p>
        </div>
    </div>
</section>

<section id="how-it-works" style="background-color: #f1f5f9; padding: 5rem 1.5rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 3.5rem;">
            <h2 style="font-size: 2.2rem; color: var(--secondary-color); margin-bottom: 0.5rem;">How It Works</h2>
            <p style="color: var(--text-secondary); font-size: 1.1rem;">A simple, streamlined process to go live in minutes.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <div style="background: #fff; padding: 1.5rem; border-radius: var(--radius-sm); border-left: 4px solid var(--primary-color);">
                <div style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 0.5rem;">Step 1</div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Create an account</h4>
                <p style="font-size: 0.95rem; color: var(--text-secondary);">Register with your name, unique username, email, and password.</p>
            </div>

            <div style="background: #fff; padding: 1.5rem; border-radius: var(--radius-sm); border-left: 4px solid var(--primary-color);">
                <div style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 0.5rem;">Step 2</div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Enter information or upload resume</h4>
                <p style="font-size: 0.95rem; color: var(--text-secondary);">Start from scratch manually or upload a PDF resume for pre-filled data extraction.</p>
            </div>

            <div style="background: #fff; padding: 1.5rem; border-radius: var(--radius-sm); border-left: 4px solid var(--primary-color);">
                <div style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 0.5rem;">Step 3</div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Review and edit information</h4>
                <p style="font-size: 0.95rem; color: var(--text-secondary);">Fine-tune your content, correct extraction errors, and add missing achievements.</p>
            </div>

            <div style="background: #fff; padding: 1.5rem; border-radius: var(--radius-sm); border-left: 4px solid var(--primary-color);">
                <div style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 0.5rem;">Step 4</div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Select a template</h4>
                <p style="font-size: 0.95rem; color: var(--text-secondary);">Pick between Modern, Minimal, Professional, Creative, and Classic designs.</p>
            </div>

            <div style="background: #fff; padding: 1.5rem; border-radius: var(--radius-sm); border-left: 4px solid var(--primary-color);">
                <div style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 0.5rem;">Step 5</div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Customize and preview</h4>
                <p style="font-size: 0.95rem; color: var(--text-secondary);">Adjust accent colors, fonts, and section arrangements, then preview your layout.</p>
            </div>

            <div style="background: #fff; padding: 1.5rem; border-radius: var(--radius-sm); border-left: 4px solid var(--primary-color);">
                <div style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 0.5rem;">Step 6</div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Publish and share</h4>
                <p style="font-size: 0.95rem; color: var(--text-secondary);">Make your portfolio live and share your personalized slug link with the world.</p>
            </div>
        </div>
    </div>
</section>

<section id="templates" class="templates-section">

    <div class="templates-heading">
        <h2>Portfolio Templates</h2>
        <p>Choose a design that fits your professional style.</p>
    </div>

    <div class="template-preview-grid">

        <!-- MODERN -->
        <div class="template-preview-card">
            <div class="template-preview preview-modern">

                <div class="modern-header">
                    <div class="preview-avatar"></div>
                    <div>
                        <div class="preview-name">Alex Johnson</div>
                        <div class="preview-job">Web Developer</div>
                        <div class="preview-contact">alex@email.com</div>
                    </div>
                </div>

                <div class="modern-section">
                    <h4>ABOUT</h4>
                    <div class="preview-line"></div>
                    <div class="preview-text"></div>
                    <div class="preview-text short"></div>
                </div>

                <div class="modern-cards">
                    <div>
                        <strong>PROJECTS</strong>
                        <span></span>
                        <span></span>
                    </div>

                    <div>
                        <strong>SKILLS</strong>
                        <span></span>
                        <span></span>
                    </div>
                </div>

            </div>

            <div class="template-info">
                <h3>Modern</h3>
                <p>Contemporary layout with strong visual hierarchy and modern content cards.</p>
                <a href="/PortfolioForge-Clean/register.php" class="nav-btn btn-primary">Use This Template</a>
            </div>
        </div>


        <!-- MINIMAL -->
        <div class="template-preview-card">
            <div class="template-preview preview-minimal">

                <div class="minimal-header">
                    <div class="preview-name">Alex Johnson</div>
                    <div class="preview-job">Web Developer</div>
                    <div class="minimal-contact">alex@email.com</div>
                </div>

                <div class="minimal-section">
                    <h4>ABOUT</h4>
                    <div class="minimal-line"></div>
                    <div class="preview-text"></div>
                    <div class="preview-text"></div>
                    <div class="preview-text short"></div>
                </div>

                <div class="minimal-section">
                    <h4>EXPERIENCE</h4>
                    <div class="minimal-line"></div>

                    <div class="minimal-entry">
                        <strong>Web Developer</strong>
                        <span>2024 — Present</span>
                    </div>

                    <div class="preview-text"></div>
                    <div class="preview-text short"></div>
                </div>

                <div class="minimal-skills">
                    <span>PHP</span>
                    <span>JavaScript</span>
                    <span>MySQL</span>
                </div>

            </div>

            <div class="template-info">
                <h3>Minimal</h3>
                <p>Clean typography, generous whitespace, subtle dividers, and distraction-free content.</p>
                <a href="/PortfolioForge-Clean/register.php" class="nav-btn btn-primary">Use This Template</a>
            </div>
        </div>


        <!-- PROFESSIONAL -->
        <div class="template-preview-card">
            <div class="template-preview preview-professional">

                <div class="professional-header">
                    <div class="preview-avatar"></div>

                    <div>
                        <div class="preview-name">Alex Johnson</div>
                        <div class="preview-job">Software Engineer</div>
                        <div class="preview-contact">
                            Email: alex@email.com
                        </div>
                    </div>
                </div>

                <div class="professional-section">
                    <h4>ABOUT ME</h4>

                    <div class="professional-card">
                        <div class="preview-text"></div>
                        <div class="preview-text short"></div>
                    </div>
                </div>

                <div class="professional-section">
                    <h4>EXPERIENCE</h4>

                    <div class="professional-card">
                        <strong>Software Engineer</strong>
                        <div class="preview-text"></div>
                        <div class="preview-text short"></div>
                    </div>
                </div>

                <div class="professional-skills">
                    <span>PHP</span>
                    <span>MySQL</span>
                    <span>Java</span>
                </div>

            </div>

            <div class="template-info">
                <h3>Professional</h3>
                <p>Structured layout with uppercase headings, accent lines, and professional content cards.</p>
                <a href="/PortfolioForge-Clean/register.php" class="nav-btn btn-primary">Use This Template</a>
            </div>
        </div>


        <!-- CREATIVE -->
        <div class="template-preview-card">
            <div class="template-preview preview-creative">

                <div class="creative-header">

                    <div class="creative-avatar"></div>

                    <div>
                        <div class="creative-name">Alex Johnson</div>
                        <div class="preview-job">Creative Developer</div>
                    </div>

                </div>

                <div class="creative-section">
                    <h4>✦ ABOUT ME</h4>

                    <div class="creative-card">
                        <div class="preview-text"></div>
                        <div class="preview-text short"></div>
                    </div>
                </div>

                <div class="creative-section">
                    <h4>✦ SKILLS</h4>

                    <div class="creative-pills">
                        <span>PHP</span>
                        <span>JavaScript</span>
                        <span>UI Design</span>
                    </div>
                </div>

                <div class="creative-section">
                    <h4>✦ PROJECTS</h4>

                    <div class="creative-projects">
                        <div></div>
                        <div></div>
                    </div>
                </div>

            </div>

            <div class="template-info">
                <h3>Creative</h3>
                <p>Expressive design with colorful headings, filled skill pills, and accent-topped cards.</p>
                <a href="/PortfolioForge-Clean/register.php" class="nav-btn btn-primary">Use This Template</a>
            </div>
        </div>


        <!-- CLASSIC -->
        <div class="template-preview-card">
            <div class="template-preview preview-classic">

                <div class="classic-header">

                    <div class="preview-avatar classic-avatar"></div>

                    <div>
                        <div class="classic-name">Alex Johnson</div>
                        <div class="classic-job">
                            Software Developer
                        </div>
                        <div class="classic-contact">
                            Email: alex@email.com
                        </div>
                    </div>

                </div>

                <div class="classic-section">

                    <h4>ABOUT ME</h4>

                    <div class="preview-text"></div>
                    <div class="preview-text"></div>
                    <div class="preview-text short"></div>

                </div>

                <div class="classic-section">

                    <h4>EXPERIENCE</h4>

                    <div class="classic-entry">
                        <strong>Software Developer</strong>
                        <span>2024 — Present</span>
                    </div>

                    <div class="preview-text"></div>
                    <div class="preview-text short"></div>

                </div>

                <div class="classic-section">

                    <h4>SKILLS</h4>

                    <div class="classic-skills">
                        PHP • JavaScript • MySQL • Java
                    </div>

                </div>

            </div>

            <div class="template-info">
                <h3>Classic</h3>
                <p>Traditional CV-inspired design with serif typography and formal section styling.</p>
                <a href="/PortfolioForge-Clean/register.php" class="nav-btn btn-primary">Use This Template</a>
            </div>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>