<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/../inc/posts.php';

// ── Delete ───────────────────────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);

    $st = $pdo->prepare('SELECT image FROM posts WHERE id = ?');
    $st->execute([$id]);
    $img = $st->fetchColumn();

    $pdo->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
    delete_upload($img ?: null, rtrim($base_path, '/') . '/assets/uploads');
    regenerate_sitemap();

    flash('ok', 'Article deleted.');
    header('Location: ' . portal_url('posts.php'));
    exit;
}

// ── Filters + pagination ─────────────────────────────────────────────────────
$q      = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$cat    = (string) ($_GET['cat'] ?? '');
$page   = max(1, (int) ($_GET['page'] ?? 1));
$per    = 15;

$where = [];
$args  = [];
if ($q !== '') {
    $where[] = '(title LIKE ? OR slug LIKE ?)';
    $args[] = "%$q%";
    $args[] = "%$q%";
}
if (in_array($status, ['draft', 'published'], true)) {
    $where[] = 'status = ?';
    $args[] = $status;
}
if (isset(BLOG_CATEGORIES[$cat])) {
    $where[] = 'category = ?';
    $args[] = $cat;
}
$sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$countSt = $pdo->prepare("SELECT COUNT(*) FROM posts$sql");
$countSt->execute($args);
$total = (int) $countSt->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page  = min($page, $pages);

$st = $pdo->prepare(
    "SELECT id, slug, title, category, image, status, published_at, read_minutes
     FROM posts$sql ORDER BY published_at DESC, id DESC LIMIT $per OFFSET " . (($page - 1) * $per)
);
$st->execute($args);
$rows = $st->fetchAll();

$pageTitle = 'Blog';
$navActive = 'posts';
$pageAction = '<a href="' . portal_url('post-edit.php') . '" class="btn-admin is-primary">New Post</a>';
require __DIR__ . '/inc/layout-top.php';
?>

<form method="get" class="filter-row">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search title or slug…" />

    <select name="status">
        <option value="">All statuses</option>
        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
    </select>

    <select name="cat">
        <option value="">All categories</option>
        <?php foreach (BLOG_CATEGORIES as $k => $label): ?>
            <option value="<?= e($k) ?>" <?= $cat === $k ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn-admin">Filter</button>
    <?php if ($q || $status || $cat): ?>
        <a href="<?= portal_url('posts.php') ?>" class="btn-admin is-ghost">Reset</a>
    <?php endif; ?>
</form>

<p class="result-count"><?= $total ?> <?= $total === 1 ? 'article' : 'articles' ?></p>

<?php if (!$rows): ?>
    <div class="empty-state">
        <p>No articles match.</p>
        <a href="<?= portal_url('post-edit.php') ?>" class="btn-admin is-primary">Write one</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="col-thumb"></th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="col-thumb">
                            <img src="<?= e(image_url($r['image'])) ?>" alt="" loading="lazy" />
                        </td>
                        <td>
                            <a href="<?= portal_url('post-edit.php?id=' . $r['id']) ?>" class="row-title">
                                <?= e($r['title']) ?>
                            </a>
                            <span class="row-sub">/blog/<?= e($r['slug']) ?> · <?= $r['read_minutes'] ?> min</span>
                        </td>
                        <td><?= e(BLOG_CATEGORIES[$r['category']] ?? $r['category']) ?></td>
                        <td class="nowrap"><?= date('M j, Y', strtotime($r['published_at'])) ?></td>
                        <td>
                            <span class="pill is-<?= $r['status'] === 'published' ? 'live' : 'draft' ?>">
                                <?= e($r['status']) ?>
                            </span>
                        </td>
                        <td class="col-actions">
                            <a href="<?= $base_url ?>blog/<?= e($r['slug']) ?>" target="_blank" rel="noopener"
                                class="icon-btn" aria-label="View">
                                <svg viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </a>
                            <a href="<?= portal_url('post-edit.php?id=' . $r['id']) ?>" class="icon-btn"
                                aria-label="Edit">
                                <svg viewBox="0 0 24 24">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z" />
                                </svg>
                            </a>
                            <form method="post" class="inline-form" data-confirm-title="Delete this article?"
                                data-confirm="“<?= e($r['title']) ?>” will be removed from the site and the sitemap. This cannot be undone.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                                <button type="submit" class="icon-btn is-danger" aria-label="Delete">
                                    <svg viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    $qs = fn(int $p) => portal_url('posts.php?' . http_build_query(array_filter(
        ['q' => $q, 'status' => $status, 'cat' => $cat, 'page' => $p > 1 ? $p : null]
    )));
    require __DIR__ . '/inc/pager.php';
    ?>
<?php endif; ?>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
