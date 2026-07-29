<?php
/**
 * One-time installer.
 *
 * Creates the database schema, seeds it from the existing PHP content arrays,
 * and creates the single admin account. Delete this file once you have run it.
 *
 *   http://localhost/RoyaleSurfaces/BusinessPortal/install.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../inc/conn.php';
require_once __DIR__ . '/../inc/posts.php';
require_once __DIR__ . '/../inc/projects.php';

$done = [];
$errors = [];
$created = null;

// ── Schema ───────────────────────────────────────────────────────────────────
const SCHEMA = [
'admin_users' => "CREATE TABLE IF NOT EXISTS admin_users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(60)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name     VARCHAR(120) NOT NULL DEFAULT '',
    last_login_at DATETIME NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'posts' => "CREATE TABLE IF NOT EXISTS posts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(180) NOT NULL UNIQUE,
    title        VARCHAR(255) NOT NULL,
    meta_title       VARCHAR(255) NOT NULL DEFAULT '',
    excerpt      TEXT,
    meta_description VARCHAR(320) NOT NULL DEFAULT '',
    meta_keywords    VARCHAR(255) NOT NULL DEFAULT '',
    body         LONGTEXT,
    category     VARCHAR(40)  NOT NULL DEFAULT 'trends',
    image        VARCHAR(255) NOT NULL DEFAULT '',
    og_image     VARCHAR(255) NOT NULL DEFAULT '',
    read_minutes TINYINT UNSIGNED NOT NULL DEFAULT 3,
    status       ENUM('draft','published') NOT NULL DEFAULT 'published',
    noindex      TINYINT(1) NOT NULL DEFAULT 0,
    published_at DATE NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_live (status, published_at),
    INDEX idx_cat (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'slabs' => "CREATE TABLE IF NOT EXISTS slabs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(160) NOT NULL,
    type       VARCHAR(40)  NOT NULL,
    origin     VARCHAR(80)  NOT NULL DEFAULT '',
    finish     VARCHAR(80)  NOT NULL DEFAULT '',
    thickness  VARCHAR(40)  NOT NULL DEFAULT '',
    size       VARCHAR(80)  NOT NULL DEFAULT '',
    stock      INT NOT NULL DEFAULT 0,
    image      VARCHAR(255) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    status     ENUM('draft','published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_live (status, type, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'projects' => "CREATE TABLE IF NOT EXISTS projects (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(200) NOT NULL,
    space      VARCHAR(80)  NOT NULL DEFAULT '',
    material   VARCHAR(160) NOT NULL DEFAULT '',
    type       VARCHAR(40)  NOT NULL,
    location   VARCHAR(120) NOT NULL DEFAULT '',
    body       TEXT,
    image      VARCHAR(255) NOT NULL DEFAULT '',
    is_feature TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    status     ENUM('draft','published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_live (status, type, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'settings' => "CREATE TABLE IF NOT EXISTS settings (
    `key`      VARCHAR(60) PRIMARY KEY,
    `value`    TEXT,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'inquiries' => "CREATE TABLE IF NOT EXISTS inquiries (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(160) NOT NULL,
    company     VARCHAR(160) NOT NULL DEFAULT '',
    phone       VARCHAR(60)  NOT NULL DEFAULT '',
    email       VARCHAR(190) NOT NULL,
    subject     VARCHAR(190) NOT NULL DEFAULT '',
    message     TEXT         NOT NULL,
    ip          VARCHAR(45)  NOT NULL DEFAULT '',
    user_agent  VARCHAR(255) NOT NULL DEFAULT '',
    referer     VARCHAR(255) NOT NULL DEFAULT '',
    spam_score  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    spam_reason VARCHAR(255) NOT NULL DEFAULT '',
    is_spam     TINYINT(1)   NOT NULL DEFAULT 0,
    emailed     TINYINT(1)   NOT NULL DEFAULT 0,
    status      ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_inbox (is_spam, status, created_at),
    INDEX idx_ip (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'faqs' => "CREATE TABLE IF NOT EXISTS faqs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    question     VARCHAR(255) NOT NULL,
    answer       TEXT NOT NULL,
    group_name   VARCHAR(80) NOT NULL DEFAULT 'General',
    sort_order   INT NOT NULL DEFAULT 0,
    show_on_home TINYINT(1) NOT NULL DEFAULT 0,
    status       ENUM('draft','published') NOT NULL DEFAULT 'published',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_live (status, group_name, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'login_attempts' => "CREATE TABLE IF NOT EXISTS login_attempts (
    ip           VARCHAR(45) PRIMARY KEY,
    attempts     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

/** Turn the stored [tag, text] pairs back into the editor's plain-text format. */
function body_to_text(array $body): string
{
    $out = [];
    foreach ($body as [$tag, $text]) {
        $out[] = $tag === 'h2' ? '## ' . $text : $text;
    }
    return implode("\n\n", $out);
}

