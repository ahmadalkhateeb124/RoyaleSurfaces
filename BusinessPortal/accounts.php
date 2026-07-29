<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $action = (string) ($_POST['action'] ?? '');
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'status' && $id) {
        $status = (string) ($_POST['status'] ?? '');
        if (in_array($status, ['pending', 'active', 'suspended', 'rejected'], true)) {
            $pdo->prepare(
                'UPDATE trade_accounts SET status = ?, approved_at = IF(? = "active" AND approved_at IS NULL, NOW(), approved_at)
                 WHERE id = ?'
            )->execute([$status, $status, $id]);

            flash('ok', match ($status) {
                'active'    => 'Account approved — they can sign in now.',
                'suspended' => 'Account suspended. Sign-in is blocked until you reactivate it.',
                'rejected'  => 'Application rejected.',
                default     => 'Account set back to pending.',
            });
        }
    } elseif ($action === 'notes' && $id) {
        $pdo->prepare('UPDATE trade_accounts SET admin_notes = ? WHERE id = ?')
            ->execute([trim((string) ($_POST['admin_notes'] ?? '')), $id]);
        flash('ok', 'Notes saved.');
    } elseif ($action === 'delete' && $id) {
        // Orders cascade with the account — that is intentional, an account's
        // history has no meaning without the account.
        $pdo->prepare('DELETE FROM trade_accounts WHERE id = ?')->execute([$id]);
        flash('ok', 'Account and its request history deleted.');
    }

    header('Location: ' . portal_url('accounts.php?' . http_build_query(array_filter(['box' => $_POST['box'] ?? '']))));
    exit;
}

$box = in_array($_GET['box'] ?? '', ['pending', 'active', 'suspended', 'rejected'], true)
    ? (string) $_GET['box'] : 'all';
$q = trim((string) ($_GET['q'] ?? ''));

$where = [];
$args  = [];
if ($box !== 'all') {
    $where[] = 'a.status = ?';
    $args[] = $box;
}
if ($q !== '') {
    $where[] = '(a.company LIKE ? OR a.contact_name LIKE ? OR a.email LIKE ?)';
    array_push($args, "%$q%", "%$q%", "%$q%");
}
$sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$st = $pdo->prepare(
    "SELECT a.*, COUNT(o.id) AS order_count, MAX(o.created_at) AS last_order
     FROM trade_accounts a LEFT JOIN orders o ON o.account_id = a.id
     $sql GROUP BY a.id ORDER BY FIELD(a.status,'pending','active','suspended','rejected'), a.created_at DESC"
);
$st->execute($args);
$rows = $st->fetchAll();

$counts = [];
foreach (['all', 'pending', 'active', 'suspended', 'rejected'] as $k) {
    $counts[$k] = (int) ($k === 'all'
        ? $pdo->query('SELECT COUNT(*) FROM trade_accounts')->fetchColumn()
        : $pdo->query("SELECT COUNT(*) FROM trade_accounts WHERE status = '$k'")->fetchColumn());
}

$pageTitle = 'Trade Accounts';
$navActive = 'accounts';
require __DIR__ . '/inc/layout-top.php';
?>

<nav class="box-tabs" aria-label="Filter accounts">
    <?php foreach (['all' => 'All', 'pending' => 'Pending', 'active' => 'Active',
                    'suspended' => 'Suspended', 'rejected' => 'Rejected'] as $key => $label): ?>
        <a href="<?= portal_url('accounts.php' . ($key === 'all' ? '' : '?box=' . $key)) ?>"
            class="box-tab<?= $box === $key ? ' active' : '' ?>">
            <?= e($label) ?>
            <?php if ($counts[$key] > 0): ?>
                <span class="box-count<?= $key === 'pending' ? ' is-alert' : '' ?>"><?= $counts[$key] ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>

<form method="get" class="filter-row">
    <input type="hidden" name="box" value="<?= e($box) ?>" />
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search company, contact or email…" />
    <button type="submit" class="btn-admin">Search</button>
    <?php if ($q): ?>
        <a href="<?= portal_url('accounts.php?box=' . $box) ?>" class="btn-admin is-ghost">Reset</a>
    <?php endif; ?>
</form>

<p class="result-count"><?= count($rows) ?> account<?= count($rows) === 1 ? '' : 's' ?></p>

<?php if (!$rows): ?>
    <div class="empty-state">
        <p><?= $box === 'pending' ? 'No applications waiting.' : 'No accounts here.' ?></p>
    </div>
