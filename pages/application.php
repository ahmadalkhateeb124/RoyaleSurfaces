<?php
/**
 * Renders any entry in APPLICATION_PAGES. `$app` and `$appSlug` come from index.php.
 */
require_once __DIR__ . '/../inc/conn.php';
require_once __DIR__ . '/../inc/content-pages.php';
require_once __DIR__ . '/../inc/slabs.php';
require_once __DIR__ . '/../inc/projects.php';

$appSlug = $appSlug ?? '';
$app = $app ?? (APPLICATION_PAGES[$appSlug] ?? null);
if (!$app) {
    return;
}

// Slabs in the materials this application calls for — real stock, not stock photos.
$picks = array_values(array_filter(
    slabs_all(),
    fn($s) => in_array($s['type'], $app['materials'], true)
));
$picks = array_slice($picks, 0, 3);

// Any finished project that used one of those materials.
$related = array_values(array_filter(
    gallery_projects(),
    fn($p) => in_array($p['type'], $app['materials'], true)
));
$related = array_slice($related, 0, 3);
?>

<main id="main">
  <div class="container">

    <?php
    // The picture sits beside the title rather than under it. These photos are
    // mostly square, so a full-width banner either cropped half of each one
    // away or left a wide empty band around it.
    ob_start(); ?>
      <img src="<?= e(slab_image($app['image'])) ?>"
        alt="<?= e($app['title']) ?> — <?= e(strtolower($app['h1'])) ?> at <?= e(site_name()) ?>"
        fetchpriority="high" />
    <?php
    $heroMedia = ob_get_clean();

    $hero = [
        'crumbs'  => ['Applications' => 'countertops', $app['title']],
        'eyebrow' => 'Applications',
        'title'   => $app['h1'],
        'lead'    => $app['lead'],
        'media'   => $heroMedia,
        'actions' => '<a href="' . $base_url . 'slabs" class="btn-primary">View Inventory</a>'
                   . '<a href="' . $base_url . 'contact" class="btn-outline">Request a Quote</a>',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <!-- RECOMMENDED MATERIALS -->
    <section class="section-pad" style="padding-top:0;">
      <div class="section-head" data-reveal>
        <div>
          <span class="section-label">Best Suited</span>
          <h2>Materials We Recommend</h2>
        </div>
        <a href="<?= $base_url ?>slabs" class="btn-text">
          Browse all inventory
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="4" y1="12" x2="19" y2="12" />
            <polyline points="13 6 19 12 13 18" />
          </svg>
        </a>
      </div>

      <div class="mini-grid">
        <?php foreach ($app['materials'] as $key): ?>
          <?php $m = SITE_MATERIALS[$key] ?? null; if (!$m) continue; ?>
          <a href="<?= $base_url . e($key) ?>" class="mini-card" data-reveal>
            <h3><?= e($m['label']) ?></h3>
            <p><?= e($m['blurb']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- GUIDANCE -->
    <section class="section-pad" style="padding-top:0;">
      <div class="prose" data-reveal>
        <?php foreach ($app['sections'] as [$heading, $text]): ?>
          <h2><?= e($heading) ?></h2>
          <p><?= e($text) ?></p>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- IN STOCK NOW -->
    <?php if ($picks): ?>
      <section class="section-pad" style="padding-top:0;">
        <div class="section-head" data-reveal>
          <div>
            <span class="section-label">In Stock</span>
            <h2>Slabs for This Job</h2>
          </div>
        </div>
        <div class="products-grid">
          <?php foreach ($picks as $slab): ?>
            <?php $label = SITE_MATERIALS[$slab['type']]['label'] ?? $slab['type']; ?>
            <article class="product-card" data-reveal>
              <a href="<?= $base_url ?>slabs/<?= e($slab['slug'] ?? '') ?>" class="product-card-img">
                <img src="<?= e(slab_image($slab['image'])) ?>"
                  alt="<?= e($slab['name']) ?> <?= e(strtolower($label)) ?> slab" loading="lazy" width="600"
                  height="450" />
                <span class="product-badge"><?= e($label) ?></span>
              </a>
              <div class="product-card-body">
                <h3><a href="<?= $base_url ?>slabs/<?= e($slab['slug'] ?? '') ?>"><?= e($slab['name']) ?></a></h3>
                <div class="product-specs">
                  <div class="spec-row"><span class="spec-key">Origin</span><span class="spec-val"><?= e($slab['origin']) ?></span></div>
                  <div class="spec-row"><span class="spec-key">Finish</span><span class="spec-val"><?= e($slab['finish']) ?></span></div>
                </div>
                <a href="<?= $base_url ?>contact?inquiry=<?= urlencode($slab['name']) ?>" class="btn-quote">Request Quote</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- FAQ -->
    <section class="section-pad" style="padding-top:0;">
      <div class="faq-layout">
        <div data-reveal>
          <span class="section-label">Questions</span>
          <h2><?= e($app['title']) ?> FAQ</h2>
          <p class="lead">Anything not covered here,
            <a href="<?= $base_url ?>contact" style="color:var(--accent);">ask our team</a> — we would rather answer
            before you buy than after.</p>
        </div>
        <div class="faq-list" data-reveal>
          <?php foreach ($app['faq'] as $i => [$q, $a]): ?>
            <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
              <summary><?= e($q) ?></summary>
              <p><?= e($a) ?></p>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- FINISHED WORK -->
    <?php if ($related): ?>
      <section class="pb-section">
        <div class="section-head" data-reveal>
          <div>
            <span class="section-label">Finished Work</span>
            <h2>Projects in These Materials</h2>
          </div>
          <a href="<?= $base_url ?>gallery" class="btn-text">
            Full gallery
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <line x1="4" y1="12" x2="19" y2="12" />
              <polyline points="13 6 19 12 13 18" />
            </svg>
          </a>
        </div>
        <div class="work-grid">
          <?php foreach ($related as $p): ?>
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

<section class="cta-banner">
  <div class="container">
    <h2>Planning <?= e(strtolower($app['title'])) ?>?</h2>
    <p>Bring your dimensions and we will work back to slab count, availability and pricing.</p>
    <a href="<?= $base_url ?>contact" class="btn-dark">Talk to Our Team</a>
  </div>
</section>

<script type="application/ld+json">
<?= json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type'          => 'Question',
        'name'           => strip_tags(html_entity_decode($f[0])),
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags(html_entity_decode($f[1]))],
    ], $app['faq']),
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
