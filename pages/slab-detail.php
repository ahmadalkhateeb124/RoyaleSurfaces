<?php
/**
 * Single slab page. `$slab` and `$slabSlug` are resolved in index.php so the
 * header can emit this slab's own title, description and social image.
 */
require_once __DIR__ . '/../inc/conn.php';
require_once __DIR__ . '/../inc/slabs.php';
require_once __DIR__ . '/../inc/projects.php';
require_once __DIR__ . '/../inc/trade.php';

$slabSlug = $slabSlug ?? '';
$slab = $slab ?? null;
if (!$slab) {
    return;
}

$label = SITE_MATERIALS[$slab['type']]['label'] ?? $slab['type'];
$kind  = SITE_MATERIALS[$slab['type']]['kind'] ?? '';

// Same material, excluding this one.
$similar = array_values(array_filter(
    slabs_all(),
    fn($s) => $s['type'] === $slab['type'] && ($s['slug'] ?? '') !== $slabSlug
));
$similar = array_slice($similar, 0, 3);

// Projects that used this material.
$projects = array_slice(array_values(array_filter(
    gallery_projects(),
    fn($p) => $p['type'] === $slab['type']
)), 0, 2);

$specs = array_filter([
    'Material'  => $label,
    'Origin'    => $slab['origin'] ?? '',
    'Finish'    => $slab['finish'] ?? '',
    'Thickness' => $slab['thickness'] ?? '',
    'Avg Size'  => $slab['size'] ?? '',
]);

$stock = slab_stock($slab);
[$stockText, $stockTone] = slab_stock_label($stock);
?>

