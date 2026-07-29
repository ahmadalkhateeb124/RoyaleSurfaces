<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';

// FAQs are two short fields each, so everything is edited inline on this one
// page — a separate edit screen per question would be needless clicking.

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $action = (string) ($_POST['action'] ?? '');
    $id     = (int) ($_POST['id'] ?? 0);

    $question = trim((string) ($_POST['question'] ?? ''));
    $answer   = trim((string) ($_POST['answer'] ?? ''));
    $group    = trim((string) ($_POST['group_name'] ?? '')) ?: 'General';
    $sort     = (int) ($_POST['sort_order'] ?? 0);
    $onHome   = isset($_POST['show_on_home']) ? 1 : 0;
    $status   = ($_POST['status'] ?? '') === 'draft' ? 'draft' : 'published';

    if ($action === 'create' || $action === 'update') {
        if (mb_strlen($question) < 5) {
            flash('error', 'The question needs to be at least 5 characters.');
        } elseif (mb_strlen($answer) < 10) {
            flash('error', 'The answer needs to be at least 10 characters.');
        } elseif ($action === 'create') {
            // New questions go to the end of their group by default.
            if ($sort === 0) {
                $st = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM faqs WHERE group_name = ?');
                $st->execute([$group]);
                $sort = (int) $st->fetchColumn();
            }
            $pdo->prepare(
                'INSERT INTO faqs (question, answer, group_name, sort_order, show_on_home, status)
                 VALUES (?,?,?,?,?,?)'
            )->execute([$question, $answer, $group, $sort, $onHome, $status]);
            flash('ok', 'Question added.');
        } else {
            $pdo->prepare(
                'UPDATE faqs SET question=?, answer=?, group_name=?, sort_order=?, show_on_home=?, status=?
                 WHERE id=?'
            )->execute([$question, $answer, $group, $sort, $onHome, $status, $id]);
            flash('ok', 'Question updated.');
        }
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare('DELETE FROM faqs WHERE id = ?')->execute([$id]);
        flash('ok', 'Question deleted.');
    } elseif ($action === 'move' && $id) {
        // Swap sort_order with the neighbour in the same group.
        $dir = ($_POST['dir'] ?? '') === 'up' ? 'up' : 'down';
        $st = $pdo->prepare('SELECT group_name, sort_order FROM faqs WHERE id = ?');
        $st->execute([$id]);
        if ($row = $st->fetch()) {
            $cmp = $dir === 'up' ? '<' : '>';
            $ord = $dir === 'up' ? 'DESC' : 'ASC';
            $nb = $pdo->prepare(
                "SELECT id, sort_order FROM faqs
                 WHERE group_name = ? AND sort_order $cmp ? ORDER BY sort_order $ord LIMIT 1"
            );
            $nb->execute([$row['group_name'], $row['sort_order']]);
            if ($other = $nb->fetch()) {
                $up = $pdo->prepare('UPDATE faqs SET sort_order = ? WHERE id = ?');
                $up->execute([$other['sort_order'], $id]);
                $up->execute([$row['sort_order'], $other['id']]);
            }
        }
    }

    header('Location: ' . portal_url('faqs.php'));
    exit;
}

$rows = $pdo->query('SELECT * FROM faqs ORDER BY group_name, sort_order, id')->fetchAll();

$grouped = [];
foreach ($rows as $r) {
    $grouped[$r['group_name']][] = $r;
}

$groupNames = array_keys($grouped);
$onHome = count(array_filter($rows, fn($r) => $r['show_on_home'] && $r['status'] === 'published'));

$pageTitle = 'FAQ';
$navActive = 'faqs';
$pageAction = '<a href="' . $base_url . 'faq" target="_blank" rel="noopener" class="btn-admin is-ghost">View page ↗</a>';
require __DIR__ . '/inc/layout-top.php';
?>

<div class="stat-row" style="grid-template-columns:repeat(3,1fr);">
    <div class="stat-card is-static">
        <span class="stat-card-label">Questions</span>
        <strong><?= count($rows) ?></strong>
        <span class="stat-card-meta"><?= count($grouped) ?> topic<?= count($grouped) === 1 ? '' : 's' ?></span>
    </div>
    <div class="stat-card is-static">
        <span class="stat-card-label">On the homepage</span>
        <strong><?= $onHome ?></strong>
        <span class="stat-card-meta">shown in the Home FAQ block</span>
    </div>
    <div class="stat-card is-static">
        <span class="stat-card-label">Drafts</span>
        <strong><?= count(array_filter($rows, fn($r) => $r['status'] === 'draft')) ?></strong>
        <span class="stat-card-meta">hidden from the site</span>
    </div>
</div>

