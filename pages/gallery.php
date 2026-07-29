<?php
require_once __DIR__ . '/../inc/projects.php';

$type = (string) ($_GET['type'] ?? '');
$type = isset(SITE_MATERIALS[$type]) ? $type : '';
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = gallery_page($page, $type);
$counts = gallery_type_counts();

$from = ($result['page'] - 1) * $result['perPage'] + 1;
$to   = min($result['page'] * $result['perPage'], $result['total']);
?>

<main id="main">
  <div class="container">

    <?php
    $hero = [
        'crumbs'  => $type !== '' ? ['Gallery' => 'gallery', SITE_MATERIALS[$type]['label']] : ['Gallery'],
        'eyebrow' => 'Finished Work',
        'title'   => $type !== '' ? e(SITE_MATERIALS[$type]['label']) . ' Projects' : 'Finished Projects',
        'lead'    => 'Our material, fabricated to perfection by the shops we supply. Explore how premium natural
            stone elevates architectural spaces across Texas.',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <!-- MATERIAL FILTER -->
    <?php if ($counts): ?>
      <nav class="filter-bar gallery-filters" aria-label="Filter projects by material">
        <a href="<?= $base_url ?>gallery" class="filter-btn<?= $type === '' ? ' active' : '' ?>">
          All <span class="filter-n"><?= count(gallery_projects()) ?></span>
        </a>
        <?php foreach ($counts as $key => $n): ?>
          <a href="<?= e(gallery_url($base_url, 1, $key)) ?>" class="filter-btn<?= $type === $key ? ' active' : '' ?>">
            <?= e(SITE_MATERIALS[$key]['label']) ?> <span class="filter-n"><?= $n ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>

    <?php if ($result['total'] === 0): ?>

      <div class="filter-empty" style="margin-bottom:96px;">
        <p>No projects in this material yet. <a href="<?= $base_url ?>gallery">View all projects</a> or
          <a href="<?= $base_url ?>contact">tell us what you're building</a>.</p>
      </div>

    <?php else: ?>

      <p class="filter-count">
        Showing <?= $from ?>–<?= $to ?> of <?= $result['total'] ?>
        <?= $result['total'] === 1 ? 'project' : 'projects' ?>
        <?php if ($result['pages'] > 1): ?> · Page <?= $result['page'] ?> of <?= $result['pages'] ?><?php endif; ?>
      </p>

      <!-- GRID -->
      <ul class="gallery-grid" id="galleryGrid">
        <?php foreach ($result['projects'] as $i => $p): ?>
          <li class="gallery-tile<?= !empty($p['feature']) ? ' is-feature' : '' ?>" data-reveal>
            <button type="button" class="gallery-open"
              data-index="<?= $i ?>"
              data-image="<?= e(slab_image($p['image'])) ?>"
              data-title="<?= e($p['title']) ?>"
              data-space="<?= e($p['space']) ?>"
              data-material="<?= e($p['material']) ?>"
              data-location="<?= e($p['location']) ?>"
              data-body="<?= e($p['body']) ?>"
              data-href="<?= $base_url ?>slabs?type=<?= e($p['type']) ?>"
              aria-label="View <?= e($p['title']) ?> larger">
              <img src="<?= e(slab_image($p['image'])) ?>"
                alt="<?= e($p['title']) ?> — <?= e($p['material']) ?> in <?= e($p['location']) ?>"
                loading="<?= $i < 4 ? 'eager' : 'lazy' ?>" decoding="async" width="800" height="600" />
              <span class="gallery-overlay">
                <span class="gallery-space"><?= e($p['space']) ?></span>
                <span class="gallery-title"><?= e($p['title']) ?></span>
                <span class="gallery-material"><?= e($p['material']) ?></span>
              </span>
              <span class="gallery-zoom" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                  <circle cx="11" cy="11" r="7" />
                  <line x1="16.5" y1="16.5" x2="21" y2="21" />
                  <line x1="11" y1="8" x2="11" y2="14" />
                  <line x1="8" y1="11" x2="14" y2="11" />
                </svg>
              </span>
            </button>
          </li>
        <?php endforeach; ?>
      </ul>

      <!-- PAGINATION -->
      <?php if ($result['pages'] > 1): ?>
        <nav class="pager" aria-label="Gallery pages">
          <?php if ($result['page'] > 1): ?>
            <a href="<?= e(gallery_url($base_url, $result['page'] - 1, $type)) ?>" class="pager-arrow" rel="prev">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <polyline points="15 18 9 12 15 6" />
              </svg><span>Previous</span>
            </a>
          <?php else: ?>
            <span class="pager-arrow is-disabled" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6" />
              </svg><span>Previous</span>
            </span>
          <?php endif; ?>

          <ol class="pager-nums">
            <?php foreach (pager_range($result['page'], $result['pages']) as $n): ?>
              <?php if ($n === null): ?>
                <li class="pager-gap" aria-hidden="true">…</li>
              <?php elseif ($n === $result['page']): ?>
                <li><span class="pager-num active" aria-current="page"><?= $n ?></span></li>
              <?php else: ?>
                <li><a href="<?= e(gallery_url($base_url, $n, $type)) ?>" class="pager-num"
                    aria-label="Page <?= $n ?>"><?= $n ?></a></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ol>

          <?php if ($result['page'] < $result['pages']): ?>
            <a href="<?= e(gallery_url($base_url, $result['page'] + 1, $type)) ?>" class="pager-arrow" rel="next">
              <span>Next</span>
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </a>
          <?php else: ?>
            <span class="pager-arrow is-disabled" aria-hidden="true">
              <span>Next</span>
              <svg viewBox="0 0 24 24">
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>

    <?php endif; ?>

    <div class="pb-section"></div>
  </div>
</main>

<!-- LIGHTBOX (one instance, populated from the clicked tile) -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-labelledby="lbTitle" hidden>
  <div class="lightbox-backdrop" data-close></div>

  <button type="button" class="lightbox-close" data-close aria-label="Close">
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <line x1="6" y1="6" x2="18" y2="18" />
      <line x1="18" y1="6" x2="6" y2="18" />
    </svg>
  </button>

  <button type="button" class="lightbox-nav is-prev" data-prev aria-label="Previous project">
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <polyline points="15 18 9 12 15 6" />
    </svg>
  </button>

  <figure class="lightbox-inner">
    <img id="lbImage" src="" alt="" />
    <figcaption class="lightbox-caption">
      <span class="section-label" id="lbSpace"></span>
      <h2 id="lbTitle"></h2>
      <p id="lbBody"></p>
      <div class="lightbox-meta">
        <span><strong id="lbMaterial"></strong></span>
        <span id="lbLocation"></span>
        <a href="#" id="lbLink" class="btn-text">
          Source this material
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="4" y1="12" x2="19" y2="12" />
            <polyline points="13 6 19 12 13 18" />
          </svg>
        </a>
      </div>
      <p class="lightbox-count" id="lbCount"></p>
    </figcaption>
  </figure>

  <button type="button" class="lightbox-nav is-next" data-next aria-label="Next project">
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <polyline points="9 18 15 12 9 6" />
    </svg>
  </button>
</div>

<section class="cta-banner">
  <div class="container">
    <h2>Source the Material</h2>
    <p>Ready to supply your next project with premium stone? Visit our <?= e(site_city()) ?> showroom or get in touch with
      our trade team.</p>
    <a href="<?= $base_url ?>contact" class="btn-dark">Contact Us Today</a>
  </div>
</section>
