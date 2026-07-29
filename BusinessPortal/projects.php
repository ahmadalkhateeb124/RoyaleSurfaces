<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);

    $st = $pdo->prepare('SELECT image FROM projects WHERE id = ?');
    $st->execute([$id]);
    $img = $st->fetchColumn();

    $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
    delete_upload($img ?: null, rtrim($base_path, '/') . '/assets/uploads');
    regenerate_sitemap();

    flash('ok', 'Project removed from the gallery.');
    header('Location: ' . portal_url('projects.php'));
    exit;
}

$q      = trim((string) ($_GET['q'] ?? ''));
$type   = (string) ($_GET['type'] ?? '');
$page   = max(1, (int) ($_GET['page'] ?? 1));
$per    = 18;

$where = [];
$args  = [];
if ($q !== '') {
    $where[] = '(title LIKE ? OR material LIKE ? OR location LIKE ?)';
    array_push($args, "%$q%", "%$q%", "%$q%");
}
if (isset(SITE_MATERIALS[$type])) {
    $where[] = 'type = ?';
    $args[] = $type;
}
$sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$countSt = $pdo->prepare("SELECT COUNT(*) FROM projects$sql");
$countSt->execute($args);
$total = (int) $countSt->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page  = min($page, $pages);

$st = $pdo->prepare("SELECT * FROM projects$sql ORDER BY sort_order, id LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($args);
$rows = $st->fetchAll();

$pageTitle = 'Project Gallery';
$navActive = 'projects';
$pageAction = '<a href="' . portal_url('project-edit.php') . '" class="btn-admin is-primary">Add Project</a>';
require __DIR__ . '/inc/layout-top.php';
?>

<form method="get" class="filter-row">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search title, material or location…" />
    <select name="type">
        <option value="">All materials</option>
        <?php foreach (SITE_MATERIALS as $k => $m): ?>
            <option value="<?= e($k) ?>" <?= $type === $k ? 'selected' : '' ?>><?= e($m['label']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-admin">Filter</button>
    <?php if ($q || $type): ?>
        <a href="<?= portal_url('projects.php') ?>" class="btn-admin is-ghost">Reset</a>
    <?php endif; ?>
</form>

<p class="result-count"><?= $total ?> <?= $total === 1 ? 'project' : 'projects' ?></p>

<?php if (!$rows): ?>
    <div class="empty-state">
        <p>No projects match.</p>
        <a href="<?= portal_url('project-edit.php') ?>" class="btn-admin is-primary">Add one</a>
    </div>
<?php else: ?>
    <!-- A gallery is visual, so this list is a grid of cards rather than a table -->
    <div class="card-grid">
        <?php foreach ($rows as $r): ?>
            <article class="admin-card">
                <a href="<?= portal_url('project-edit.php?id=' . $r['id']) ?>" class="admin-card-img">
                    <img src="<?= e(image_url($r['image'])) ?>" alt="" loading="lazy" />
                    <?php if ($r['is_feature']): ?>
                        <span class="card-flag">Featured</span>
                    <?php endif; ?>
                    <?php if ($r['status'] !== 'published'): ?>
                        <span class="card-flag is-draft">Draft</span>
                    <?php endif; ?>
                </a>
                <div class="admin-card-body">
                    <span class="admin-card-space"><?= e($r['space']) ?></span>
                    <h3><a href="<?= portal_url('project-edit.php?id=' . $r['id']) ?>"><?= e($r['title']) ?></a></h3>
                    <p><?= e($r['material']) ?><?= $r['location'] ? ' · ' . e($r['location']) : '' ?></p>
                    <div class="admin-card-foot">
                        <a href="<?= portal_url('project-edit.php?id=' . $r['id']) ?>" class="btn-admin is-small">Edit</a>
                        <form method="post" class="inline-form"
                            data-confirm-title="Remove this project?"
                            data-confirm="“<?= e($r['title']) ?>” will be deleted from the gallery and the sitemap.">
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
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php
    $qs = fn(int $p) => portal_url('projects.php?' . http_build_query(array_filter(
        ['q' => $q, 'type' => $type, 'page' => $p > 1 ? $p : null]
    )));
    require __DIR__ . '/inc/pager.php';
    ?>
<?php endif; ?>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
