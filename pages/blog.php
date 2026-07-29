<?php
require_once __DIR__ . '/../inc/posts.php';

$cat  = (string) ($_GET['cat'] ?? '');
$cat  = isset(BLOG_CATEGORIES[$cat]) ? $cat : '';
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = blog_page($page, $cat);
$counts = blog_category_counts();

// The newest post gets a full-width feature — but only on the unfiltered
// first page, otherwise it would repeat inside the grid below.
$feature = null;
if ($result['page'] === 1 && $cat === '' && $result['posts']) {
    $featureSlug = array_key_first($result['posts']);
    $feature = $result['posts'][$featureSlug];
    unset($result['posts'][$featureSlug]);
}

$from = ($result['page'] - 1) * $result['perPage'] + 1;
$to   = min($result['page'] * $result['perPage'], $result['total']);
?>

<main id="main">
  <div class="container">

    <?php
    $hero = [
        'crumbs'  => $cat !== '' ? ['Blog' => 'blog', BLOG_CATEGORIES[$cat]] : ['Blog'],
        'eyebrow' => 'Our Blog',
        'title'   => $cat !== '' ? e(BLOG_CATEGORIES[$cat]) : 'Insights &amp; Updates',
        'lead'    => 'Material trends, fabrication guidance and care advice from the Royale Surfaces floor.',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <!-- CATEGORY FILTER -->
    <?php if ($counts): ?>
      <nav class="filter-bar blog-filters" aria-label="Filter by category">
        <a href="<?= $base_url ?>blog" class="filter-btn<?= $cat === '' ? ' active' : '' ?>">
          All <span class="filter-n"><?= count(blog_posts()) ?></span>
        </a>
        <?php foreach ($counts as $key => $n): ?>
          <a href="<?= e(blog_url($base_url, 1, $key)) ?>" class="filter-btn<?= $cat === $key ? ' active' : '' ?>">
            <?= e(BLOG_CATEGORIES[$key]) ?> <span class="filter-n"><?= $n ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>

    <?php if ($result['total'] === 0): ?>

      <div class="filter-empty" style="margin-bottom:96px;">
        <p>No articles in this category yet. <a href="<?= $base_url ?>blog">Browse all articles</a>.</p>
      </div>

    <?php else: ?>

      <p class="filter-count">
        Showing <?= $from ?>–<?= $to ?> of <?= $result['total'] ?>
        <?= $result['total'] === 1 ? 'article' : 'articles' ?>
        <?php if ($result['pages'] > 1): ?>
          · Page <?= $result['page'] ?> of <?= $result['pages'] ?>
        <?php endif; ?>
      </p>

      <!-- FEATURED POST -->
      <?php if ($feature): ?>
        <a href="<?= $base_url ?>blog/<?= e($featureSlug) ?>" class="post-feature" data-reveal>
          <div class="post-feature-img">
            <img src="<?= e(slab_image($feature['image'])) ?>" alt="" loading="eager" width="900"
              height="600" />
            <span class="product-badge">Latest</span>
          </div>
          <div class="post-feature-body">
            <span class="section-label"><?= e(BLOG_CATEGORIES[$feature['category']] ?? 'Article') ?></span>
            <h2><?= e($feature['title']) ?></h2>
            <p><?= e($feature['excerpt']) ?></p>
            <div class="post-meta">
              <span class="post-date">
                <?= date('M j, Y', strtotime($feature['published'])) ?> · <?= $feature['read'] ?> min read
              </span>
              <span class="btn-text">
                Read article
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <line x1="4" y1="12" x2="19" y2="12" />
                  <polyline points="13 6 19 12 13 18" />
                </svg>
              </span>
            </div>
          </div>
        </a>
      <?php endif; ?>

      <!-- GRID -->
      <?php if ($result['posts']): ?>
        <div class="products-grid">
          <?php foreach ($result['posts'] as $slug => $post): ?>
            <article class="product-card" data-reveal>
              <a href="<?= $base_url ?>blog/<?= e($slug) ?>" class="product-card-img" aria-hidden="true" tabindex="-1">
                <img src="<?= e(slab_image($post['image'])) ?>" alt="" loading="lazy" width="600"
                  height="450" />
                <span class="product-badge"><?= e(BLOG_CATEGORIES[$post['category']] ?? 'Article') ?></span>
              </a>
              <div class="product-card-body">
                <h3><a href="<?= $base_url ?>blog/<?= e($slug) ?>"><?= e($post['title']) ?></a></h3>
                <p class="post-excerpt"><?= e($post['excerpt']) ?></p>
                <div class="post-meta">
                  <span class="post-date">
                    <?= date('M j, Y', strtotime($post['published'])) ?> · <?= $post['read'] ?> min
                  </span>
                  <span class="btn-text">
                    Read
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <line x1="4" y1="12" x2="19" y2="12" />
                      <polyline points="13 6 19 12 13 18" />
                    </svg>
                  </span>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- PAGINATION -->
      <?php if ($result['pages'] > 1): ?>
        <nav class="pager" aria-label="Blog pages">
          <?php if ($result['page'] > 1): ?>
            <a href="<?= e(blog_url($base_url, $result['page'] - 1, $cat)) ?>" class="pager-arrow" rel="prev">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <polyline points="15 18 9 12 15 6" />
              </svg>
              <span>Previous</span>
            </a>
          <?php else: ?>
            <span class="pager-arrow is-disabled" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6" />
              </svg>
              <span>Previous</span>
            </span>
          <?php endif; ?>

          <ol class="pager-nums">
            <?php foreach (pager_range($result['page'], $result['pages']) as $n): ?>
              <?php if ($n === null): ?>
                <li class="pager-gap" aria-hidden="true">…</li>
              <?php elseif ($n === $result['page']): ?>
                <li><span class="pager-num active" aria-current="page"><?= $n ?></span></li>
              <?php else: ?>
                <li>
                  <a href="<?= e(blog_url($base_url, $n, $cat)) ?>" class="pager-num"
                    aria-label="Page <?= $n ?>"><?= $n ?></a>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ol>

          <?php if ($result['page'] < $result['pages']): ?>
            <a href="<?= e(blog_url($base_url, $result['page'] + 1, $cat)) ?>" class="pager-arrow" rel="next">
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

<section class="cta-banner">
  <div class="container">
    <h2>Need Material for a Project?</h2>
    <p>Our team can source specific lots, exotics and matched bundles for anything you're quoting.</p>
    <a href="<?= $base_url ?>contact" class="btn-dark">Talk to Our Trade Team</a>
  </div>
</section>
