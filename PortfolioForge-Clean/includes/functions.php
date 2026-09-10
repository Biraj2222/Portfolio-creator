<?php
require_once __DIR__ . '/../config/database.php';
// includes/functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| SANITIZE
|--------------------------------------------------------------------------
*/

function sanitize($data)
{
    if (is_array($data)) {
        return '';
    }

    return htmlspecialchars(
        trim((string)($data ?? '')),
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| SLUG
|--------------------------------------------------------------------------
*/

function generateSlug($text)
{
    $text = trim((string)$text);

    $text = preg_replace(
        '~[^\pL\d]+~u',
        '-',
        $text
    );

    $converted = @iconv(
        'utf-8',
        'us-ascii//TRANSLIT',
        $text
    );

    if ($converted !== false) {
        $text = $converted;
    }

    $text = preg_replace(
        '~[^-\w]+~',
        '',
        $text
    );

    $text = trim($text, '-');

    $text = preg_replace(
        '~-+~',
        '-',
        $text
    );

    $text = strtolower($text);

    return empty($text)
        ? 'user-' . time()
        : $text;
}


/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}


function getFlash()
{
    if (isset($_SESSION['flash'])) {

        $flash = $_SESSION['flash'];

        unset($_SESSION['flash']);

        return $flash;
    }

    return null;
}


function displayFlash()
{
    $flash = getFlash();

    if ($flash) {

        $typeClass = sanitize($flash['type']);
        $msg = sanitize($flash['message']);

        echo "<div class='alert alert-{$typeClass}'>{$msg}</div>";
    }
}


/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


function verifyCsrfToken($token)
{
    return (
        isset($_SESSION['csrf_token']) &&
        hash_equals(
            $_SESSION['csrf_token'],
            $token ?? ''
        )
    );
}


/*
|--------------------------------------------------------------------------
| FILE UPLOAD
|--------------------------------------------------------------------------
*/

function uploadFile(
    $file,
    $allowedMimes,
    $allowedExts,
    $maxSize,
    $targetDir,
    $customFilename = null
){

    if (
        !isset($file['error']) ||
        is_array($file['error'])
    ) {

        return [
            'success' => false,
            'error' => 'Invalid file parameter.'
        ];
    }


    if ($file['error'] !== UPLOAD_ERR_OK) {

        switch ($file['error']) {

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:

                return [
                    'success' => false,
                    'error' => 'File exceeds maximum upload size.'
                ];

            case UPLOAD_ERR_NO_FILE:

                return [
                    'success' => false,
                    'error' => 'No file was uploaded.'
                ];

            default:

                return [
                    'success' => false,
                    'error' =>
                    'File upload error code: '
                        . $file['error']
                ];
        }
    }


    if (
        !isset($file['size']) ||
        $file['size'] <= 0
    ) {

        return [
            'success' => false,
            'error' => 'The uploaded file is empty.'
        ];
    }


    if ($file['size'] > $maxSize) {

        $maxMb = round(
            $maxSize / (1024 * 1024),
            2
        );

        return [
            'success' => false,
            'error' =>
            "File size exceeds maximum allowed size of {$maxMb}MB."
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MIME CHECK
    |--------------------------------------------------------------------------
    */

    if (!function_exists('finfo_open')) {

        return [
            'success' => false,
            'error' =>
            'PHP Fileinfo extension is not enabled.'
        ];
    }


    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    if (!$finfo) {

        return [
            'success' => false,
            'error' =>
            'Unable to inspect uploaded file.'
        ];
    }


    $mimeType = finfo_file(
        $finfo,
        $file['tmp_name']
    );

    finfo_close($finfo);


    $validMime = in_array(
        $mimeType,
        $allowedMimes,
        true
    );


    if (!$validMime) {

        return [
            'success' => false,
            'error' =>
            "Invalid file type ({$mimeType}). Please upload a DOCX file."
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | EXTENSION CHECK
    |--------------------------------------------------------------------------
    */

    $ext = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );


    if (!in_array(
        $ext,
        $allowedExts,
        true
    )) {

        return [
            'success' => false,
            'error' =>
            "Invalid file extension (.{$ext}). Please upload a DOCX file."
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE DIRECTORY
    |--------------------------------------------------------------------------
    */

    if (!is_dir($targetDir)) {

        if (!mkdir(
            $targetDir,
            0755,
            true
        )) {

            return [
                'success' => false,
                'error' =>
                'Unable to create upload directory.'
            ];
        }
    }


  /*
|--------------------------------------------------------------------------
| FILE NAME
|--------------------------------------------------------------------------
*/

if ($customFilename !== null) {

    $newFilename =
        $customFilename
        . '.'
        . $ext;

} else {

    // Keep random filename for other uploads
    try {

        $randomName =
            bin2hex(
                random_bytes(16)
            );

    } catch (Throwable $e) {

        $randomName =
            uniqid('', true);
    }

    $newFilename =
        $randomName
        . '.'
        . $ext;
}
    $targetPath =
        rtrim(
            $targetDir,
            '/\\'
        )
        . DIRECTORY_SEPARATOR
        . $newFilename;


    /*
    |--------------------------------------------------------------------------
    | MOVE FILE
    |--------------------------------------------------------------------------
    */

    if (!move_uploaded_file(
        $file['tmp_name'],
        $targetPath
    )) {

        return [
            'success' => false,
            'error' =>
            'Failed to move uploaded file.'
        ];
    }


    return [
        'success' => true,
        'filename' => $newFilename,
        'filepath' => $targetPath,
        'original_name' => $file['name']
    ];
}


/*
|--------------------------------------------------------------------------
| DOCX TEXT EXTRACTION
|--------------------------------------------------------------------------
|
| DOCX files are ZIP archives containing XML.
|
| This version preserves empty paragraphs so that
| separate resume entries can be detected later.
|
|--------------------------------------------------------------------------
*/

function extractTextFromDOCX($filepath)
{
    if (
        empty($filepath) ||
        !file_exists($filepath)
    ) {
        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | ZIPARCHIVE REQUIRED
    |--------------------------------------------------------------------------
    */

    if (!class_exists('ZipArchive')) {

        return '';
    }


    $zip = new ZipArchive();

    $opened = $zip->open($filepath);

    if ($opened !== true) {

        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | MAIN DOCUMENT
    |--------------------------------------------------------------------------
    */

    $xml = $zip->getFromName(
        'word/document.xml'
    );


    $zip->close();


    if (
        $xml === false ||
        trim($xml) === ''
    ) {

        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD XML SAFELY
    |--------------------------------------------------------------------------
    */

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();

    $loaded = @$dom->loadXML(
        $xml,
        LIBXML_NOERROR |
            LIBXML_NOWARNING |
            LIBXML_NONET
    );


    if (!$loaded) {

        libxml_clear_errors();

        return '';
    }


    libxml_clear_errors();


    /*
    |--------------------------------------------------------------------------
    | XPATH
    |--------------------------------------------------------------------------
    */

    $xpath = new DOMXPath($dom);

    $xpath->registerNamespace(
        'w',
        'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
    );


    $output = [];


    /*
    |--------------------------------------------------------------------------
    | READ DOCUMENT PARAGRAPHS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Empty paragraphs are preserved.
    |
    |--------------------------------------------------------------------------
    */

    $paragraphNodes =
        $xpath->query(
            '//w:body/w:p'
        );


    if ($paragraphNodes !== false) {

        foreach (
            $paragraphNodes as $paragraph
        ) {

            $parts = [];


            $textNodes =
                $xpath->query(
                    './/w:t',
                    $paragraph
                );


            if ($textNodes !== false) {

                foreach (
                    $textNodes as $textNode
                ) {

                    $parts[] =
                        $textNode->nodeValue;
                }
            }


            $paragraphText =
                implode(
                    '',
                    $parts
                );


            /*
            |--------------------------------------------------------------------------
            | TABS
            |--------------------------------------------------------------------------
            */

            $paragraphText =
                str_replace(
                    "\t",
                    ' ',
                    $paragraphText
                );


            /*
            |--------------------------------------------------------------------------
            | DECODE XML
            |--------------------------------------------------------------------------
            */

            $paragraphText =
                html_entity_decode(
                    $paragraphText,
                    ENT_QUOTES | ENT_XML1,
                    'UTF-8'
                );


            /*
            |--------------------------------------------------------------------------
            | NORMALIZE SPACES
            |--------------------------------------------------------------------------
            */

            $paragraphText =
                preg_replace(
                    '/[ \t]+/u',
                    ' ',
                    $paragraphText
                );


            $paragraphText =
                trim(
                    $paragraphText
                );


            /*
            |--------------------------------------------------------------------------
            | KEEP EMPTY PARAGRAPHS
            |--------------------------------------------------------------------------
            */

            $output[] =
                $paragraphText;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | READ TABLES
    |--------------------------------------------------------------------------
    |
    | Some resumes store contact information,
    | skills, or other information inside tables.
    |
    |--------------------------------------------------------------------------
    */

    $tableRows =
        $xpath->query(
            '//w:tr'
        );


    if (
        $tableRows !== false &&
        $tableRows->length > 0
    ) {

        foreach (
            $tableRows as $row
        ) {

            $cells = [];


            $cellNodes =
                $xpath->query(
                    './w:tc',
                    $row
                );


            if ($cellNodes === false) {
                continue;
            }


            foreach (
                $cellNodes as $cell
            ) {

                $cellText = '';


                $textNodes =
                    $xpath->query(
                        './/w:t',
                        $cell
                    );


                if (
                    $textNodes !== false
                ) {

                    foreach (
                        $textNodes as $node
                    ) {

                        $cellText .=
                            ' ' .
                            $node->nodeValue;
                    }
                }


                $cellText =
                    trim(
                        preg_replace(
                            '/\s+/u',
                            ' ',
                            $cellText
                        )
                    );


                if (
                    $cellText !== ''
                ) {

                    $cells[] =
                        $cellText;
                }
            }


            if (!empty($cells)) {

                $rowText =
                    implode(
                        ' | ',
                        $cells
                    );


                $output[] =
                    $rowText;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RETURN READABLE TEXT
    |--------------------------------------------------------------------------
    */

    if (empty($output)) {

        return '';
    }


    $text =
        implode(
            "\n",
            $output
        );


    return cleanExtractedDOCXText(
        $text
    );
}


/*
|--------------------------------------------------------------------------
| CLEAN DOCX TEXT
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Blank lines are preserved.
|
|--------------------------------------------------------------------------
*/

function cleanExtractedDOCXText($text)
{
    if (
        $text === null ||
        $text === ''
    ) {

        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE LINE ENDINGS
    |--------------------------------------------------------------------------
    */

    $text =
        str_replace(
            ["\r\n", "\r"],
            "\n",
            $text
        );


    /*
    |--------------------------------------------------------------------------
    | REMOVE NULL BYTES
    |--------------------------------------------------------------------------
    */

    $text =
        str_replace(
            "\0",
            '',
            $text
        );


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE EACH LINE
    |--------------------------------------------------------------------------
    */

    $lines =
        explode(
            "\n",
            $text
        );


    $cleanLines = [];


    foreach (
        $lines as $line
    ) {

        $line =
            preg_replace(
                '/[ \t]+/u',
                ' ',
                $line
            );


        $line =
            trim(
                $line
            );


        /*
        |--------------------------------------------------------------------------
        | DO NOT REMOVE EMPTY LINES
        |--------------------------------------------------------------------------
        */

        $cleanLines[] =
            $line;
    }


    /*
    |--------------------------------------------------------------------------
    | REBUILD TEXT
    |--------------------------------------------------------------------------
    */

    $text =
        implode(
            "\n",
            $cleanLines
        );


    /*
    |--------------------------------------------------------------------------
    | MAX TWO CONSECUTIVE EMPTY LINES
    |--------------------------------------------------------------------------
    */

    $text =
        preg_replace(
            "/\n{3,}/u",
            "\n\n",
            $text
        );


    return trim(
        $text
    );
}


/*
|--------------------------------------------------------------------------
| PARSE RESUME TEXT
|--------------------------------------------------------------------------
*/

function parseResumeText($text)
{
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

        'social_links' => [],

        'contact' => [],

        'activities' => [],

        'interests' => [],
    ];


    if (
        empty(trim($text))
    ) {

        return $parsed;
    }


    $text =
        cleanExtractedDOCXText(
            $text
        );


    /*
    |--------------------------------------------------------------------------
    | EMAIL
    |--------------------------------------------------------------------------
    */

    if (
        preg_match(
            '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
            $text,
            $matches
        )
    ) {

        $parsed['email'] =
            trim(
                $matches[0]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | PHONE
    |--------------------------------------------------------------------------
    */

    if (
        preg_match(
            '/(?:\+?\d{1,4}[\s.-]?)?(?:\(?\d{2,4}\)?[\s.-]?)?\d{3,4}[\s.-]?\d{3,4}/',
            $text,
            $matches
        )
    ) {

        $parsed['phone'] =
            trim(
                $matches[0]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SOCIAL LINKS
    |--------------------------------------------------------------------------
    */

    preg_match_all(
        '~https?://[^\s]+~i',
        $text,
        $urlMatches
    );


    if (
        !empty($urlMatches[0])
    ) {

        $parsed['social_links'] =
            array_values(
                array_unique(
                    $urlMatches[0]
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | LINES
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Use /\\n/ instead of /\\n+/ so blank lines remain.
    |
    |--------------------------------------------------------------------------
    */

    $lines =
        preg_split(
            '/\n/u',
            $text
        );


    $lines =
        array_map(
            'trim',
            $lines
        );


    /*
    |--------------------------------------------------------------------------
    | NAME
    |--------------------------------------------------------------------------
    */

    foreach (
        $lines as $line
    ) {

        if (
            $line === ''
        ) {
            continue;
        }


        if (
            strlen($line) < 3 ||
            strlen($line) > 60
        ) {
            continue;
        }


        if (
            str_contains(
                $line,
                '@'
            )
        ) {
            continue;
        }


        if (
            preg_match(
                '/https?:\/\//i',
                $line
            )
        ) {
            continue;
        }


        if (
            preg_match(
                '/\d{5,}/',
                $line
            )
        ) {
            continue;
        }


        $lower =
            strtolower(
                trim($line)
            );


        $ignored = [
            'activities',

            'extracurricular activities',

            'involvement',

            'participation',

            'campus activities',

            'student activities',

            'activity',

            'interests',

            'passions',

            'hobbies',

            'area of interest',

            'areas of interest',

            'area of focus',

            'personal interests',

            'fields of interest',

            'resume',

            'curriculum vitae',

            'cv',

            'summary',

            'professional summary',

            'profile',

            'professional profile',

            'about',

            'about me',

            'experience',

            'work experience',

            'professional experience',

            'education',

            'skills',

            'technical skills',

            'projects',

            'certifications',

            'certification',

            'languages',

            'contact',

            'contact me',

            'achievements',

            'awards'
        ];


        if (
            in_array(
                $lower,
                $ignored,
                true
            )
        ) {

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Avoid obvious long sentences
        |--------------------------------------------------------------------------
        */

        if (
            substr_count(
                $line,
                ' '
            ) > 7
        ) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Names usually contain letters/spaces/apostrophes/hyphens
        |--------------------------------------------------------------------------
        */

        $name =
            preg_replace(
                "/[^a-zA-Z\s.'-]/",
                '',
                $line
            );


        $name =
            trim(
                $name
            );


        if (
            strlen($name) >= 3
        ) {

            $parsed['full_name'] =
                $name;

            break;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SECTION KEYWORDS
    |--------------------------------------------------------------------------
    */

    $sectionKeywords = [

        'summary' => [

            'summary',

            'professional summary',

            'about',

            'about me',

            'profile',

            'professional profile',

            'objective',

            'career objective'
        ],


        'education' => [

            'education',

            'academic',

            'academic background',

            'qualification',

            'qualifications',

            'educational background'
        ],


        'skills' => [

            'skills',

            'technical skills',

            'core competencies',

            'competencies',

            'expertise',

            'technical expertise',

            'technical proficiencies'
        ],


        'experience' => [

            'experience',

            'work experience',

            'professional experience',

            'employment',

            'employment history',

            'work history'
        ],


        'projects' => [

            'projects',

            'key projects',

            'personal projects',

            'academic projects',

            'project experience'
        ],


        'certifications' => [

            'certifications',

            'certification',

            'certificates',

            'courses',

            'training'
        ],


        'achievements' => [

            'achievements',

            'achievement',

            'awards',

            'honors',

            'honours'
        ],


        'languages' => [

            'languages',

            'language',

            'language proficiency'
        ],

        'contact' => [

            'contact',

            'contact me',

            'get in touch',

            'reach me'
        ],

        'activities' => [
            'activities',

            'extracurricular activities',

            'involvement',

            'participation',

            'campus activities',

            'student activities',

            'activity'
        ],

        'interests' => [
            'interests',

            'passions',

            'hobbies',

            'area of interest',

            'area of focus',

            'personal interests',

            'fields of interest'
        ]

    ];


    /*
    |--------------------------------------------------------------------------
    | PARSE SECTIONS
    |--------------------------------------------------------------------------
    */

    $currentSection = null;

    $buffer = [];


    foreach (
        $lines as $line
    ) {

        $trimmedLine =
            trim(
                $line
            );


        $lowerLine =
            strtolower(
                $trimmedLine
            );


        $foundSection = null;


        /*
        |--------------------------------------------------------------------------
        | Detect section heading
        |--------------------------------------------------------------------------
        */

        foreach (
            $sectionKeywords as $section => $keywords
        ) {

            foreach (
                $keywords as $keyword
            ) {

                if (
                    $lowerLine === $keyword
                ) {

                    $foundSection =
                        $section;

                    break 2;
                }


                if (
                    str_starts_with(
                        $lowerLine,
                        $keyword . ':'
                    )
                ) {

                    $foundSection =
                        $section;

                    break 2;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | NEW SECTION
        |--------------------------------------------------------------------------
        */

        if (
            $foundSection !== null
        ) {

            /*
            |--------------------------------------------------------------------------
            | Process previous section
            |--------------------------------------------------------------------------
            */

            if (
                $currentSection !== null &&
                !empty($buffer)
            ) {

                processSectionBuffer(
                    $currentSection,
                    $buffer,
                    $parsed
                );
            }


            $buffer = [];


            $currentSection =
                $foundSection;


            /*
            |--------------------------------------------------------------------------
            | Handle "Summary: text"
            |--------------------------------------------------------------------------
            */

            $colonPosition =
                strpos(
                    $line,
                    ':'
                );


            if (
                $colonPosition !== false
            ) {

                $afterColon =
                    trim(
                        substr(
                            $line,
                            $colonPosition + 1
                        )
                    );


                if (
                    $afterColon !== ''
                ) {

                    $buffer[] =
                        $afterColon;
                }
            }


            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Add line to current section
        |--------------------------------------------------------------------------
        |
        | Keep blank lines because they separate entries.
        |
        |--------------------------------------------------------------------------
        */

        if (
            $currentSection !== null
        ) {

            $buffer[] =
                $trimmedLine;

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Guess summary
        |--------------------------------------------------------------------------
        */

        if (
            empty($parsed['summary']) &&
            strlen($trimmedLine) > 40
        ) {

            $parsed['summary'] =
                $trimmedLine;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PROCESS FINAL SECTION
    |--------------------------------------------------------------------------
    */

    if (
        $currentSection !== null &&
        !empty($buffer)
    ) {

        processSectionBuffer(
            $currentSection,
            $buffer,
            $parsed
        );
    }


    return $parsed;
}


/*
|--------------------------------------------------------------------------
| PROCESS RESUME SECTION
|--------------------------------------------------------------------------
*/

function processSectionBuffer(
    $section,
    $buffer,
    &$parsed
) {

    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */

    if (
        $section === 'summary'
    ) {

        $cleanBuffer = [];


        foreach (
            $buffer as $line
        ) {

            $line =
                trim(
                    $line
                );


            if (
                $line !== ''
            ) {

                $cleanBuffer[] =
                    $line;
            }
        }


        if (
            !empty($cleanBuffer)
        ) {

            $parsed['summary'] =
                trim(
                    implode(
                        ' ',
                        $cleanBuffer
                    )
                );
        }


        return;
    }


    /*
    |--------------------------------------------------------------------------
    | SKILLS
    |--------------------------------------------------------------------------
    */

    if (
        $section === 'skills'
    ) {

        $items = [];


        foreach (
            $buffer as $line
        ) {

            $line =
                trim(
                    $line
                );


            if (
                $line === ''
            ) {
                continue;
            }


            $parts =
                preg_split(
                    '/[,;|•●▪■]+/u',
                    $line
                );


            foreach (
                $parts as $part
            ) {

                $clean =
                    trim(
                        $part
                    );


                $clean =
                    preg_replace(
                        '/^[\s\-*•●▪■]+/u',
                        '',
                        $clean
                    );


                $clean =
                    trim(
                        $clean
                    );


                if (
                    $clean !== '' &&
                    strlen($clean) <= 100
                ) {

                    $items[] =
                        $clean;
                }
            }
        }


        $items =
            array_values(
                array_unique(
                    $items
                )
            );


        $parsed['skills'] =
            array_values(
                array_unique(
                    array_merge(
                        $parsed['skills'],
                        $items
                    )
                )
            );


        return;
    }


    /*
    |--------------------------------------------------------------------------
    | OTHER SECTIONS
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $section,
            [
                'education',
                'experience',
                'projects',
                'certifications',
                'achievements',
                'languages',
                'contact',
                'activities',
                'interests',
            ],
            true
        )
    ) {

        $entries = [];

        $currentEntry = [];

        $singleLineSections = [
            'certifications',
            'achievements',
            'languages',
            'activities',
            'interests',
            'contact'
        ];

        $isSingleLineSection = in_array($section, $singleLineSections, true);


        foreach (
            $buffer as $line
        ) {

            $line =
                trim(
                    $line
                );


            /*
            |--------------------------------------------------------------------------
            | Blank line = new entry
            |--------------------------------------------------------------------------
            */

            if (
                $line === ''
            ) {

                if (
                    !empty($currentEntry)
                ) {

                    $entries[] =
                        trim(
                            implode(
                                "\n",
                                $currentEntry
                            )
                        );

                    $currentEntry = [];
                }


                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Bullet or single line section entry
            |--------------------------------------------------------------------------
            */

            $isBullet = preg_match('/^[\s\-*•●▪■–]/u', $line);

            if ($isBullet) {

                if (!empty($currentEntry)) {
                    $entries[] = trim(implode("\n", $currentEntry));
                    $currentEntry = [];
                }

                $cleanLine = trim(preg_replace('/^[\s\-*•●▪■–]+/u', '', $line));

                if ($cleanLine !== '') {
                    $currentEntry[] = $cleanLine;
                }

                continue;
            }


            if ($isSingleLineSection) {

                if (!empty($currentEntry)) {
                    $entries[] = trim(implode("\n", $currentEntry));
                    $currentEntry = [];
                }

                $currentEntry[] = $line;

                continue;
            }


            $currentEntry[] =
                $line;
        }


        /*
        |--------------------------------------------------------------------------
        | Add final entry
        |--------------------------------------------------------------------------
        */

        if (
            !empty($currentEntry)
        ) {

            $entries[] =
                trim(
                    implode(
                        "\n",
                        $currentEntry
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | If there were no blank-line-separated entries,
        | keep each line as an individual entry.
        |--------------------------------------------------------------------------
        */

        if (
            empty($entries)
        ) {

            foreach (
                $buffer as $line
            ) {

                $line =
                    trim(
                        $line
                    );


                if (
                    $line !== ''
                ) {

                    $entries[] =
                        $line;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Remove duplicates and empty entries
        |--------------------------------------------------------------------------
        */

        $entries =
            array_values(
                array_unique(
                    array_filter(
                        $entries,
                        function ($entry) {

                            return trim(
                                $entry
                            ) !== '';
                        }
                    )
                )
            );


        $parsed[$section] =
            array_values(
                array_unique(
                    array_merge(
                        $parsed[$section],
                        $entries
                    )
                )
            );
    }
}


/*
|--------------------------------------------------------------------------
| GET OR CREATE USER PORTFOLIO
|--------------------------------------------------------------------------
*/

function getOrCreateUserPortfolio(
    $userId,
    $pdo
) {

    $stmt =
        $pdo->prepare(
            "SELECT *
             FROM portfolios
             WHERE user_id = ?"
        );


    $stmt->execute([
        $userId
    ]);


    $portfolio =
        $stmt->fetch();


    if (!$portfolio) {

        /*
        |--------------------------------------------------------------------------
        | GET USER
        |--------------------------------------------------------------------------
        */

        $uStmt =
            $pdo->prepare(
                "SELECT full_name, username
                 FROM users
                 WHERE user_id = ?"
            );


        $uStmt->execute([
            $userId
        ]);


        $user =
            $uStmt->fetch();


        /*
        |--------------------------------------------------------------------------
        | GENERATE SLUG
        |--------------------------------------------------------------------------
        */

        $baseSlug =
            generateSlug(
                $user['username']
                    ??
                    $user['full_name']
                    ??
                    'portfolio'
            );


        $slug =
            $baseSlug;


        $counter = 1;


        while (true) {

            $checkStmt =
                $pdo->prepare(
                    "SELECT portfolio_id
                     FROM portfolios
                     WHERE portfolio_slug = ?"
                );


            $checkStmt->execute([
                $slug
            ]);


            if (
                !$checkStmt->fetch()
            ) {

                break;
            }


            $slug =
                $baseSlug
                . '-'
                . $counter++;
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE PORTFOLIO
        |--------------------------------------------------------------------------
        */

        $insStmt =
            $pdo->prepare(
                "INSERT INTO portfolios
                (
                    user_id,
                    template_id,
                    title,
                    portfolio_slug,
                    status
                )
                VALUES (?, 1, ?, ?, 'draft')"
            );


        $insStmt->execute([
            $userId,

            ($user['full_name'] ?? 'My')
                . "'s Portfolio",

            $slug
        ]);


        $portfolioId =
            $pdo->lastInsertId();


        /*
        |--------------------------------------------------------------------------
        | DEFAULT SECTIONS
        |--------------------------------------------------------------------------
        */

        $defaultSections = [

            [
                'type' => 'about',
                'title' => 'About Me',
                'order' => 1
            ],

            [
                'type' => 'education',
                'title' => 'Education',
                'order' => 2
            ],

            [
                'type' => 'skills',
                'title' => 'Skills',
                'order' => 3
            ],

            [
                'type' => 'projects',
                'title' => 'Projects',
                'order' => 4
            ],

            [
                'type' => 'experience',
                'title' => 'Work Experience',
                'order' => 5
            ],

            [
                'type' => 'contact',
                'title' => 'Contact Me',
                'order' => 6
            ]
        ];


        $secStmt =
            $pdo->prepare(
                "INSERT INTO portfolio_sections
                (
                    portfolio_id,
                    section_type,
                    title,
                    content,
                    display_order,
                    is_visible
                )
                VALUES (?, ?, ?, ?, ?, 1)"
            );


        foreach (
            $defaultSections as $section
        ) {

            $secStmt->execute([
                $portfolioId,

                $section['type'],

                $section['title'],

                json_encode(
                    [],
                    JSON_UNESCAPED_UNICODE
                ),

                $section['order']
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | RELOAD PORTFOLIO
        |--------------------------------------------------------------------------
        */

        $stmt->execute([
            $userId
        ]);


        $portfolio =
            $stmt->fetch();
    }


    return $portfolio;
}
