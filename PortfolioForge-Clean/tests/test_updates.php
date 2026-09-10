<?php
// tests/test_updates.php

require_once __DIR__ . '/../includes/functions.php';

echo "=== PORTFOLIO FORGE EXTENDED VERIFICATION TESTS ===\n\n";

$passed = 0;
$failed = 0;

function assertTest($condition, $testName) {
    global $passed, $failed;

    if ($condition) {
        echo "PASS: {$testName}\n";
        $passed++;
    } else {
        echo "FAIL: {$testName}\n";
        $failed++;
    }
}

/* Test 1: Resume parsing with multi-line and bullet sections */
$sampleText = "
John Smith
Email: john.smith@example.com
Phone: +977 9812345678

SUMMARY
Creative web developer with 3 years of experience in PHP and MySQL.

TECHNICAL SKILLS
PHP, MySQL, HTML5, CSS3, JavaScript, Git

WORK EXPERIENCE
Frontend Developer at Acme Corp (2022 - Present)
Built interactive user interfaces.

Junior Developer at Tech House
Assisted in back-end maintenance.

EDUCATION
Bachelor in Computer Application - Tribhuvan University (2018 - 2022)

CERTIFICATIONS
- Certified Web Developer (2023)
- MySQL Database Specialist (2024)

LANGUAGES
English
Nepali
";

$parsed = parseResumeText($sampleText);

assertTest($parsed['email'] === 'john.smith@example.com', 'Parsed email correctly');
assertTest($parsed['phone'] === '+977 9812345678', 'Parsed phone correctly');
assertTest(count($parsed['skills']) >= 5, 'Parsed skills list correctly');
assertTest(count($parsed['experience']) === 2, 'Parsed 2 experience entries');
assertTest(count($parsed['certifications']) === 2, 'Parsed 2 certification items');
assertTest($parsed['certifications'][0] === 'Certified Web Developer (2023)', 'Cleaned certification bullet text');

/* Test 2: Creative template view rendering logic check */
$testSections = [
    [
        'section_id' => 1,
        'section_type' => 'certifications',
        'title' => 'Certifications',
        'is_visible' => 1,
        'content' => json_encode(['Certified Web Developer (2023)', 'MySQL Specialist (2024)'])
    ],
    [
        'section_id' => 2,
        'section_type' => 'experience',
        'title' => 'Work Experience',
        'is_visible' => 1,
        'content' => json_encode([
            [
                'job_title' => 'Frontend Developer',
                'company' => 'Acme Corp',
                'duration' => '2022-Present',
                'description' => 'Built interfaces'
            ]
        ])
    ]
];

$portfolio = [
    'accent_color' => '#6c5ce7',
    'font_family' => 'Poppins, sans-serif',
    'show_profile_image' => 0,
    'show_email' => 1,
    'title' => 'Software Engineer Portfolio'
];
$user = [
    'full_name' => 'John Smith',
    'email' => 'john.smith@example.com'
];
$resume = [
    'public_download_enabled' => 1,
    'file_path' => '/uploads/resumes/resume_123.docx'
];

ob_start();
$sections = $testSections;
include __DIR__ . '/../templates/creative/view.php';
$creativeOutput = ob_get_clean();

assertTest(str_contains($creativeOutput, 'Certified Web Developer (2023)'), 'Creative template renders certification item 1');
assertTest(str_contains($creativeOutput, 'MySQL Specialist (2024)'), 'Creative template renders certification item 2');
assertTest(str_contains($creativeOutput, 'Frontend Developer'), 'Creative template renders experience job title');
assertTest(str_contains($creativeOutput, 'Download Resume / CV'), 'Creative template renders CV download button');

ob_start();
$sections = $testSections;
include __DIR__ . '/../templates/minimal/view.php';
$minimalOutput = ob_get_clean();

assertTest(str_contains($minimalOutput, 'Certified Web Developer (2023)'), 'Minimal template renders certification item 1');
assertTest(str_contains($minimalOutput, 'Frontend Developer'), 'Minimal template renders experience job title');
assertTest(str_contains($minimalOutput, 'Download CV / Resume'), 'Minimal template renders CV download button');

ob_start();
$sections = $testSections;
include __DIR__ . '/../templates/classic/view.php';
$classicOutput = ob_get_clean();

assertTest(str_contains($classicOutput, 'Certified Web Developer (2023)'), 'Classic template renders certification item 1');
assertTest(str_contains($classicOutput, 'Frontend Developer'), 'Classic template renders experience job title');
assertTest(str_contains($classicOutput, 'Download CV'), 'Classic template renders CV download button');

ob_start();
$sections = $testSections;
include __DIR__ . '/../templates/modern/view.php';
$modernOutput = ob_get_clean();

assertTest(str_contains($modernOutput, 'Certified Web Developer (2023)'), 'Modern template renders certification item 1');
assertTest(str_contains($modernOutput, 'Frontend Developer'), 'Modern template renders experience job title');
assertTest(str_contains($modernOutput, 'Download Resume'), 'Modern template renders CV download button');

ob_start();
$sections = $testSections;
include __DIR__ . '/../templates/professional/view.php';
$profOutput = ob_get_clean();

assertTest(str_contains($profOutput, 'Certified Web Developer (2023)'), 'Professional template renders certification item 1');
assertTest(str_contains($profOutput, 'Frontend Developer'), 'Professional template renders experience job title');
assertTest(str_contains($profOutput, 'Download Resume'), 'Professional template renders CV download button');

echo "\n=== SUMMARY ===\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n\n";

if ($failed === 0) {
    echo "ALL TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "TESTS FAILED!\n";
    exit(1);
}
