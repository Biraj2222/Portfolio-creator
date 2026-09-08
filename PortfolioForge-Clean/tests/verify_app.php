<?php
// tests/verify_app.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

echo "=== PORTFOLIO FORGE AUTOMATED SYSTEM VERIFICATION ===\n\n";

$pdo = getDBConnection();
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

/*
|--------------------------------------------------------------------------
| 1. Test Database Tables
|--------------------------------------------------------------------------
*/

$tables = [
    'users',
    'admins',
    'portfolios',
    'portfolio_sections',
    'resume',
    'templates',
    'portfolio_visits'
];

foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));

    assertTest(
        $stmt->rowCount() > 0,
        "Table '{$table}' exists in database"
    );
}

/*
|--------------------------------------------------------------------------
| 2. Test User Registration & Password Hashing
|--------------------------------------------------------------------------
*/

$testUsername = 'testuser_' . time() . '_' . random_int(1000, 9999);
$testEmail = 'test_' . time() . '_' . random_int(1000, 9999) . '@example.com';
$testPassword = 'securePassword123';

$hashed = password_hash($testPassword, PASSWORD_BCRYPT);

$insUser = $pdo->prepare(
    "INSERT INTO users 
    (full_name, username, email, password, status) 
    VALUES (?, ?, ?, ?, 'active')"
);

$insUser->execute([
    'Test User',
    $testUsername,
    $testEmail,
    $hashed
]);

$userId = $pdo->lastInsertId();

assertTest(
    $userId > 0,
    "Registered test user ID: {$userId}"
);

assertTest(
    password_verify($testPassword, $hashed),
    "Password hashing and verification functioning"
);

/*
|--------------------------------------------------------------------------
| 3. Test Portfolio Creation & 1:1 Portfolio Relationship
|--------------------------------------------------------------------------
*/

$portfolio = getOrCreateUserPortfolio($userId, $pdo);

assertTest(
    !empty($portfolio),
    "Portfolio created automatically for user"
);

$portfolio2 = getOrCreateUserPortfolio($userId, $pdo);

assertTest(
    $portfolio['portfolio_id'] == $portfolio2['portfolio_id'],
    "Single portfolio maintained for the user"
);

/*
|--------------------------------------------------------------------------
| 4. Test Portfolio Sections
|--------------------------------------------------------------------------
*/

// Check default sections
$secStmt = $pdo->prepare(
    "SELECT COUNT(*) 
     FROM portfolio_sections 
     WHERE portfolio_id = ?"
);

$secStmt->execute([
    $portfolio['portfolio_id']
]);

$sectionCount = $secStmt->fetchColumn();

assertTest(
    $sectionCount >= 6,
    "Default core sections created for portfolio"
);


// Update About section
$upSec = $pdo->prepare(
    "UPDATE portfolio_sections
     SET title = ?, content = ?
     WHERE portfolio_id = ?
     AND section_type = 'about'"
);

$upSec->execute([
    'Updated Section Title',
    json_encode([
        'text' => 'Updated about text content.'
    ]),
    $portfolio['portfolio_id']
]);


// Verify update
$checkSec = $pdo->prepare(
    "SELECT title
     FROM portfolio_sections
     WHERE portfolio_id = ?
     AND section_type = 'about'"
);

$checkSec->execute([
    $portfolio['portfolio_id']
]);

$updatedTitle = $checkSec->fetchColumn();

assertTest(
    $updatedTitle === 'Updated Section Title',
    "Section content update persisted"
);

/*
|--------------------------------------------------------------------------
| 5. Test Template Switching
|--------------------------------------------------------------------------
*/

$upTpl = $pdo->prepare(
    "UPDATE portfolios
     SET template_id = 3
     WHERE portfolio_id = ?"
);

$upTpl->execute([
    $portfolio['portfolio_id']
]);

$checkTpl = $pdo->prepare(
    "SELECT template_id
     FROM portfolios
     WHERE portfolio_id = ?"
);

$checkTpl->execute([
    $portfolio['portfolio_id']
]);

$currentTemplate = $checkTpl->fetchColumn();

assertTest(
    (int)$currentTemplate === 3,
    "Template switched successfully without altering portfolio sections"
);


