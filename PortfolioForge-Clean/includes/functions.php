<?php
// includes/functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'user-' . time() : $text;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $typeClass = sanitize($flash['type']);
        $msg = sanitize($flash['message']);
        echo "<div class='alert alert-{$typeClass}'>{$msg}</div>";
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function uploadFile($file, $allowedMimes, $allowedExts, $maxSize, $targetDir) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file parameter.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'error' => 'File exceeds maximum upload size.'];
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'error' => 'No file was uploaded.'];
            default:
                return ['success' => false, 'error' => 'File upload error code: ' . $file['error']];
        }
    }

    if ($file['size'] > $maxSize) {
        $maxMb = round($maxSize / (1024 * 1024), 2);
        return ['success' => false, 'error' => "File size exceeds maximum allowed size of {$maxMb}MB."];
    }

    $finfo = new finfo(FILE_INFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, $allowedMimes)) {
        return ['success' => false, 'error' => "Invalid file type ({$mimeType})."];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        return ['success' => false, 'error' => "Invalid file extension (.{$ext})."];
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $newFilename = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = rtrim($targetDir, '/') . '/' . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }

    return [
        'success' => true,
        'filename' => $newFilename,
        'filepath' => $targetPath,
        'original_name' => $file['name']
    ];
}

function extractTextFromPDF($filepath) {
    if (!file_exists($filepath)) {
        return '';
    }

    // Attempt pdftotext execution
    $escapedPath = escapeshellarg($filepath);
    $cmd = "pdftotext -layout {$escapedPath} - 2>/dev/null";
    $output = shell_exec($cmd);

    if ($output !== null && trim($output) !== '') {
        return $output;
    }

    // Basic PHP raw extraction fallback for unencrypted PDFs if pdftotext yields empty
    $content = @file_get_contents($filepath);
    if (!$content) return '';

    preg_match_all('/\((.*?)\)\s*T[jJ]/s', $content, $matches);
    if (!empty($matches[1])) {
        return implode(" ", $matches[1]);
    }

    return '';
}

function parseResumeText($text) {
    $parsed = [
        'full_name' => '',
        'email' => '',
        'phone' => '',
        'location' => '',
        'summary' => '',
        'education' => [],
        'skills' => [],
        'projects' => [],
        'experience' => [],
        'certifications' => [],
        'achievements' => [],
        'languages' => [],
        'social_links' => []
    ];

    if (empty(trim($text))) {
        return $parsed;
    }

    // Extract Email
    if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
        $parsed['email'] = $matches[0];
    }

    // Extract Phone
    if (preg_match('/(\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', $text, $matches)) {
        $parsed['phone'] = $matches[0];
    }

    $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));

    // Attempt Name extraction (usually first non-empty line without email/phone/keywords)
    foreach ($lines as $line) {
        if (strlen($line) > 2 && strlen($line) < 50 && !str_contains($line, '@') && !preg_match('/\d{5,}/', $line)) {
            $parsed['full_name'] = preg_replace('/[^a-zA-Z\s.]/', '', $line);
            break;
        }
    }

    // Section parsing based on common headings
    $currentSection = null;
    $buffer = [];

    $sectionKeywords = [
        'summary' => ['summary', 'about', 'profile', 'objective'],
        'education' => ['education', 'academic', 'qualification', 'qualifications'],
        'skills' => ['skills', 'technical skills', 'core competencies', 'expertise'],
        'experience' => ['experience', 'work experience', 'employment', 'work history'],
        'projects' => ['projects', 'key projects', 'personal projects'],
        'certifications' => ['certifications', 'certificates', 'courses'],
        'achievements' => ['achievements', 'awards', 'honors'],
        'languages' => ['languages', 'language proficiency'],
    ];

    foreach ($lines as $line) {
        $lowerLine = strtolower($line);
        $foundKeywordSection = null;

        foreach ($sectionKeywords as $sec => $keywords) {
            foreach ($keywords as $kw) {
                if ($lowerLine === $kw || str_starts_with($lowerLine, $kw . ':')) {
                    $foundKeywordSection = $sec;
                    break 2;
                }
            }
        }

        if ($foundKeywordSection) {
            if ($currentSection && !empty($buffer)) {
                processSectionBuffer($currentSection, $buffer, $parsed);
                $buffer = [];
            }
            $currentSection = $foundKeywordSection;
        } elseif ($currentSection) {
            $buffer[] = $line;
        } elseif (empty($parsed['summary']) && strlen($line) > 20) {
            $buffer[] = $line;
            $currentSection = 'summary';
        }
    }

    if ($currentSection && !empty($buffer)) {
        processSectionBuffer($currentSection, $buffer, $parsed);
    }

    return $parsed;
}

function processSectionBuffer($section, $buffer, &$parsed) {
    $text = implode("\n", $buffer);
    if ($section === 'summary') {
        $parsed['summary'] = trim($text);
    } elseif ($section === 'skills') {
        // Split by commas, bullets, or newlines
        $items = preg_split('/[,ΓÇó|\n]+/', $text);
        foreach ($items as $item) {
            $clean = trim(preg_replace('/^[-ΓÇó*\s]+/', '', $item));
            if (!empty($clean) && strlen($clean) < 50) {
                $parsed['skills'][] = $clean;
            }
        }
    } elseif (in_array($section, ['education', 'experience', 'projects', 'certifications', 'achievements', 'languages'])) {
        $parsed[$section][] = trim($text);
    }
}

function getOrCreateUserPortfolio($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM portfolios WHERE user_id = ?");
    $stmt->execute([$userId]);
    $portfolio = $stmt->fetch();

    if (!$portfolio) {
        // Fetch user full name for default slug
        $uStmt = $pdo->prepare("SELECT full_name, username FROM users WHERE user_id = ?");
        $uStmt->execute([$userId]);
        $user = $uStmt->fetch();

        $baseSlug = generateSlug($user['username'] ?? $user['full_name'] ?? 'portfolio');
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $checkStmt = $pdo->prepare("SELECT portfolio_id FROM portfolios WHERE portfolio_slug = ?");
            $checkStmt->execute([$slug]);
            if (!$checkStmt->fetch()) break;
            $slug = $baseSlug . '-' . $counter++;
        }

        $insStmt = $pdo->prepare("INSERT INTO portfolios (user_id, template_id, title, portfolio_slug, status) VALUES (?, 1, ?, ?, 'draft')");
        $insStmt->execute([$userId, ($user['full_name'] ?? 'My') . "'s Portfolio", $slug]);

        $portfolioId = $pdo->lastInsertId();

        // Create default core sections
        $defaultSections = [
            ['type' => 'about', 'title' => 'About Me', 'order' => 1],
            ['type' => 'education', 'title' => 'Education', 'order' => 2],
            ['type' => 'skills', 'title' => 'Skills', 'order' => 3],
            ['type' => 'projects', 'title' => 'Projects', 'order' => 4],
            ['type' => 'experience', 'title' => 'Work Experience', 'order' => 5],
            ['type' => 'contact', 'title' => 'Contact Me', 'order' => 6]
        ];

        $secStmt = $pdo->prepare("INSERT INTO portfolio_sections (portfolio_id, section_type, title, content, display_order, is_visible) VALUES (?, ?, ?, ?, ?, 1)");
        foreach ($defaultSections as $ds) {
            $secStmt->execute([$portfolioId, $ds['type'], $ds['title'], json_encode([]), $ds['order']]);
        }

        $stmt->execute([$userId]);
        $portfolio = $stmt->fetch();
    }

    return $portfolio;
}

