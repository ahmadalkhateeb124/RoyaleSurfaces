<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/../inc/trade.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $action = (string) ($_POST['action'] ?? '');
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'status' && $id) {
        $status = (string) ($_POST['status'] ?? '');
        if (in_array($status, ['new', 'quoted', 'confirmed', 'ready', 'completed', 'cancelled'], true)) {
            // Confirming takes the slabs off the floor; cancelling puts them
            // back. Refuse rather than oversell if two traders raced for the
            // same slabs while the request sat in the queue.
            $short = in_array($status, ORDER_HOLDS_STOCK, true) ? order_stock_shortfall($pdo, $id) : [];

            if ($short) {
                $lines = array_map(
                    fn($x) => $x['name'] . ' (asked for ' . $x['want'] . ', ' . $x['have'] . ' left)',
                    $short
                );
                flash('error', 'Not enough stock to confirm this request — ' . implode('; ', $lines)
                    . '. Update the count on the inventory page, or cancel the request.');
            } else {
                $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
                order_sync_stock($pdo, $id, $status);

                [$label] = order_status_label($status);
                $moved = in_array($status, ORDER_HOLDS_STOCK, true)
                    ? ' Stock has been reduced.'
                    : ($status === 'cancelled' ? ' Any reserved slabs are back in stock.' : '');
                flash('ok', 'Request marked as ' . strtolower($label) . '.' . $moved);
            }
        }
    } elseif ($action === 'notes' && $id) {
        $pdo->prepare('UPDATE orders SET admin_notes = ? WHERE id = ?')
            ->execute([trim((string) ($_POST['admin_notes'] ?? '')), $id]);
        flash('ok', 'Notes saved.');
    } elseif ($action === 'delete' && $id) {
        // Put the slabs back before the order disappears, otherwise they stay
        // deducted forever with nothing left to explain why.
        order_sync_stock($pdo, $id, 'cancelled');
        $pdo->prepare('DELETE FROM orders WHERE id = ?')->execute([$id]);
        flash('ok', 'Request deleted.');
    }

    header('Location: ' . portal_url('orders.php?' . http_build_query(array_filter([
        'box' => $_POST['box'] ?? '', 'account' => $_POST['account'] ?? '',
    ]))));
    exit;
}

$box     = (string) ($_GET['box'] ?? 'open');
$account = (int) ($_GET['account'] ?? 0);
$q       = trim((string) ($_GET['q'] ?? ''));

$where = [];
$args  = [];
if ($box === 'open') {
    $where[] = "o.status NOT IN ('completed','cancelled')";
} elseif (in_array($box, ['new', 'quoted', 'confirmed', 'ready', 'completed', 'cancelled'], true)) {
    $where[] = 'o.status = ?';
    $args[] = $box;
}
if ($account) {
    $where[] = 'o.account_id = ?';
    $args[] = $account;
}
if ($q !== '') {
    $where[] = '(o.reference LIKE ? OR a.company LIKE ? OR a.contact_name LIKE ?)';
    array_push($args, "%$q%", "%$q%", "%$q%");
}
$sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$st = $pdo->prepare(
    "SELECT o.*, a.company, a.contact_name, a.email, a.phone,
            COUNT(i.id) AS line_count, COALESCE(SUM(i.quantity),0) AS slab_count
     FROM orders o
     JOIN trade_accounts a ON a.id = o.account_id
     LEFT JOIN order_items i ON i.order_id = o.id
     $sql GROUP BY o.id ORDER BY o.created_at DESC"
);
$st->execute($args);
$rows = $st->fetchAll();

$counts = [
    'open' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status NOT IN ('completed','cancelled')")->fetchColumn(),
    'new'  => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn(),
    'all'  => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
];

$pageTitle = 'Requests';
$navActive = 'orders';
require __DIR__ . '/inc/layout-top.php';
?>

<nav class="box-tabs" aria-label="Filter requests">
    <?php foreach (['open' => 'Open', 'new' => 'New', 'quoted' => 'Quoted', 'confirmed' => 'Confirmed',
                    'ready' => 'Ready', 'completed' => 'Completed', 'all' => 'All'] as $key => $label): ?>
        <a href="<?= portal_url('orders.php?box=' . $key) ?>" class="box-tab<?= $box === $key ? ' active' : '' ?>">
            <?= e($label) ?>
            <?php if (isset($counts[$key]) && $counts[$key] > 0): ?>
                <span class="box-count<?= $key === 'new' ? ' is-alert' : '' ?>"><?= $counts[$key] ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>

<form method="get" class="filter-row">
    <input type="hidden" name="box" value="<?= e($box) ?>" />
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search reference or company…" />
    <button type="submit" class="btn-admin">Search</button>
    <?php if ($q || $account): ?>
        <a href="<?= portal_url('orders.php?box=' . $box) ?>" class="btn-admin is-ghost">Reset</a>
    <?php endif; ?>
</form>

<p class="result-count"><?= count($rows) ?> request<?= count($rows) === 1 ? '' : 's' ?></p>

<?php if (!$rows): ?>
    <div class="empty-state">
        <p>Nothing here. Requests arrive the moment a trade account sends one.</p>
    </div>
