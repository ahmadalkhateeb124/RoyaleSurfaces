<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'stock') {
    csrf_check();
    $id    = (int) ($_POST['id'] ?? 0);
    $stock = max(0, min(9999, (int) ($_POST['stock'] ?? 0)));

    $pdo->prepare('UPDATE slabs SET stock = ? WHERE id = ?')->execute([$stock, $id]);

    $st = $pdo->prepare('SELECT name FROM slabs WHERE id = ?');
    $st->execute([$id]);
    $name = (string) $st->fetchColumn();

    flash('ok', $stock === 0
        ? '“' . $name . '” is now marked sold out.'
        : '“' . $name . '” set to ' . $stock . ' slab' . ($stock === 1 ? '' : 's') . '.');

    header('Location: ' . portal_url('slabs.php?' . http_build_query(array_filter([
        'q' => $_POST['q'] ?? '', 'type' => $_POST['type'] ?? '',
        'status' => $_POST['status'] ?? '', 'page' => $_POST['page'] ?? '',
    ]))));
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);

    $st = $pdo->prepare('SELECT image FROM slabs WHERE id = ?');
    $st->execute([$id]);
    $img = $st->fetchColumn();

    $pdo->prepare('DELETE FROM slabs WHERE id = ?')->execute([$id]);
    delete_upload($img ?: null, rtrim($base_path, '/') . '/assets/uploads');

    flash('ok', 'Slab removed from inventory.');
    header('Location: ' . portal_url('slabs.php'));
    exit;
}

$q      = trim((string) ($_GET['q'] ?? ''));
$type   = (string) ($_GET['type'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$page   = max(1, (int) ($_GET['page'] ?? 1));
$per    = 20;

$where = [];
$args  = [];
if ($q !== '') {
    $where[] = '(name LIKE ? OR origin LIKE ?)';
    $args[] = "%$q%";
    $args[] = "%$q%";
}
if (isset(SITE_MATERIALS[$type])) {
    $where[] = 'type = ?';
    $args[] = $type;
}
if (in_array($status, ['draft', 'published'], true)) {
    $where[] = 'status = ?';
    $args[] = $status;
} elseif ($status === 'soldout') {
    $where[] = 'stock < 1';
} elseif ($status === 'low') {
    $where[] = 'stock BETWEEN 1 AND 2';
}
$sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$countSt = $pdo->prepare("SELECT COUNT(*) FROM slabs$sql");
$countSt->execute($args);
$total = (int) $countSt->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page  = min($page, $pages);

$st = $pdo->prepare("SELECT * FROM slabs$sql ORDER BY sort_order, id LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($args);
$rows = $st->fetchAll();

$pageTitle = 'Slab Inventory';
$navActive = 'slabs';
$pageAction = '<a href="' . portal_url('slab-edit.php') . '" class="btn-admin is-primary">Add Slab</a>';
require __DIR__ . '/inc/layout-top.php';
?>

<form method="get" class="filter-row">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name or origin…" />

    <select name="type">
        <option value="">All materials</option>
        <?php foreach (SITE_MATERIALS as $k => $m): ?>
            <option value="<?= e($k) ?>" <?= $type === $k ? 'selected' : '' ?>><?= e($m['label']) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="status">
        <option value="">All statuses</option>
        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="low" <?= $status === 'low' ? 'selected' : '' ?>>Low stock</option>
        <option value="soldout" <?= $status === 'soldout' ? 'selected' : '' ?>>Sold out</option>
    </select>

    <button type="submit" class="btn-admin">Filter</button>
    <?php if ($q || $type || $status): ?>
        <a href="<?= portal_url('slabs.php') ?>" class="btn-admin is-ghost">Reset</a>
    <?php endif; ?>
</form>

<p class="result-count"><?= $total ?> <?= $total === 1 ? 'slab' : 'slabs' ?></p>

<?php if (!$rows): ?>
    <div class="empty-state">
        <p>No slabs match.</p>
        <a href="<?= portal_url('slab-edit.php') ?>" class="btn-admin is-primary">Add one</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="col-thumb"></th>
                    <th>Name</th>
                    <th>Material</th>
                    <th>Origin</th>
                    <th>Finish</th>
                    <th>Size</th>
                    <th class="col-stock">In Stock</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="col-thumb"><img src="<?= e(image_url($r['image'])) ?>" alt="" loading="lazy" /></td>
                        <td>
                            <a href="<?= portal_url('slab-edit.php?id=' . $r['id']) ?>" class="row-title">
                                <?= e($r['name']) ?>
                            </a>
                            <span class="row-sub"><?= e($r['thickness']) ?></span>
                        </td>
                        <td><?= e(SITE_MATERIALS[$r['type']]['label'] ?? $r['type']) ?></td>
                        <td><?= e($r['origin']) ?></td>
                        <td><?= e($r['finish']) ?></td>
                        <td class="nowrap"><?= e($r['size']) ?></td>
                        <td class="col-stock">
                            <form method="post" class="stock-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="stock" />
                                <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                                <input type="hidden" name="q" value="<?= e($q) ?>" />
                                <input type="hidden" name="type" value="<?= e($type) ?>" />
                                <input type="hidden" name="status" value="<?= e($status) ?>" />
                                <input type="hidden" name="page" value="<?= $page ?>" />
                                <label class="sr-only" for="s<?= $r['id'] ?>">Slabs of <?= e($r['name']) ?></label>
                                <input type="number" id="s<?= $r['id'] ?>" name="stock" min="0" max="9999"
                                    value="<?= (int) $r['stock'] ?>"
                                    class="stock-input<?= (int) $r['stock'] < 1 ? ' is-out' : ((int) $r['stock'] <= 2 ? ' is-low' : '') ?>" />
                                <button type="submit" class="stock-save" aria-label="Save stock for <?= e($r['name']) ?>">Save</button>
                            </form>
                            <?php if ((int) $r['stock'] < 1): ?>
                                <span class="pill is-draft">Sold out</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="pill is-<?= $r['status'] === 'published' ? 'live' : 'draft' ?>">
                                <?= e($r['status']) ?>
                            </span>
                        </td>
                        <td class="col-actions">
                            <a href="<?= portal_url('slab-edit.php?id=' . $r['id']) ?>" class="icon-btn"
                                aria-label="Edit">
                                <svg viewBox="0 0 24 24">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z" />
                                </svg>
                            </a>
                            <form method="post" class="inline-form"
                                data-confirm-title="Remove this slab?"
                                data-confirm="“<?= e($r['name']) ?>” will be removed from the inventory page and its own product page.">
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
    $qs = fn(int $p) => portal_url('slabs.php?' . http_build_query(array_filter(
        ['q' => $q, 'type' => $type, 'status' => $status, 'page' => $p > 1 ? $p : null]
    )));
    require __DIR__ . '/inc/pager.php';
    ?>
<?php endif; ?>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
