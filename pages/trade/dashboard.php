<?php
$recent = array_slice(orders_for_account($pdo, $me['id']), 0, 3);
$open   = count(array_filter(orders_for_account($pdo, $me['id']),
                fn($o) => !in_array($o['status'], ['completed','cancelled'], true)));
?>
<main id="main">
  <div class="container">
    <?php
    $hero = [
        'crumbs'  => ['Trade Account'],
        'eyebrow' => 'Trade Account',
        'title'   => 'Welcome back, ' . e(explode(' ', $me['name'])[0]) . '.',
        'lead'    => 'Browse the inventory, build a request list, and send it over. We confirm availability and '
                   . 'pricing on every request — nothing is charged here.',
    ];
    include __DIR__ . '/../../parts/page-header.php';
    include __DIR__ . '/_nav.php';
    ?>

    <?php if ($notice): ?><div class="form-status" style="margin-bottom:20px;"><?= e($notice) ?></div><?php endif; ?>

    <div class="stats-grid pb-section" style="text-align:left;gap:16px;">
      <a href="<?= $base_url ?>slabs" class="feature-card">
        <h3>Browse Inventory</h3>
        <p>Every slab on the floor, filterable by material. Add what you need to your request list as you go.</p>
      </a>
      <a href="<?= $base_url ?>trade/list" class="feature-card">
        <h3>Request List<?= cart_lines() ? ' · ' . cart_lines() : '' ?></h3>
        <p><?= cart_lines() ? 'You have ' . cart_count() . ' slab(s) ready to send.' : 'Nothing on the list yet — add materials from the inventory pages.' ?></p>
      </a>
      <a href="<?= $base_url ?>trade/orders" class="feature-card">
        <h3>My Requests<?= $open ? ' · ' . $open . ' open' : '' ?></h3>
        <p>Track what you have sent and where each request has got to.</p>
      </a>
    </div>

    <?php if ($recent): ?>
      <section class="pb-section" style="padding-top:0;">
        <div class="section-head">
          <div><span class="section-label">Latest</span><h2>Recent Requests</h2></div>
          <a href="<?= $base_url ?>trade/orders" class="btn-text">All requests
            <svg viewBox="0 0 24 24" aria-hidden="true"><line x1="4" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/></svg>
          </a>
        </div>
        <?php include __DIR__ . '/_orders-table.php'; ?>
      </section>
    <?php endif; ?>
  </div>
</main>
