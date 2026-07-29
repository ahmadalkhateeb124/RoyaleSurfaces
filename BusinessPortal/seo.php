<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/../inc/posts.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'sitemap') {
    csrf_check();
    $r = regenerate_sitemap();

    if ($r['error']) {
        flash('error', 'Sitemap not written: ' . e($r['error']));
    } else {
        $msg = 'Sitemap regenerated.';
        if ($r['pinged']) {
            $msg .= ' Notified ' . e(implode(', ', $r['pinged'])) . '.';
        }
        flash('ok', $msg);
    }
    header('Location: ' . portal_url('seo.php'));
    exit;
}

$sitemapFile = rtrim($base_path, '/') . '/sitemap.xml';
$sitemapAge  = is_file($sitemapFile) ? filemtime($sitemapFile) : null;
$urlCount    = $sitemapAge ? substr_count((string) file_get_contents($sitemapFile), '<loc>') : 0;

// ── Content health checks ────────────────────────────────────────────────────
$issues = [];

$missingExcerpt = (int) $pdo->query(
    "SELECT COUNT(*) FROM posts WHERE status='published' AND (excerpt IS NULL OR CHAR_LENGTH(excerpt) < 60)"
)->fetchColumn();
if ($missingExcerpt) {
    $issues[] = [
        'level' => 'warn',
        'text'  => "$missingExcerpt published article(s) have an excerpt under 60 characters. Search engines use the excerpt as the description — short ones get rewritten or truncated.",
        'link'  => portal_url('posts.php?status=published'),
        'cta'   => 'Review articles',
    ];
}

$longExcerpt = (int) $pdo->query(
    "SELECT COUNT(*) FROM posts WHERE status='published' AND CHAR_LENGTH(excerpt) > 165"
)->fetchColumn();
if ($longExcerpt) {
    $issues[] = [
        'level' => 'warn',
        'text'  => "$longExcerpt article excerpt(s) run past 165 characters and will be cut off in results.",
        'link'  => portal_url('posts.php?status=published'),
        'cta'   => 'Review articles',
    ];
}

$noImage = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE image = '' OR image IS NULL")->fetchColumn()
    + (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE image = '' OR image IS NULL")->fetchColumn();
if ($noImage) {
    $issues[] = [
        'level' => 'warn',
        'text'  => "$noImage item(s) have no image. Social shares fall back to a generic picture without one.",
        'link'  => portal_url('posts.php'),
        'cta'   => 'Review content',
    ];
}

$drafts = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn();
if ($drafts) {
    $issues[] = [
        'level' => 'info',
        'text'  => "$drafts article(s) are still drafts — they are excluded from the site and the sitemap.",
        'link'  => portal_url('posts.php?status=draft'),
        'cta'   => 'View drafts',
    ];
}

if (str_contains(SITE_DOMAIN, 'royalesurfaces.com') && str_contains($base_url, 'localhost')) {
    $issues[] = [
        'level' => 'info',
        'text'  => 'You are on localhost. Search-engine pings are skipped until the site runs on its real domain.',
    ];
}

$counts = [
    'Published articles' => (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn(),
    'Published slabs'    => (int) $pdo->query("SELECT COUNT(*) FROM slabs WHERE status='published'")->fetchColumn(),
    'Published projects' => (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE status='published'")->fetchColumn(),
];

$pageTitle = 'SEO';
$navActive = 'seo';
require __DIR__ . '/inc/layout-top.php';
?>

<section class="admin-panel">
    <header class="admin-panel-head">
        <h2>Sitemap</h2>
        <form method="post" class="inline-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="sitemap" />
            <button type="submit" class="btn-admin is-primary">Regenerate Now</button>
        </form>
    </header>

    <p class="panel-lead">
        Your sitemap is generated live at
        <a href="<?= $base_url ?>sitemap.xml" target="_blank" rel="noopener"><?= e(rtrim($base_url, '/')) ?>/sitemap.xml</a>,
        so it is never stale. Publishing or deleting content also writes a static copy and — on the live domain —
        notifies Google and Bing.
    </p>

    <dl class="kv">
        <div>
            <dt>Static copy written</dt>
            <dd><?= $sitemapAge ? date('M j, Y \a\t g:i A', $sitemapAge) : 'Not yet — press Regenerate' ?></dd>
        </div>
        <div>
            <dt>URLs listed</dt>
            <dd><?= $urlCount ?: '—' ?></dd>
        </div>
        <div>
            <dt>robots.txt</dt>
            <dd><a href="<?= $base_url ?>robots.txt" target="_blank" rel="noopener">View</a></dd>
        </div>
    </dl>
</section>

<section class="admin-panel">
    <header class="admin-panel-head">
        <h2>Content Health</h2>
    </header>

    <?php if (!$issues): ?>
        <p class="empty">Nothing needs attention — every published item has an image and a well-sized excerpt.</p>
    <?php else: ?>
        <ul class="issue-list">
            <?php foreach ($issues as $i): ?>
                <li class="issue is-<?= e($i['level']) ?>">
                    <span><?= e($i['text']) ?></span>
                    <?php if (!empty($i['link'])): ?>
                        <a href="<?= e($i['link']) ?>" class="btn-admin is-small"><?= e($i['cta']) ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<section class="admin-panel">
    <header class="admin-panel-head">
        <h2>Indexed Content</h2>
    </header>
    <dl class="kv">
        <?php foreach ($counts as $label => $n): ?>
            <div>
                <dt><?= e($label) ?></dt>
                <dd><?= $n ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>

    <p class="panel-lead" style="margin-top:20px;">
        Next steps outside this panel: submit the sitemap once in
        <a href="https://search.google.com/search-console" target="_blank" rel="noopener">Google Search Console</a>,
        and keep the business details in <code>inc/site.php</code> matching your Google Business Profile exactly —
        name, address and phone must be identical for local rankings.
    </p>
</section>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
