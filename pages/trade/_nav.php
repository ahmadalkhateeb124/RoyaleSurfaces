<?php /** Shared sub-nav for signed-in trade pages. */ ?>
<nav class="trade-bar" aria-label="Trade account">
  <div class="trade-who">
    <span class="trade-avatar"><?= e(strtoupper(substr($me['company'], 0, 1))) ?></span>
    <div>
      <strong><?= e($me['company']) ?></strong>
      <small><?= e($me['name']) ?></small>
    </div>
  </div>
  <div class="trade-links">
    <a href="<?= $base_url ?>trade" class="<?= ($tradePage ?? '') === '' ? 'active' : '' ?>">Overview</a>
    <a href="<?= $base_url ?>trade/list" class="<?= ($tradePage ?? '') === 'list' ? 'active' : '' ?>">
      Request List
      <?php if (cart_lines()): ?><span class="trade-badge"><?= cart_lines() ?></span><?php endif; ?>
    </a>
    <a href="<?= $base_url ?>trade/orders" class="<?= ($tradePage ?? '') === 'orders' ? 'active' : '' ?>">My Requests</a>
    <a href="<?= $base_url ?>trade/logout" class="trade-out">Sign out</a>
  </div>
</nav>
