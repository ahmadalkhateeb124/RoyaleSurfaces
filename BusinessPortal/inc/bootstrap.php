<?php
/**
 * Portal bootstrap — session, database, auth guard and shared helpers.
 * Every admin page includes this first.
 *
 * Pages that must be reachable while logged out (login.php) define
 * PORTAL_PUBLIC before including this file.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/conn.php';   // $pdo, $base_url, site constants

// Admin pages must never be cached or indexed.
header('X-Robots-Tag: noindex, nofollow', true);
header('Cache-Control: no-store, no-cache, must-revalidate');

const PORTAL_URL      = 'BusinessPortal/';
const SESSION_IDLE    = 3600 * 4;   // log out after 4 hours of inactivity
const MAX_ATTEMPTS    = 5;
const LOCKOUT_MINUTES = 15;

/** Absolute URL to a page inside the portal. */
function portal_url(string $path = ''): string
{
    global $base_url;
    return $base_url . PORTAL_URL . ltrim($path, '/');
}

/** Escape helper (mirrors the front-end `e()`), safe to call on null. */
if (!function_exists('e')) {
    function e(?string $v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

// ── CSRF ─────────────────────────────────────────────────────────────────────

/** Per-session CSRF token, generated once. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Hidden input for forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '" />';
}

/**
 * Verify the submitted token. Every state-changing POST must call this —
 * without it, a malicious page could make the logged-in admin delete content.
 */
function csrf_check(): void
{
    $sent = (string) ($_POST['_token'] ?? '');
    if ($sent === '' || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('Session expired. Go back, reload the page and try again.');
    }
}

// ── Flash messages ───────────────────────────────────────────────────────────

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Read and clear queued messages. */
function flash_take(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// ── Authentication ───────────────────────────────────────────────────────────

function auth_user(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

/** Client IP, used only for login throttling. */
function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/**
 * How long this IP is locked out for, in seconds. Zero means it may try.
 * Throttling by IP is what stops an unattended password-guessing script.
 */
function login_lockout(PDO $pdo): int
{
    // Both sides of the comparison must come from MySQL. Reading locked_until
    // and diffing it against PHP's time() silently adds the offset between the
    // two clocks — that turned a 15-minute lockout into 8 hours in testing.
    $st = $pdo->prepare(
        'SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), locked_until))
         FROM login_attempts WHERE ip = ? AND locked_until IS NOT NULL'
    );
    $st->execute([client_ip()]);

    return (int) ($st->fetchColumn() ?: 0);
}

function login_fail(PDO $pdo): void
{
    $ip = client_ip();
    $pdo->prepare(
        'INSERT INTO login_attempts (ip, attempts) VALUES (?, 1)
         ON DUPLICATE KEY UPDATE attempts = attempts + 1'
    )->execute([$ip]);

    $st = $pdo->prepare('SELECT attempts FROM login_attempts WHERE ip = ?');
    $st->execute([$ip]);

    if ((int) $st->fetchColumn() >= MAX_ATTEMPTS) {
        $pdo->prepare(
            'UPDATE login_attempts SET attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE ip = ?'
        )->execute([LOCKOUT_MINUTES, $ip]);
    }
}

function login_reset(PDO $pdo): void
{
    $pdo->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([client_ip()]);
}

function auth_login(array $user): void
{
    // New ID on privilege change defeats session fixation.
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'       => (int) $user['id'],
        'username' => $user['username'],
        'name'     => $user['full_name'] ?: $user['username'],
    ];
    $_SESSION['last_seen'] = time();
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ── Guard ────────────────────────────────────────────────────────────────────

if (!defined('PORTAL_PUBLIC')) {
    if (!auth_check()) {
        header('Location: ' . portal_url('login.php'));
        exit;
    }

    // Idle timeout.
    if (time() - ($_SESSION['last_seen'] ?? 0) > SESSION_IDLE) {
        auth_logout();
        session_start();
        flash('error', 'You were signed out after a period of inactivity.');
        header('Location: ' . portal_url('login.php'));
        exit;
    }
    $_SESSION['last_seen'] = time();
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    exit('Database unavailable. Check inc/conn.php.');
}
