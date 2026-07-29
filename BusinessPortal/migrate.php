<?php
/**
 * Idempotent schema migrations.
 *
 * Safe to run any number of times — each step checks whether it is already
 * applied. Run it after pulling changes that add columns:
 *
 *   http://localhost/RoyaleSurfaces/BusinessPortal/migrate.php
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

/** Does this column already exist? */
function has_column(PDO $pdo, string $table, string $column): bool
{
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $st->execute([$table, $column]);
    return (int) $st->fetchColumn() > 0;
}

/** Per-post SEO overrides, added after the initial release. */
const MIGRATIONS = [
    ['posts', 'meta_title',       "ALTER TABLE posts ADD COLUMN meta_title VARCHAR(255) NOT NULL DEFAULT '' AFTER title"],
    ['posts', 'meta_description', "ALTER TABLE posts ADD COLUMN meta_description VARCHAR(320) NOT NULL DEFAULT '' AFTER excerpt"],
    ['posts', 'meta_keywords',    "ALTER TABLE posts ADD COLUMN meta_keywords VARCHAR(255) NOT NULL DEFAULT '' AFTER meta_description"],
    ['posts', 'noindex',          "ALTER TABLE posts ADD COLUMN noindex TINYINT(1) NOT NULL DEFAULT 0 AFTER status"],
    ['posts', 'og_image',         "ALTER TABLE posts ADD COLUMN og_image VARCHAR(255) NOT NULL DEFAULT '' AFTER image"],
    ['slabs', 'stock',            "ALTER TABLE slabs ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER size"],
    ['orders', 'stock_applied',   "ALTER TABLE orders ADD COLUMN stock_applied TINYINT(1) NOT NULL DEFAULT 0"],
    ['trade_accounts', 'reset_token_hash',
        "ALTER TABLE trade_accounts ADD COLUMN reset_token_hash VARCHAR(64) NULL DEFAULT NULL AFTER password_hash,
         ADD INDEX idx_reset_token (reset_token_hash)"],
    ['trade_accounts', 'reset_expires',
        "ALTER TABLE trade_accounts ADD COLUMN reset_expires DATETIME NULL DEFAULT NULL AFTER reset_token_hash"],
];

// Tables that may not exist on installs made before this feature shipped.
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        `key`      VARCHAR(60) PRIMARY KEY,
        `value`    TEXT,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS inquiries (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS faqs (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `trade_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` varchar(160) NOT NULL,
  `contact_name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(60) NOT NULL DEFAULT '',
  `city` varchar(120) NOT NULL DEFAULT '',
  `tax_id` varchar(80) NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL,
  `status` enum('pending','active','suspended','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_status` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(20) NOT NULL,
  `account_id` int(11) NOT NULL,
  `status` enum('new','quoted','confirmed','ready','completed','cancelled') NOT NULL DEFAULT 'new',
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `needed_by` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `idx_queue` (`status`,`created_at`),
  KEY `idx_account` (`account_id`,`created_at`),
  CONSTRAINT `fk_order_account` FOREIGN KEY (`account_id`) REFERENCES `trade_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `slab_id` int(11) DEFAULT NULL,
  `slab_name` varchar(160) NOT NULL,
  `slab_type` varchar(40) NOT NULL DEFAULT '',
  `slab_slug` varchar(180) NOT NULL DEFAULT '',
  `quantity` smallint(5) unsigned NOT NULL DEFAULT 1,
  `size_note` varchar(160) NOT NULL DEFAULT '',
  `item_notes` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `trade_login_attempts` (
  `ip` varchar(45) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
} catch (Throwable $ex) { /* reported below via $failed if it matters */ }

$applied = [];
$skipped = [];
$failed  = [];

foreach (MIGRATIONS as [$table, $column, $sql]) {
    try {
        if (has_column($pdo, $table, $column)) {
            $skipped[] = "$table.$column";
            continue;
        }
        $pdo->exec($sql);
        $applied[] = "$table.$column";
    } catch (Throwable $ex) {
        $failed[] = "$table.$column — " . $ex->getMessage();
    }
}

$pageTitle = 'Database Migration';
$navActive = '';
require __DIR__ . '/inc/layout-top.php';
?>

<section class="admin-panel">
    <header class="admin-panel-head">
        <h2>Schema Update</h2>
        <a href="<?= portal_url('index.php') ?>" class="btn-admin">Back to Dashboard</a>
    </header>

    <?php if ($applied): ?>
        <div class="alert is-ok">
            <strong>Added <?= count($applied) ?> column(s):</strong>
            <?= e(implode(', ', $applied)) ?>
        </div>
    <?php endif; ?>

    <?php if ($failed): ?>
        <?php foreach ($failed as $f): ?>
            <div class="alert is-error"><?= e($f) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!$applied && !$failed): ?>
        <div class="alert is-ok"><strong>Everything is up to date.</strong> No changes were needed.</div>
    <?php endif; ?>

    <?php if ($skipped): ?>
        <p class="panel-lead">Already present: <?= e(implode(', ', $skipped)) ?>.</p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
