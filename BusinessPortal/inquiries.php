<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/../inc/spam.php';

// ── Actions ──────────────────────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $action = (string) ($_POST['action'] ?? '');
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $pdo->prepare('DELETE FROM inquiries WHERE id = ?')->execute([$id]);
        flash('ok', 'Message deleted.');
    } elseif ($action === 'status' && $id) {
        $status = (string) ($_POST['status'] ?? 'read');
        if (in_array($status, ['new', 'read', 'replied', 'archived'], true)) {
            $pdo->prepare('UPDATE inquiries SET status = ? WHERE id = ?')->execute([$status, $id]);
            flash('ok', 'Marked as ' . $status . '.');
        }
    } elseif ($action === 'not_spam' && $id) {
        // Rescuing a false positive: clear the flag and put it back in the inbox.
        $pdo->prepare('UPDATE inquiries SET is_spam = 0, status = "new" WHERE id = ?')->execute([$id]);
        flash('ok', 'Moved back to the inbox.');
    } elseif ($action === 'mark_spam' && $id) {
        $pdo->prepare('UPDATE inquiries SET is_spam = 1 WHERE id = ?')->execute([$id]);
        flash('ok', 'Moved to spam.');
    } elseif ($action === 'empty_spam') {
        $n = $pdo->exec('DELETE FROM inquiries WHERE is_spam = 1');
        flash('ok', 'Deleted ' . (int) $n . ' spam message(s).');
    }

    header('Location: ' . portal_url('inquiries.php?' . http_build_query(array_filter([
        'box' => $_POST['box'] ?? '',
    ]))));
    exit;
}

// ── Listing ──────────────────────────────────────────────────────────────────
$box  = ($_GET['box'] ?? '') === 'spam' ? 'spam' : (($_GET['box'] ?? '') === 'archived' ? 'archived' : 'inbox');
$q    = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$per  = 20;

$where = match ($box) {
    'spam'     => ['is_spam = 1'],
    'archived' => ['is_spam = 0', "status = 'archived'"],
    default    => ['is_spam = 0', "status <> 'archived'"],
};
$args = [];
if ($q !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR company LIKE ? OR message LIKE ?)';
    array_push($args, "%$q%", "%$q%", "%$q%", "%$q%");
}
$sql = ' WHERE ' . implode(' AND ', $where);

$countSt = $pdo->prepare("SELECT COUNT(*) FROM inquiries$sql");
$countSt->execute($args);
$total = (int) $countSt->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page  = min($page, $pages);

