<?php
date_default_timezone_set('America/Chicago');   // Texas — matches showroom hours
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/site.php';

$url = $_SERVER['HTTP_HOST'] ?? SITE_DOMAIN;
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

$_isLocal = strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false;

if ($_isLocal) {
    // Local development — project lives in a /RoyaleSurfaces/ subfolder
    $base_url = $protocol . '://' . $url . '/RoyaleSurfaces/';
    $base_path = $_SERVER['DOCUMENT_ROOT'] . '/RoyaleSurfaces/';
} else {
    // Production — site is served from the domain root
    $base_url = 'https://' . SITE_DOMAIN . '/';
    $base_path = $_SERVER['DOCUMENT_ROOT'] . '/';
}

// ── Database connection ──────────────────────────────────────────────────────
if ($_isLocal) {
    $_db = ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'royalesurfaces', 'port' => 3306];
} else {
    $_credFile = __DIR__ . '/db.credentials.php';
    $_db = is_file($_credFile)
        ? require $_credFile
        : ['host' => 'localhost', 'user' => '', 'pass' => '', 'name' => '', 'port' => 3306];
}

// The site renders fully without a database — these are optional (blog/settings).
// Note: since PHP 8.1 mysqli throws on failure instead of setting connect_error,
// so this MUST be wrapped or an unreachable DB takes the whole site down.
mysqli_report(MYSQLI_REPORT_OFF);
try {
    $conn = @new mysqli($_db['host'], $_db['user'], $_db['pass'], $_db['name'], $_db['port']);
    if ($conn->connect_errno) {
        throw new RuntimeException($conn->connect_error ?? 'unknown error');
    }
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    error_log('Royale Surfaces DB connection failed: ' . $e->getMessage());
    $conn = null;
}

// PDO instance for new code
try {
    $pdo = new PDO(
        "mysql:host={$_db['host']};port={$_db['port']};dbname={$_db['name']};charset=utf8mb4",
        $_db['user'], $_db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Throwable $e) {
    error_log('Royale Surfaces PDO failed: ' . $e->getMessage());
    $pdo = null;
}

require_once __DIR__ . '/settings.php';   // setting(), social_links(), analytics_id()

/** Helper: clean phone number for tel: links (digits only, with optional leading +). */
function tel_link(string $phone): string
{
    $digits = preg_replace('/[^\d+]/', '', $phone);
    return 'tel:' . $digits;
}

/** Helper: clean phone number for wa.me links (digits only, no plus). */
function wa_link(string $phone): string
{
    return 'https://wa.me/' . preg_replace('/\D/', '', $phone);
}

function getCurrentURL(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? SITE_DOMAIN;
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return $protocol . '://' . $host . $uri;
}

// Usage example:
$currentURL = getCurrentURL();

