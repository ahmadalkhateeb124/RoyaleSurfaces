<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';

$stats = [];
foreach (['posts' => 'Blog posts', 'slabs' => 'Inventory', 'projects' => 'Projects'] as $table => $label) {
    $stats[$table] = [
        'label'     => $label,
        'total'     => (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn(),
        'published' => (int) $pdo->query("SELECT COUNT(*) FROM `$table` WHERE status = 'published'")->fetchColumn(),
    ];
}

// Inquiry counters for the dashboard.
$inqTotal = $inqUnread = $inqSpam = 0;
$recentInquiries = [];
try {
    $inqTotal  = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE is_spam = 0")->fetchColumn();
    $inqUnread = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE is_spam = 0 AND status = 'new'")->fetchColumn();
    $inqSpam   = (int) $pdo->query('SELECT COUNT(*) FROM inquiries WHERE is_spam = 1')->fetchColumn();
    $recentInquiries = $pdo->query(
        "SELECT id, name, company, email, message, status, created_at
         FROM inquiries WHERE is_spam = 0 ORDER BY created_at DESC LIMIT 5"
    )->fetchAll();
} catch (Throwable $e) { /* run migrate.php to create the table */ }

$recentPosts = $pdo->query(
    'SELECT id, title, status, published_at FROM posts ORDER BY published_at DESC, id DESC LIMIT 5'
)->fetchAll();

$recentProjects = $pdo->query(
    'SELECT id, title, type, status FROM projects ORDER BY id DESC LIMIT 5'
)->fetchAll();

$sitemapFile = rtrim($base_path, '/') . '/sitemap.xml';
$sitemapAge  = is_file($sitemapFile) ? filemtime($sitemapFile) : null;

$pageTitle = 'Dashboard';
$navActive = 'dashboard';
require __DIR__ . '/inc/layout-top.php';
?>

<div class="stat-row">
    <?php foreach ($stats as $key => $s): ?>
        <a href="<?= portal_url($key . '.php') ?>" class="stat-card">
            <span class="stat-card-label"><?= e($s['label']) ?></span>
            <strong><?= $s['total'] ?></strong>
            <span class="stat-card-meta">
                <?= $s['published'] ?> published
                <?php if ($s['total'] - $s['published'] > 0): ?>
                    · <?= $s['total'] - $s['published'] ?> draft
                <?php endif; ?>
            </span>
        </a>
    <?php endforeach; ?>

    <a href="<?= portal_url('inquiries.php') ?>" class="stat-card">
        <span class="stat-card-label">Inquiries</span>
        <strong><?= $inqTotal ?></strong>
        <span class="stat-card-meta">
            <?= $inqUnread ?> unread<?= $inqSpam ? " · $inqSpam spam filtered" : "" ?>
        </span>
    </a>

    <div class="stat-card is-static">
        <span class="stat-card-label">Sitemap</span>
        <strong><?= $sitemapAge ? date('M j', $sitemapAge) : '—' ?></strong>
        <span class="stat-card-meta">
            <?= $sitemapAge ? 'updated ' . date('g:i A', $sitemapAge) : 'not generated yet' ?>
        </span>
    </div>
</div>

<section class="admin-panel">
    <header class="admin-panel-head">
        <h2>Latest Inquiries</h2>
        <a href="<?= portal_url('inquiries.php') ?>" class="btn-admin is-small">Open Inbox</a>
    </header>

    <?php if (!$recentInquiries): ?>
        <p class="empty">No messages yet. They arrive here the moment someone uses the contact form.</p>
    <?php else: ?>
        <ul class="mini-list">
            <?php foreach ($recentInquiries as $m): ?>
                <li>
                    <a href="<?= portal_url('inquiries.php') ?>">
                        <?= e($m['name']) ?><?= $m['company'] !== '' ? ' — ' . e($m['company']) : '' ?>
                        <span class="row-sub"><?= e(mb_substr($m['message'], 0, 70)) ?>…</span>
                    </a>
                    <span class="mini-meta">
                        <?php if ($m['status'] === 'new'): ?><span class="pill is-live">new</span><?php endif; ?>
                        <?= date('M j, g:i A', strtotime($m['created_at'])) ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<div class="panel-row">
    <section class="admin-panel">
        <header class="admin-panel-head">
            <h2>Recent Articles</h2>
            <a href="<?= portal_url('post-edit.php') ?>" class="btn-admin is-small">New Post</a>
        </header>

        <?php if (!$recentPosts): ?>
            <p class="empty">No articles yet. <a href="<?= portal_url('post-edit.php') ?>">Write the first one</a>.</p>
        <?php else: ?>
            <ul class="mini-list">
                <?php foreach ($recentPosts as $p): ?>
                    <li>
                        <a href="<?= portal_url('post-edit.php?id=' . $p['id']) ?>"><?= e($p['title']) ?></a>
                        <span class="mini-meta">
                            <span class="pill is-<?= $p['status'] === 'published' ? 'live' : 'draft' ?>">
                                <?= e($p['status']) ?>
                            </span>
                            <?= date('M j, Y', strtotime($p['published_at'])) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="admin-panel">
        <header class="admin-panel-head">
            <h2>Recent Projects</h2>
            <a href="<?= portal_url('project-edit.php') ?>" class="btn-admin is-small">New Project</a>
        </header>

        <?php if (!$recentProjects): ?>
            <p class="empty">No projects yet. <a href="<?= portal_url('project-edit.php') ?>">Add one</a>.</p>
        <?php else: ?>
            <ul class="mini-list">
                <?php foreach ($recentProjects as $p): ?>
                    <li>
                        <a href="<?= portal_url('project-edit.php?id=' . $p['id']) ?>"><?= e($p['title']) ?></a>
                        <span class="mini-meta">
                            <span class="pill is-<?= $p['status'] === 'published' ? 'live' : 'draft' ?>">
                                <?= e($p['status']) ?>
                            </span>
                            <?= e(SITE_MATERIALS[$p['type']]['label'] ?? $p['type']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<section class="admin-panel">
    <header class="admin-panel-head">
        <h2>Quick Actions</h2>
    </header>
    <div class="quick-grid">
        <a href="<?= portal_url('post-edit.php') ?>" class="quick-card">
            <strong>Write an article</strong>
            <span>Publish to the blog and the sitemap in one step.</span>
        </a>
        <a href="<?= portal_url('slab-edit.php') ?>" class="quick-card">
            <strong>Add a slab</strong>
            <span>Appears in inventory and its material filter immediately.</span>
        </a>
        <a href="<?= portal_url('project-edit.php') ?>" class="quick-card">
            <strong>Add a project</strong>
            <span>Upload finished work to the gallery.</span>
        </a>
        <a href="<?= portal_url('seo.php') ?>" class="quick-card">
            <strong>Rebuild the sitemap</strong>
            <span>Regenerate and notify search engines.</span>
        </a>
    </div>
</section>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
