<?php
/**
 * Slab inventory. Data comes from inc/slabs.php, which reads the `slabs` table
 * the admin portal writes to and falls back to a seed array without a database.
 */
require_once __DIR__ . '/../inc/slabs.php';
require_once __DIR__ . '/../inc/trade.php';   // signed-in traders add straight from the grid

$slabs = slabs_all();

// Pre-filter server-side too, so the correct set is in the HTML even before JS
// runs (and so search engines see the filtered page as real content).
$activeType = strtolower((string) ($_GET['type'] ?? 'all'));
if (!isset(SITE_MATERIALS[$activeType])) {
    $activeType = 'all';
}
$visibleCount = $activeType === 'all'
    ? count($slabs)
    : count(array_filter($slabs, fn($s) => $s['type'] === $activeType));
?>

<main id="main" class="pb-section">
  <div class="container">

    <?php
    $hero = [
        'crumbs'  => $activeType !== 'all' ? ['Slab Inventory' => 'slabs', SITE_MATERIALS[$activeType]['label']] : ['Slab Inventory'],
        'eyebrow' => 'Materials',
        'title'   => $activeType !== 'all' ? e(SITE_MATERIALS[$activeType]['label']) . ' Slabs' : 'Slab Inventory',
        'lead'    => 'Current stock in our ' . e(site_city()) . ' gallery. Every slab is catalogued by bundle and lot
            number for guaranteed matching across large runs.',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <div class="products-header">
      <div class="filter-bar" id="filterBar" role="group" aria-label="Filter slabs by material">
        <button type="button" class="filter-btn<?= $activeType === 'all' ? ' active' : '' ?>" data-filter="all"
          aria-pressed="<?= $activeType === 'all' ? 'true' : 'false' ?>">All</button>
        <?php foreach (SITE_MATERIALS as $slug => $m): ?>
          <button type="button" class="filter-btn<?= $activeType === $slug ? ' active' : '' ?>" data-filter="<?= e($slug) ?>"
            aria-pressed="<?= $activeType === $slug ? 'true' : 'false' ?>"><?= e($m['label']) ?></button>
        <?php endforeach; ?>
      </div>
    </div>

    <p class="filter-count" id="filterCount" aria-live="polite">
      <?= $visibleCount ?> <?= $visibleCount === 1 ? 'slab' : 'slabs' ?>
    </p>

    <div class="products-grid" id="productsGrid">
      <?php foreach ($slabs as $slab):
          $label = SITE_MATERIALS[$slab['type']]['label'];
          $hidden = $activeType !== 'all' && $slab['type'] !== $activeType;
          $stock = slab_stock($slab);
          [$stockText, $stockTone] = slab_stock_label($stock);
      ?>
        <article class="product-card<?= $stock < 1 ? ' is-soldout' : '' ?>" data-type="<?= e($slab['type']) ?>" <?= $hidden ? 'hidden' : '' ?>>
          <div class="product-card-img">
            <img src="<?= e(slab_image($slab['image'])) ?>"
              alt="<?= e($slab['name']) ?> <?= e(strtolower($label)) ?> slab from <?= e($slab['origin']) ?>"
              loading="lazy" width="600" height="450" />
            <span class="product-badge"><?= e($label) ?></span>
            <?php if ($stock < 1): ?>
              <span class="sold-out-flag">Sold Out</span>
            <?php endif; ?>
          </div>
          <div class="product-card-body">
            <h3><?= e($slab['name']) ?></h3>
            <div class="product-specs">
              <div class="spec-row"><span class="spec-key">Origin</span><span class="spec-val"><?= e($slab['origin']) ?></span></div>
              <div class="spec-row"><span class="spec-key">Finish</span><span class="spec-val"><?= e($slab['finish']) ?></span></div>
              <div class="spec-row"><span class="spec-key">Thickness</span><span class="spec-val"><?= e($slab['thickness']) ?></span></div>
              <div class="spec-row"><span class="spec-key">Avg Size</span><span class="spec-val"><?= e($slab['size']) ?></span></div>
              <div class="spec-row"><span class="spec-key">Availability</span>
                <span class="spec-val stock-note is-<?= $stockTone ?>"><?= e($stockText) ?></span></div>
            </div>
            <?php if (trade_check() && $stock < 1): ?>
              <span class="btn-quote is-disabled" aria-disabled="true">Sold Out</span>
              <p class="card-inlist">Tell us what you need — <a href="<?= $base_url ?>contact">we source weekly</a>.</p>
            <?php elseif (trade_check()): ?>
              <?php $inList = cart_items()[$slab['slug'] ?? ''] ?? null; ?>
              <form method="post" action="<?= $base_url ?>trade/list" class="card-request">
                <input type="hidden" name="action" value="cart_add" />
                <input type="hidden" name="slug" value="<?= e($slab['slug'] ?? '') ?>" />
                <input type="hidden" name="back" value="<?= e($base_url . 'slabs' . ($activeType !== 'all' ? '?type=' . $activeType : '')) ?>" />
                <label class="sr-only" for="q<?= e($slab['slug'] ?? '') ?>">Slabs of <?= e($slab['name']) ?></label>
                <input type="number" id="q<?= e($slab['slug'] ?? '') ?>" name="quantity" value="1" min="1"
                  max="<?= $stock ?>" />
                <button type="submit" class="btn-quote">
                  <?= $inList ? 'Add More' : 'Add' ?><span class="sr-only"> <?= e($slab['name']) ?> to request list</span>
                </button>
              </form>
              <?php if ($inList): ?>
                <p class="card-inlist"><?= (int) $inList['quantity'] ?> of <?= $stock ?> on your list</p>
              <?php endif; ?>
            <?php elseif ($stock < 1): ?>
              <a href="<?= $base_url ?>contact?inquiry=<?= urlencode($slab['name']) ?>" class="btn-quote">
                Ask When It Lands<span class="sr-only"> — <?= e($slab['name']) ?></span>
              </a>
            <?php else: ?>
              <a href="<?= $base_url ?>contact?inquiry=<?= urlencode($slab['name']) ?>" class="btn-quote">
                Request Quote<span class="sr-only"> for <?= e($slab['name']) ?></span>
              </a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="filter-empty" id="filterEmpty" hidden>
      <p>No slabs match that filter right now. New containers land weekly — <a href="<?= $base_url ?>contact">tell us
          what you need</a> and we'll source it.</p>
    </div>

    <div class="panel">
      <h2>Inventory Changes Weekly</h2>
      <p>This page shows a selection of what's on the floor. The best way to see the full range — including new arrivals
        and exotics — is to walk the gallery in person.</p>
      <a href="<?= $base_url ?>contact" class="btn-primary">Schedule a Showroom Visit</a>
    </div>

  </div>
</main>
