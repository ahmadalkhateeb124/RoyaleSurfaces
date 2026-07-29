<?php
/**
 * Brand lockup. Renders the uploaded logo when the admin has set one, and
 * falls back to the built-in "R" mark plus wordmark otherwise.
 *
 * The logo's own flat background (whatever colour it was exported on) is cut
 * to transparent automatically — see logo_url_cutout() — so the same file
 * looks right on the dark navbar, the dark footer, or anywhere else, in its
 * own original colours. Set $brandClass (extra classes) before including if
 * needed.
 */
$brandClass = $brandClass ?? '';
?>
<?php if (has_logo()): ?>
    <img src="<?= e(logo_url_cutout()) ?>" alt="<?= e(site_name()) ?>" class="logo-img <?= e($brandClass) ?>"
        width="200" height="44" />
<?php else: ?>
    <span class="logo-icon" aria-hidden="true">R</span>
    <span class="logo-text"><?= e(site_name()) ?></span>
<?php endif; ?>
<?php $brandClass = null; ?>