$ran = false;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $ran = true;
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['confirm'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9_.-]{3,60}$/', $username)) {
        $errors[] = 'Username must be 3–60 characters (letters, numbers, dot, dash, underscore).';
    }
    if (strlen($password) < 10) {
        $errors[] = 'Password must be at least 10 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (!isset($pdo) || !$pdo instanceof PDO) {
        $errors[] = 'No database connection. Create the database first, then reload this page.';
    }

    if (!$errors) {
        try {
            foreach (SCHEMA as $table => $sql) {
                $pdo->exec($sql);
                $done[] = "Table <code>$table</code> ready.";
            }

            // ── Seed content from the existing PHP arrays (idempotent) ───────
            if ((int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn() === 0) {
                $st = $pdo->prepare(
                    'INSERT INTO posts (slug, title, excerpt, body, category, image, read_minutes, published_at)
                     VALUES (?,?,?,?,?,?,?,?)'
                );
                foreach (BLOG_POSTS as $slug => $p) {
                    $st->execute([
                        $slug, $p['title'], $p['excerpt'], body_to_text($p['body']),
                        $p['category'], $p['image'], $p['read'], $p['published'],
                    ]);
                }
                $done[] = 'Seeded ' . count(BLOG_POSTS) . ' blog posts.';
            }

            if ((int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn() === 0) {
                $st = $pdo->prepare(
                    'INSERT INTO projects (title, space, material, type, location, body, image, is_feature, sort_order)
                     VALUES (?,?,?,?,?,?,?,?,?)'
                );
                foreach (gallery_projects() as $i => $p) {
                    $st->execute([
                        $p['title'], $p['space'], $p['material'], $p['type'], $p['location'],
                        $p['body'], $p['image'], !empty($p['feature']) ? 1 : 0, $i,
                    ]);
                }
                $done[] = 'Seeded ' . count(gallery_projects()) . ' gallery projects.';
            }

            if ((int) $pdo->query('SELECT COUNT(*) FROM slabs')->fetchColumn() === 0) {
                // Slab seed data lives inside pages/Slabs.php — pull it without rendering.
                $seed = [
                    ['Calacatta Gold','quartz','Engineered','Polished','3cm','126" × 63"','slab-quartz.jpg'],
                    ['Pure White','quartz','Engineered','Matte','2cm','126" × 63"','slab-quartz.jpg'],
                    ['Statuario Maximus','porcelain','Italy','Polished','12mm','126" × 63"','slab-marble.jpg'],
                    ['Basalt Grey','porcelain','Spain','Matte','6mm','126" × 63"','slab-marble.jpg'],
                    ['Silver Travertine','natural-stone','Turkey','Honed','2cm','120" × 72"','hero-stone.jpg'],
                    ['Honey Onyx','natural-stone','Iran','Polished','2cm','108" × 64"','hero-stone.jpg'],
                    ['Taj Mahal','quartzite','Brazil','Polished','3cm','130" × 80"','slab-quartzite.jpg'],
                    ['Macaubas Fantasy','quartzite','Brazil','Polished','2cm','132" × 78"','slab-quartzite.jpg'],
                    ['Absolute Black','granite','India','Polished','3cm','128" × 76"','slab-granite.jpg'],
                    ['Via Lactea','granite','Brazil','Leathered','3cm','125" × 75"','slab-granite.jpg'],
                    ['Carrara Venato','marble','Italy','Honed','2cm','115" × 70"','slab-marble.jpg'],
                    ['Nero Marquina','marble','Spain','Polished','2cm','118" × 71"','slab-marble.jpg'],
                    ['Glacier White','solid-surfaces','Engineered','Matte','12mm','145" × 30"','slab-quartz.jpg'],
                    ['Cameo Sand','solid-surfaces','Engineered','Satin','12mm','145" × 30"','slab-quartz.jpg'],
                ];
                $st = $pdo->prepare(
                    'INSERT INTO slabs (name, type, origin, finish, thickness, size, image, sort_order)
                     VALUES (?,?,?,?,?,?,?,?)'
                );
                foreach ($seed as $i => $row) {
                    $st->execute([...$row, $i]);
                }
                $done[] = 'Seeded ' . count($seed) . ' slabs.';
            }

            // ── Admin account ────────────────────────────────────────────────
            $exists = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
            if ($exists > 0) {
                $errors[] = 'An admin account already exists. Delete install.php — the portal is ready.';
            } else {
                $st = $pdo->prepare(
                    'INSERT INTO admin_users (username, password_hash, full_name) VALUES (?,?,?)'
                );
                $st->execute([$username, password_hash($password, PASSWORD_DEFAULT), 'Administrator']);
                $created = $username;
                $done[] = 'Admin account <code>' . htmlspecialchars($username) . '</code> created.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$dbName = $_db['name'] ?? '?';
$dbReady = isset($pdo) && $pdo instanceof PDO;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Install — Royale Surfaces Portal</title>
    <link rel="stylesheet" href="assets/admin.css" />
</head>

<body class="auth-body">
    <main class="auth-card" style="max-width:560px;">
        <div class="auth-brand">
            <span class="auth-logo">R</span>
            <span>Royale Surfaces</span>
        </div>

        <h1>Install the Portal</h1>
        <p class="auth-sub">Creates the database tables, imports your existing content, and sets up the single
            administrator account.</p>

        <?php if (!$dbReady): ?>
            <div class="alert is-error">
                <strong>No database connection.</strong>
                Create a database named <code><?= htmlspecialchars($dbName) ?></code> in phpMyAdmin, then reload.
            </div>
        <?php endif; ?>

        <?php foreach ($errors as $e): ?>
            <div class="alert is-error"><?= $e ?></div>
        <?php endforeach; ?>

        <?php if ($done): ?>
            <div class="alert is-ok">
                <?php foreach ($done as $d): ?>
                    <div><?= $d ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($created): ?>
            <div class="alert is-ok">
                <strong>Installation complete.</strong>
                Now <strong>delete <code>BusinessPortal/install.php</code></strong> — leaving it on a live server is a
                security risk.
            </div>
            <a href="login.php" class="btn-admin is-primary is-block">Go to Login</a>
        <?php elseif ($dbReady && !$ran || $errors): ?>
            <form method="post" class="auth-form" autocomplete="off">
                <label for="username">Admin Username</label>
                <input type="text" id="username" name="username" required minlength="3"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />

                <label for="password">Password <span class="hint">minimum 10 characters</span></label>
                <input type="password" id="password" name="password" required minlength="10" />

                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" required minlength="10" />

                <button type="submit" class="btn-admin is-primary is-block">Install</button>
            </form>
        <?php endif; ?>
    </main>
</body>

</html>