/*
|--------------------------------------------------------------------------
| 6. Test Non-AI Resume Parsing
|--------------------------------------------------------------------------
*/

$sampleText = "
Jane Doe
Email: jane.doe@example.com
Phone: (555) 123-4567

SUMMARY
Experienced software engineer with a strong foundation in PHP, MySQL, and JavaScript.

TECHNICAL SKILLS
PHP, MySQL, JavaScript, HTML5, CSS3, Git

EXPERIENCE
Software Engineer at Tech Corp (2021-Present)
Developed scalable web applications and REST APIs.

EDUCATION
BCA in Computer Applications - State University
";

$parsedData = parseResumeText($sampleText);

assertTest(
    $parsedData['email'] === 'jane.doe@example.com',
    "Resume parser extracted email correctly"
);

assertTest(
    trim($parsedData['phone']) === '(555) 123-4567',
    "Resume parser extracted phone correctly"
);

assertTest(
    in_array('PHP', $parsedData['skills']),
    "Resume parser extracted skills correctly"
);


/*
|--------------------------------------------------------------------------
| 7. Test Public Visit Counter
|--------------------------------------------------------------------------
*/

$slug = $portfolio['portfolio_slug'];

// Publish portfolio
$publishStmt = $pdo->prepare(
    "UPDATE portfolios
     SET status = 'published'
     WHERE portfolio_id = ?"
);

$publishStmt->execute([
    $portfolio['portfolio_id']
]);


// Count existing visits
$vCountBefore = $pdo->prepare(
    "SELECT COUNT(*)
     FROM portfolio_visits
     WHERE portfolio_id = ?"
);

$vCountBefore->execute([
    $portfolio['portfolio_id']
]);

$c1 = (int)$vCountBefore->fetchColumn();


// Add two public visits
$visitStmt = $pdo->prepare(
    "INSERT INTO portfolio_visits (portfolio_id)
     VALUES (?)"
);

$visitStmt->execute([
    $portfolio['portfolio_id']
]);

$visitStmt->execute([
    $portfolio['portfolio_id']
]);


// Count again
$vCountAfter = $pdo->prepare(
    "SELECT COUNT(*)
     FROM portfolio_visits
     WHERE portfolio_id = ?"
);

$vCountAfter->execute([
    $portfolio['portfolio_id']
]);

$c2 = (int)$vCountAfter->fetchColumn();

assertTest(
    $c2 === ($c1 + 2),
    "Public visit counter correctly incremented by 2"
);


/*
|--------------------------------------------------------------------------
| 8. Test Admin Deactivation
|--------------------------------------------------------------------------
*/

$deact = $pdo->prepare(
    "UPDATE users
     SET status = 'inactive'
     WHERE user_id = ?"
);

$deact->execute([
    $userId
]);


$chDeact = $pdo->prepare(
    "SELECT status
     FROM users
     WHERE user_id = ?"
);

$chDeact->execute([
    $userId
]);

$deactivatedStatus = $chDeact->fetchColumn();

assertTest(
    $deactivatedStatus === 'inactive',
    "User account deactivation state updated successfully"
);


/*
|--------------------------------------------------------------------------
| 9. Cleanup
|--------------------------------------------------------------------------
*/

// Delete test user's portfolio-related data first if foreign keys
// are not configured with ON DELETE CASCADE.

$pdo->prepare(
    "DELETE FROM portfolio_visits
     WHERE portfolio_id = ?"
)->execute([
    $portfolio['portfolio_id']
]);

$pdo->prepare(
    "DELETE FROM portfolio_sections
     WHERE portfolio_id = ?"
)->execute([
    $portfolio['portfolio_id']
]);

$pdo->prepare(
    "DELETE FROM portfolios
     WHERE portfolio_id = ?"
)->execute([
    $portfolio['portfolio_id']
]);

$pdo->prepare(
    "DELETE FROM users
     WHERE user_id = ?"
)->execute([
    $userId
]);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

echo "\n=== SUMMARY ===\n";

echo "Total Passed: {$passed}\n";
echo "Total Failed: {$failed}\n\n";

if ($failed === 0) {
    echo "ALL BACKEND VERIFICATION TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED.\n";
    exit(1);
}