<!-- ADD -->
<details class="admin-panel faq-new" id="addFaq">
    <summary>
        <span>Add a question</span>
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
    </summary>

    <form method="post" class="faq-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create" />

        <div class="field">
            <label for="new_q">Question</label>
            <input type="text" id="new_q" name="question" required minlength="5"
                placeholder="Do you deliver outside Dallas?" />
        </div>

        <div class="field">
            <label for="new_a">Answer</label>
            <textarea id="new_a" name="answer" rows="4" required minlength="10"
                placeholder="Yes. We deliver across Texas…"></textarea>
        </div>

        <div class="field-row">
            <div class="field">
                <label for="new_g">Topic <span class="hint">groups the question on the FAQ page</span></label>
                <input type="text" id="new_g" name="group_name" list="faqGroups" value="General" />
                <datalist id="faqGroups">
                    <?php foreach ($groupNames as $g): ?>
                        <option value="<?= e($g) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="field">
                <label for="new_s">Status</label>
                <select id="new_s" name="status">
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
        </div>

        <label class="check">
            <input type="checkbox" name="show_on_home" value="1" />
            <span>Also show in the homepage FAQ block</span>
        </label>

        <button type="submit" class="btn-admin is-primary">Add Question</button>
    </form>
</details>

<?php if (!$rows): ?>
    <div class="empty-state">
        <p>No questions yet.</p>
    </div>
<?php else: ?>
    <?php foreach ($grouped as $group => $items): ?>
        <section class="admin-panel">
            <header class="admin-panel-head">
                <h2><?= e($group) ?></h2>
                <span class="result-count" style="margin:0;"><?= count($items) ?> question<?= count($items) === 1 ? '' : 's' ?></span>
            </header>

            <div class="faq-rows">
                <?php foreach ($items as $i => $r): ?>
                    <details class="faq-row<?= $r['status'] === 'draft' ? ' is-draft' : '' ?>">
                        <summary>
                            <span class="faq-row-q"><?= e($r['question']) ?></span>
                            <span class="faq-row-tags">
                                <?php if ($r['show_on_home']): ?>
                                    <span class="pill">home</span>
                                <?php endif; ?>
                                <?php if ($r['status'] === 'draft'): ?>
                                    <span class="pill is-draft">draft</span>
                                <?php endif; ?>
                            </span>
                        </summary>

                        <div class="faq-row-body">
                            <form method="post" class="faq-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="id" value="<?= $r['id'] ?>" />

                                <div class="field">
                                    <label for="q<?= $r['id'] ?>">Question</label>
                                    <input type="text" id="q<?= $r['id'] ?>" name="question"
                                        value="<?= e($r['question']) ?>" required minlength="5" />
                                </div>

                                <div class="field">
                                    <label for="a<?= $r['id'] ?>">Answer</label>
                                    <textarea id="a<?= $r['id'] ?>" name="answer" rows="4" required
                                        minlength="10"><?= e($r['answer']) ?></textarea>
                                </div>

                                <div class="field-row">
                                    <div class="field">
                                        <label for="g<?= $r['id'] ?>">Topic</label>
                                        <input type="text" id="g<?= $r['id'] ?>" name="group_name"
                                            value="<?= e($r['group_name']) ?>" list="faqGroups" />
                                    </div>
                                    <div class="field">
                                        <label for="s<?= $r['id'] ?>">Status</label>
                                        <select id="s<?= $r['id'] ?>" name="status">
                                            <option value="published" <?= $r['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                            <option value="draft" <?= $r['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        </select>
                                    </div>
                                </div>

                                <input type="hidden" name="sort_order" value="<?= (int) $r['sort_order'] ?>" />

                                <label class="check">
                                    <input type="checkbox" name="show_on_home" value="1"
                                        <?= $r['show_on_home'] ? 'checked' : '' ?> />
                                    <span>Also show in the homepage FAQ block</span>
                                </label>

                                <div class="faq-row-actions">
                                    <button type="submit" class="btn-admin is-primary is-small">Save Changes</button>
                                </div>
                            </form>

                            <div class="faq-row-tools">
                                <?php foreach ([['up', $i > 0, 'Move up', '18 15 12 9 6 15'],
                                                ['down', $i < count($items) - 1, 'Move down', '6 9 12 15 18 9']] as [$dir, $can, $label, $points]): ?>
                                    <?php if ($can): ?>
                                        <form method="post" class="inline-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="move" />
                                            <input type="hidden" name="dir" value="<?= $dir ?>" />
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                                            <button type="submit" class="icon-btn" aria-label="<?= $label ?>">
                                                <svg viewBox="0 0 24 24"><polyline points="<?= $points ?>" /></svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <form method="post" class="inline-form"
                                    data-confirm-title="Delete this question?"
                                    data-confirm="“<?= e(mb_substr($r['question'], 0, 70)) ?>” will be removed from the FAQ page and its structured data.">
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
                    </details>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