<?php else: ?>
    <div class="msg-list">
        <?php foreach ($rows as $a): ?>
            <details class="msg<?= $a['status'] === 'pending' ? ' is-unread' : '' ?>">
                <summary>
                    <span class="msg-who">
                        <strong><?= e($a['company']) ?></strong>
                        <span class="msg-co"><?= e($a['contact_name']) ?><?= $a['city'] !== '' ? ' · ' . e($a['city']) : '' ?></span>
                    </span>
                    <span class="msg-snippet"><?= e($a['email']) ?></span>
                    <span class="msg-meta">
                        <?php if ($a['order_count'] > 0): ?>
                            <span class="pill"><?= (int) $a['order_count'] ?> request<?= $a['order_count'] == 1 ? '' : 's' ?></span>
                        <?php endif; ?>
                        <span class="pill is-<?= $a['status'] === 'active' ? 'live' : 'draft' ?>"><?= e($a['status']) ?></span>
                        <time><?= date('M j, Y', strtotime($a['created_at'])) ?></time>
                    </span>
                </summary>

                <div class="msg-body">
                    <dl class="msg-fields">
                        <div><dt>Email</dt><dd><a href="mailto:<?= e($a['email']) ?>"><?= e($a['email']) ?></a></dd></div>
                        <div><dt>Phone</dt><dd><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $a['phone'])) ?>"><?= e($a['phone']) ?></a></dd></div>
                        <?php if ($a['tax_id'] !== ''): ?>
                            <div><dt>Resale / Tax ID</dt><dd><?= e($a['tax_id']) ?></dd></div>
                        <?php endif; ?>
                        <div><dt>Applied</dt><dd><?= date('M j, Y', strtotime($a['created_at'])) ?></dd></div>
                        <div><dt>Approved</dt><dd><?= $a['approved_at'] ? date('M j, Y', strtotime($a['approved_at'])) : '—' ?></dd></div>
                        <div><dt>Last sign-in</dt><dd><?= $a['last_login_at'] ? date('M j, Y', strtotime($a['last_login_at'])) : 'Never' ?></dd></div>
                        <div><dt>Requests</dt><dd>
                            <?php if ($a['order_count'] > 0): ?>
                                <a href="<?= portal_url('orders.php?account=' . $a['id']) ?>"><?= (int) $a['order_count'] ?> — view</a>
                            <?php else: ?>None<?php endif; ?>
                        </dd></div>
                    </dl>

                    <form method="post" style="margin-bottom:16px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="notes" />
                        <input type="hidden" name="id" value="<?= $a['id'] ?>" />
                        <input type="hidden" name="box" value="<?= e($box) ?>" />
                        <div class="field" style="margin-bottom:10px;">
                            <label for="n<?= $a['id'] ?>">Internal notes <span class="hint">only you see these</span></label>
                            <textarea id="n<?= $a['id'] ?>" name="admin_notes" rows="2"><?= e($a['admin_notes'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn-admin is-small">Save Notes</button>
                    </form>

                    <div class="msg-actions">
                        <?php
                        $moves = match ($a['status']) {
                            'pending'   => [['active', 'Approve', 'is-primary'], ['rejected', 'Reject', '']],
                            'active'    => [['suspended', 'Suspend', '']],
                            'suspended' => [['active', 'Reactivate', 'is-primary']],
                            default     => [['active', 'Approve', 'is-primary'], ['pending', 'Back to Pending', '']],
                        };
                        foreach ($moves as [$to, $label, $cls]): ?>
                            <form method="post" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="status" />
                                <input type="hidden" name="status" value="<?= $to ?>" />
                                <input type="hidden" name="id" value="<?= $a['id'] ?>" />
                                <input type="hidden" name="box" value="<?= e($box) ?>" />
                                <button type="submit" class="btn-admin is-small <?= $cls ?>"><?= $label ?></button>
                            </form>
                        <?php endforeach; ?>

                        <a href="mailto:<?= e($a['email']) ?>" class="btn-admin is-small">Email</a>

                        <form method="post" class="inline-form"
                            data-confirm-title="Delete this account?"
                            data-confirm="<?= e($a['company']) ?> and all <?= (int) $a['order_count'] ?> of their requests will be permanently removed. Suspending is usually what you want instead.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="id" value="<?= $a['id'] ?>" />
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
