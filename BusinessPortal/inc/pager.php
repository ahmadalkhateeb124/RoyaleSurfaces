<?php
/**
 * Admin list pager. Expects $page, $pages and $qs (a callable taking a page
 * number and returning its URL). Reuses the front end's pager_range().
 */
if (($pages ?? 1) <= 1) {
    return;
}
?>
<nav class="admin-pager" aria-label="Pages">
    <?php if ($page > 1): ?>
        <a href="<?= e($qs($page - 1)) ?>" class="pager-num" rel="prev">‹</a>
    <?php endif; ?>

    <?php foreach (pager_range($page, $pages) as $n): ?>
        <?php if ($n === null): ?>
            <span class="pager-gap">…</span>
        <?php elseif ($n === $page): ?>
            <span class="pager-num active" aria-current="page"><?= $n ?></span>
        <?php else: ?>
            <a href="<?= e($qs($n)) ?>" class="pager-num"><?= $n ?></a>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($page < $pages): ?>
        <a href="<?= e($qs($page + 1)) ?>" class="pager-num" rel="next">›</a>
    <?php endif; ?>
</nav>