<main id="main">
  <div class="container">

    <?php
    $hero = [
        'crumbs'  => ['Slab Inventory' => 'slabs', $label => $slab['type'], $slab['name']],
        'eyebrow' => $kind,
        'title'   => e($slab['name']),
        'lead'    => $slab['description'] ?? '',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <div class="slab-detail">
      <div class="slab-photo<?= $stock < 1 ? ' is-soldout' : '' ?>">
        <img src="<?= e(slab_image($slab['image'])) ?>"
          alt="<?= e($slab['name']) ?> <?= e(strtolower($label)) ?> slab<?= !empty($slab['origin']) ? ' from ' . e($slab['origin']) : '' ?>"
          fetchpriority="high" width="1000" height="750" />
        <span class="product-badge"><?= e($label) ?></span>
        <?php if ($stock < 1): ?>
          <span class="sold-out-flag">Sold Out</span>
        <?php endif; ?>
      </div>

      <aside class="slab-info">
        <h2>Specification</h2>

        <p class="stock-line is-<?= $stockTone ?>">
          <span class="stock-dot" aria-hidden="true"></span>
          <?= e($stockText) ?>
          <?php if ($stock > 0): ?>
            <small>on the floor in <?= e(site_city()) ?></small>
          <?php endif; ?>
        </p>

        <dl class="slab-specs">
          <?php foreach ($specs as $k => $v): ?>
            <div>
              <dt><?= e($k) ?></dt>
              <dd><?= e($v) ?></dd>
            </div>
          <?php endforeach; ?>
        </dl>

        <?php if (trade_check() && $stock < 1): ?>
          <div class="slab-actions">
            <a href="<?= $base_url ?>contact?inquiry=<?= urlencode($slab['name']) ?>" class="btn-primary">
              Ask Us to Source It</a>
            <a href="<?= $base_url . e($slab['type']) ?>" class="btn-outline">See Similar <?= e($label) ?></a>
          </div>
          <p class="slab-note">This one is off the floor. New containers land weekly — tell us the size and finish
            you need and we will call you the moment it arrives.</p>
        <?php elseif (trade_check()): ?>
          <form method="post" action="<?= $base_url ?>trade/list" class="slab-request">
            <input type="hidden" name="action" value="cart_add" />
            <input type="hidden" name="slug" value="<?= e($slabSlug) ?>" />
            <input type="hidden" name="back" value="<?= e($base_url . 'slabs/' . $slabSlug) ?>" />
            <label for="reqQty">How many slabs? <span class="stock-hint"><?= $stock ?> available</span></label>
            <div class="slab-request-row">
              <input type="number" id="reqQty" name="quantity" value="1" min="1" max="<?= $stock ?>" />
              <button type="submit" class="btn-primary">Add to Request</button>
            </div>
            <input type="text" name="notes" placeholder="Size, finish or lot-matching notes (optional)" />
          </form>
          <div class="slab-actions">
            <a href="<?= $base_url ?>trade/list" class="btn-outline">View Request List<?= cart_lines() ? ' (' . cart_lines() . ')' : '' ?></a>
          </div>
        <?php else: ?>
          <div class="slab-actions">
            <a href="<?= $base_url ?>contact?inquiry=<?= urlencode($slab['name']) ?>" class="btn-primary">
              <?= $stock < 1 ? 'Ask Us to Source It' : 'Request a Quote' ?></a>
            <a href="<?= $base_url ?>contact" class="btn-outline">Book a Viewing</a>
          </div>
          <p class="slab-note">Trade customer? <a href="<?= $base_url ?>trade/login">Sign in</a> to build a
            multi-material request list.</p>
        <?php endif; ?>

        <p class="slab-note">
          Natural stone varies slab to slab — the photo shows the material, not the exact piece you will receive.
          We recommend selecting your actual slabs in our <?= e(site_city()) ?> gallery before fabrication.
        </p>

        <p class="slab-note">
          <strong>Stock moves fast.</strong> The count above is live, but slabs sell off the floor daily — call
          <a href="<?= e(tel_link(site_phone())) ?>"><?= e(site_phone()) ?></a> to hold a lot before you quote a job.
        </p>
      </aside>
    </div>

    <!-- SIMILAR -->
    <?php if ($similar): ?>
      <section class="section-pad">
        <div class="section-head" data-reveal>
          <div>
            <span class="section-label">Same Material</span>
            <h2>More <?= e($label) ?></h2>
          </div>
          <a href="<?= $base_url . e($slab['type']) ?>" class="btn-text">
            All <?= e($label) ?>
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <line x1="4" y1="12" x2="19" y2="12" />
              <polyline points="13 6 19 12 13 18" />
            </svg>
          </a>
        </div>
        <div class="products-grid">
          <?php foreach ($similar as $s): ?>
            <article class="product-card" data-reveal>
              <a href="<?= $base_url ?>slabs/<?= e($s['slug'] ?? '') ?>" class="product-card-img">
                <img src="<?= e(slab_image($s['image'])) ?>" alt="<?= e($s['name']) ?>" loading="lazy" width="600"
                  height="450" />
              </a>
              <div class="product-card-body">
                <h3><a href="<?= $base_url ?>slabs/<?= e($s['slug'] ?? '') ?>"><?= e($s['name']) ?></a></h3>
                <div class="product-specs">
                  <div class="spec-row"><span class="spec-key">Origin</span><span class="spec-val"><?= e($s['origin']) ?></span></div>
                  <div class="spec-row"><span class="spec-key">Finish</span><span class="spec-val"><?= e($s['finish']) ?></span></div>
                </div>
                <a href="<?= $base_url ?>slabs/<?= e($s['slug'] ?? '') ?>" class="btn-quote">View Slab</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if ($projects): ?>
      <section class="pb-section">
        <div class="section-head" data-reveal>
          <div>
            <span class="section-label">Finished Work</span>
            <h2><?= e($label) ?> in Place</h2>
          </div>
        </div>
        <div class="work-grid">
          <?php foreach ($projects as $p): ?>
            <a href="<?= $base_url ?>gallery?type=<?= e($p['type']) ?>" class="work-card" data-reveal>
              <img src="<?= e(slab_image($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy" width="600"
                height="450" />
              <div class="work-info">
                <h3><?= e($p['title']) ?></h3>
                <span><?= e($p['material']) ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </div>
</main>

<script type="application/ld+json">
<?= json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $slab['name'],
    'category'    => $label,
    'image'       => slab_image($slab['image']),
    'description' => $slab['description'] !== ''
        ? $slab['description']
        : $slab['name'] . ' — ' . strtolower($label) . ' slab'
            . (!empty($slab['origin']) ? ' from ' . $slab['origin'] : '')
            . (!empty($slab['finish']) ? ', ' . strtolower($slab['finish']) . ' finish' : '') . '.',
    'brand'       => ['@type' => 'Brand', 'name' => site_name()],
    'material'    => $label,
    'offers'      => [
        '@type'         => 'Offer',
        'availability'  => $stock > 0
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
        'priceCurrency' => 'USD',
        'url'           => $base_url . 'slabs/' . $slabSlug,
        'seller'        => ['@id' => $base_url . '#business'],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