$st = $pdo->prepare("SELECT * FROM inquiries$sql ORDER BY created_at DESC LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($args);
$rows = $st->fetchAll();

$counts = [
    'inbox'    => (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE is_spam = 0 AND status <> 'archived'")->fetchColumn(),
    'unread'   => (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE is_spam = 0 AND status = 'new'")->fetchColumn(),
    'spam'     => (int) $pdo->query('SELECT COUNT(*) FROM inquiries WHERE is_spam = 1')->fetchColumn(),
    'archived' => (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE is_spam = 0 AND status = 'archived'")->fetchColumn(),
];

$pageTitle = 'Inquiries';
$navActive = 'inquiries';
require __DIR__ . '/inc/layout-top.php';
?>

<nav class="box-tabs" aria-label="Mailboxes">
    <?php
    $boxes = [
        'inbox'    => ['label' => 'Inbox',    'icon' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>'],
        'archived' => ['label' => 'Archived', 'icon' => '<polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>'],
        'spam'     => ['label' => 'Spam',     'icon' => '<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>'],
    ];
    foreach ($boxes as $key => $b): ?>
        <a href="<?= portal_url('inquiries.php?box=' . $key) ?>" class="box-tab<?= $box === $key ? ' active' : '' ?>"
            <?= $box === $key ? 'aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><?= $b['icon'] ?></svg>
            <?= e($b['label']) ?>
            <?php if ($counts[$key] > 0): ?>
                <span class="box-count<?= $key === 'inbox' && $counts['unread'] > 0 ? ' is-alert' : '' ?>">
                    <?= $counts[$key] ?>
                </span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>

<form method="get" class="filter-row">
    <input type="hidden" name="box" value="<?= e($box) ?>" />
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email, company or message…" />
    <button type="submit" class="btn-admin">Search</button>
    <?php if ($q): ?>
        <a href="<?= portal_url('inquiries.php?box=' . $box) ?>" class="btn-admin is-ghost">Reset</a>
    <?php endif; ?>

    <?php if ($box === 'spam' && $counts['spam'] > 0): ?>
        <span style="flex:1"></span>
    <?php endif; ?>
</form>

<?php if ($box === 'spam' && $counts['spam'] > 0): ?>
    <form method="post" class="inline-form" data-confirm-title="Empty the spam folder?"
        data-confirm="All <?= $counts['spam'] ?> message(s) in Spam will be deleted. This cannot be undone."
        data-confirm-label="Delete All"
        style="margin-bottom:14px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="empty_spam" />
        <input type="hidden" name="box" value="spam" />
        <button type="submit" class="btn-admin is-small">Empty Spam</button>
    </form>
<?php endif; ?>

<p class="result-count">
    <?= $total ?> message<?= $total === 1 ? '' : 's' ?>
    <?php if ($box === 'inbox' && $counts['unread'] > 0): ?> · <?= $counts['unread'] ?> unread<?php endif; ?>
</p>

<?php if (!$rows): ?>
    <div class="empty-state">
        <p><?= $box === 'spam' ? 'No spam caught — nice.' : 'No messages here yet.' ?></p>
    </div>
<?php else: ?>
    <div class="msg-list">
        <?php foreach ($rows as $r): ?>
            <details class="msg<?= $r['status'] === 'new' && !$r['is_spam'] ? ' is-unread' : '' ?>">
                <summary>
                    <span class="msg-who">
                        <strong><?= e($r['name']) ?></strong>
                        <?php if ($r['company'] !== ''): ?>
                            <span class="msg-co"><?= e($r['company']) ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="msg-snippet"><?= e(mb_substr($r['message'], 0, 90)) ?><?= mb_strlen($r['message']) > 90 ? '…' : '' ?></span>
                    <span class="msg-meta">
                        <?php if ($r['is_spam']): ?>
                            <span class="pill is-draft">spam <?= (int) $r['spam_score'] ?></span>
                        <?php elseif ($r['status'] === 'new'): ?>
                            <span class="pill is-live">new</span>
                        <?php elseif ($r['status'] === 'replied'): ?>
                            <span class="pill">replied</span>
                        <?php endif; ?>
                        <time datetime="<?= e($r['created_at']) ?>"><?= date('M j, g:i A', strtotime($r['created_at'])) ?></time>
                    </span>
                </summary>

                <div class="msg-body">
                    <dl class="msg-fields">
                        <div><dt>Email</dt><dd><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></dd></div>
                        <div><dt>Phone</dt><dd><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $r['phone'])) ?>"><?= e($r['phone']) ?></a></dd></div>
                        <?php if ($r['subject'] !== ''): ?>
                            <div><dt>Material</dt><dd><?= e($r['subject']) ?></dd></div>
                        <?php endif; ?>
                        <div><dt>Received</dt><dd><?= date('M j, Y \a\t g:i A', strtotime($r['created_at'])) ?></dd></div>
                        <div><dt>Emailed</dt><dd><?= $r['emailed'] ? 'Yes' : 'No — stored only' ?></dd></div>
                        <div><dt>IP</dt><dd><?= e($r['ip']) ?></dd></div>
                    </dl>

                    <p class="msg-text"><?= nl2br(e($r['message'])) ?></p>

                    <?php if ($r['spam_reason'] !== ''): ?>
                        <p class="msg-flags">
                            <strong>Filter score <?= (int) $r['spam_score'] ?>/<?= SPAM_THRESHOLD ?>:</strong>
                            <?= e($r['spam_reason']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="msg-actions">
                        <a href="mailto:<?= e($r['email']) ?>?subject=<?= rawurlencode('Re: your enquiry — ' . site_name()) ?>"
                            class="btn-admin is-small is-primary">Reply by Email</a>

                        <?php if (!$r['is_spam']): ?>
                            <?php foreach (['replied' => 'Mark Replied', 'archived' => 'Archive'] as $s => $label): ?>
                                <?php if ($r['status'] !== $s): ?>
                                    <form method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="status" />
                                        <input type="hidden" name="status" value="<?= $s ?>" />
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                                        <input type="hidden" name="box" value="<?= e($box) ?>" />
                                        <button type="submit" class="btn-admin is-small"><?= $label ?></button>
                                    </form>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <form method="post" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="mark_spam" />
                                <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                                <input type="hidden" name="box" value="<?= e($box) ?>" />
                                <button type="submit" class="btn-admin is-small">Move to Spam</button>
                            </form>
                        <?php else: ?>
                            <form method="post" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="not_spam" />
                                <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                                <input type="hidden" name="box" value="spam" />
                                <button type="submit" class="btn-admin is-small">Not Spam</button>
                            </form>
                        <?php endif; ?>

                        <form method="post" class="inline-form" data-confirm-title="Delete this message?"
                            data-confirm="From <?= e($r['name']) ?>. It will be removed permanently — reply first if you still need to.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="id" value="<?= $r['id'] ?>" />
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

    <?php
    $qs = fn(int $p) => portal_url('inquiries.php?' . http_build_query(array_filter(
        ['box' => $box, 'q' => $q, 'page' => $p > 1 ? $p : null]
    )));
    require __DIR__ . '/inc/pager.php';
    ?>
<?php endif; ?>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
