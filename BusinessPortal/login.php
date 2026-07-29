<?php
declare(strict_types=1);

define('PORTAL_PUBLIC', true);
require_once __DIR__ . '/inc/bootstrap.php';

// Already signed in — nothing to do here.
if (auth_check()) {
    header('Location: ' . portal_url('index.php'));
    exit;
}

$error = null;
$username = '';
$lockedFor = login_lockout($pdo);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($lockedFor > 0) {
        $error = 'Too many failed attempts. Try again in ' . ceil($lockedFor / 60) . ' minute(s).';
    } elseif ($username === '' || $password === '') {
        $error = 'Enter both your username and password.';
    } else {
        $st = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
        $st->execute([$username]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Upgrade the stored hash if PHP's default algorithm has moved on.
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            }
            $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);

            login_reset($pdo);
            auth_login($user);

            header('Location: ' . portal_url('index.php'));
            exit;
        }

        login_fail($pdo);
        $lockedFor = login_lockout($pdo);
        // Deliberately vague: naming which field was wrong tells an attacker
        // whether the username exists.
        $error = $lockedFor > 0
            ? 'Too many failed attempts. Locked for ' . ceil($lockedFor / 60) . ' minute(s).'
            : 'Those details did not match. Please try again.';
    }
}

$flashes = flash_take();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Sign In — Royale Surfaces Portal</title>
    <link rel="icon" href="<?= e(favicon_url()) ?>" type="<?= e(favicon_type()) ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="<?= e(asset('BusinessPortal/assets/admin.css')) ?>" />
</head>

<body class="auth-split">

    <!-- LEFT: brand panel -->
    <section class="auth-visual" aria-hidden="true">
        <img src="<?= $base_url ?>assets/images/hero-stone.jpg" alt="" />
        <div class="auth-visual-overlay"></div>
        <div class="auth-visual-body">
            <span class="auth-eyebrow">Content Portal</span>
            <h2>Every slab,<br />every story,<br />in one place.</h2>
            <p>Publish inventory, projects and articles — the site and its sitemap update themselves.</p>
        </div>
        <div class="auth-visual-foot">
            <span><?= e(site_city()) ?>, <?= e(site_state()) ?></span>
            <span><?= e(SITE_DOMAIN) ?></span>
        </div>
    </section>

    <!-- RIGHT: form -->
    <main class="auth-pane">
        <div class="auth-inner">
            <div class="auth-brand">
                <?php if (has_logo()): ?>
                    <img src="<?= e(logo_url()) ?>" alt="<?= e(site_name()) ?>" class="auth-brand-logo" />
                <?php else: ?>
                    <span class="auth-logo">R</span>
                    <span><?= e(site_name()) ?></span>
                <?php endif; ?>
            </div>

            <h1>Sign in</h1>
            <p class="auth-sub">Enter your administrator credentials to manage the site.</p>

            <?php foreach ($flashes as $f): ?>
                <div class="alert is-<?= e($f['type']) ?>"><?= $f['message'] ?></div>
            <?php endforeach; ?>

            <?php if ($error): ?>
                <div class="alert is-error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="auth-form" novalidate>
                <?= csrf_field() ?>

                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= e($username) ?>"
                    autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus
                    <?= $lockedFor > 0 ? 'disabled' : '' ?> />

                <label for="password">Password</label>
                <div class="field-with-toggle">
                    <input type="password" id="password" name="password" autocomplete="current-password" required
                        <?= $lockedFor > 0 ? 'disabled' : '' ?> />
                    <button type="button" class="reveal" data-reveal-for="password" aria-label="Show password">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>

                <button type="submit" class="btn-admin is-primary is-block" <?= $lockedFor > 0 ? 'disabled' : '' ?>>
                    <?= $lockedFor > 0 ? 'Locked' : 'Sign In' ?>
                </button>
            </form>

            <p class="auth-note">
                Protected area. Failed attempts are limited to <?= MAX_ATTEMPTS ?> before a
                <?= LOCKOUT_MINUTES ?>-minute lockout.
            </p>

            <a href="<?= $base_url ?>" class="auth-back">← Back to royalesurfaces.com</a>
        </div>
    </main>

    <script src="<?= e(asset('BusinessPortal/assets/admin.js')) ?>" defer></script>
</body>

</html>