<?php else: ?>
    <div class="msg-list">
        <?php foreach ($rows as $o): [$label, $tone] = order_status_label($o['status']); ?>
            <details class="msg<?= $o['status'] === 'new' ? ' is-unread' : '' ?>">
                <summary>
                    <span class="msg-who">
                        <strong><?= e($o['reference']) ?></strong>
                        <span class="msg-co"><?= e($o['company']) ?></span>
                    </span>
                    <span class="msg-snippet">
                        <?= (int) $o['line_count'] ?> line<?= $o['line_count'] == 1 ? '' : 's' ?> ·
                        <?= (int) $o['slab_count'] ?> slab<?= $o['slab_count'] == 1 ? '' : 's' ?>
                        <?= $o['notes'] ? ' — ' . e(mb_substr($o['notes'], 0, 50)) : '' ?>
                    </span>
                    <span class="msg-meta">
                        <span class="pill<?= $tone ? ' is-' . $tone : '' ?>"><?= e($label) ?></span>
                        <time><?= date('M j, g:i A', strtotime($o['created_at'])) ?></time>
                    </span>
                </summary>

                <div class="msg-body">
                    <dl class="msg-fields">
                        <div><dt>Company</dt><dd><?= e($o['company']) ?></dd></div>
                        <div><dt>Contact</dt><dd><?= e($o['contact_name']) ?></dd></div>
                        <div><dt>Email</dt><dd><a href="mailto:<?= e($o['email']) ?>"><?= e($o['email']) ?></a></dd></div>
                        <div><dt>Phone</dt><dd><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $o['phone'])) ?>"><?= e($o['phone']) ?></a></dd></div>
                        <div><dt>Sent</dt><dd><?= date('M j, Y \a\t g:i A', strtotime($o['created_at'])) ?></dd></div>
                        <div><dt>Needed by</dt><dd><?= $o['needed_by'] ? date('M j, Y', strtotime($o['needed_by'])) : '—' ?></dd></div>
                        <div><dt>Stock</dt><dd><?= $o['stock_applied']
                            ? 'Deducted from inventory'
                            : 'Not deducted yet — happens when you confirm' ?></dd></div>
                    </dl>

                    <div class="order-scroll">
                        <table class="order-items">
                            <thead><tr><th>Material</th><th>Slabs</th><th>In Stock</th><th>Size</th><th>Notes</th></tr></thead>
                            <tbody>
                                <?php foreach (order_items($pdo, (int) $o['id']) as $i):
                                    $left  = $i['slab_slug'] !== '' ? slab_stock_for($i['slab_slug']) : null;
                                    $tight = $left !== null && !$o['stock_applied'] && (int) $i['quantity'] > $left;
                                ?>
                                    <tr>
                                        <td>
                                            <?php if ($i['slab_slug'] !== ''): ?>
                                                <a href="<?= $base_url ?>slabs/<?= e($i['slab_slug']) ?>" target="_blank"
                                                    rel="noopener" class="order-item-name"><?= e($i['slab_name']) ?></a>
                                            <?php else: ?>
                                                <span class="order-item-name"><?= e($i['slab_name']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= (int) $i['quantity'] ?></strong></td>
                                        <td>
                                            <?php if ($left === null): ?>
                                                <span class="stock-tag">removed</span>
                                            <?php elseif ($tight): ?>
                                                <span class="stock-tag is-out"><?= $left ?> left</span>
                                            <?php else: ?>
                                                <span class="stock-tag"><?= $left ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($i['size_note'] ?: '—') ?></td>
                                        <td><?= e($i['item_notes'] ?: '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($o['notes']): ?>
                        <p class="msg-text" style="margin-top:16px;"><?= nl2br(e($o['notes'])) ?></p>
                    <?php endif; ?>

                    <form method="post" style="margin-top:16px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="notes" />
                        <input type="hidden" name="id" value="<?= $o['id'] ?>" />
                        <input type="hidden" name="box" value="<?= e($box) ?>" />
                        <div class="field" style="margin-bottom:10px;">
                            <label for="an<?= $o['id'] ?>">Internal notes <span class="hint">the trader never sees these</span></label>
                            <textarea id="an<?= $o['id'] ?>" name="admin_notes" rows="2"><?= e($o['admin_notes'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn-admin is-small">Save Notes</button>
                    </form>

                    <div class="msg-actions">
                        <a href="mailto:<?= e($o['email']) ?>?subject=<?= rawurlencode('Your request ' . $o['reference'] . ' — ' . site_name()) ?>"
                            class="btn-admin is-small is-primary">Reply with a Quote</a>

                        <?php
                        $next = [
                            'new' => ['quoted', 'Mark Quoted'], 'quoted' => ['confirmed', 'Mark Confirmed'],
                            'confirmed' => ['ready', 'Mark Ready'], 'ready' => ['completed', 'Mark Completed'],
                        ];
                        if (isset($next[$o['status']])): [$to, $label2] = $next[$o['status']]; ?>
                            <form method="post" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="status" />
                                <input type="hidden" name="status" value="<?= $to ?>" />
                                <input type="hidden" name="id" value="<?= $o['id'] ?>" />
                                <input type="hidden" name="box" value="<?= e($box) ?>" />
                                <button type="submit" class="btn-admin is-small"><?= $label2 ?></button>
                            </form>
                        <?php endif; ?>

                        <?php if ($o['status'] !== 'cancelled'): ?>
                            <form method="post" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="status" />
                                <input type="hidden" name="status" value="cancelled" />
                                <input type="hidden" name="id" value="<?= $o['id'] ?>" />
                                <input type="hidden" name="box" value="<?= e($box) ?>" />
                                <button type="submit" class="btn-admin is-small">Cancel</button>
                            </form>
                        <?php endif; ?>

                        <form method="post" class="inline-form"
                            data-confirm-title="Delete this request?"
                            data-confirm="<?= e($o['reference']) ?> from <?= e($o['company']) ?> will be removed permanently. Cancelling keeps the record.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="id" value="<?= $o['id'] ?>" />
                            <input type="hidden" name="box" value="<?= e($box) ?>" />
                            <button type="submit" class="icon-btn is-danger" aria-label="Delete">
                                <svg viewBox="0 0 24 24">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
