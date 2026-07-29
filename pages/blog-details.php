<?php
/**
 * Single blog post. `$post` and `$postSlug` are resolved in index.php so the
 * header can emit the post's own title, description and social image.
 */
require_once __DIR__ . '/../inc/conn.php';    // $base_url + site constants
require_once __DIR__ . '/../inc/posts.php';

// Guard: this page is only ever reached with a resolved post, but keep it
// self-contained so a direct include can't emit warnings.
$postSlug = $postSlug ?? '';
$post = $post ?? (blog_posts()[$postSlug] ?? null);
if (!$post) {
    return;
}

$related    = blog_related($postSlug, 3);
$neighbours = blog_neighbours($postSlug);
$category   = BLOG_CATEGORIES[$post['category']] ?? 'Article';
$postUrl    = $base_url . 'blog/' . $postSlug;

// Headings become both the in-page anchors and the table of contents.
$headings = [];
foreach ($post['body'] as $i => [$tag, $text]) {
    if ($tag === 'h2') {
        $headings[] = ['id' => 'section-' . $i, 'text' => $text];
    }
}
?>

<!-- Reading progress -->
<div class="read-progress" id="readProgress" role="presentation"><span></span></div>

<main id="main">
  <div class="container">

    <?php
    $hero = [
        'crumbs' => ['Blog' => 'blog', $category => ltrim(str_replace($base_url, '', blog_url($base_url, 1, $post['category'])), '/'), $post['title']],
        'title'  => e($post['title']),
        'lead'   => e($post['excerpt']),
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>
    <div style="margin-top:-40px;">

      <?php $postUpdated = $post['updated'] ?? $post['published']; ?>
      <div class="post-byline">
        <span class="post-byline-cat"><?= e($category) ?></span>
        <time datetime="<?= e($post['published']) ?>"><?= date('F j, Y', strtotime($post['published'])) ?></time>
        <?php if (substr($postUpdated, 0, 10) !== $post['published']): ?>
          <span class="post-byline-updated">Updated <time datetime="<?= e($postUpdated) ?>"><?= date('F j, Y', strtotime($postUpdated)) ?></time></span>
        <?php endif; ?>
        <span><?= $post['read'] ?> min read</span>
      </div>
    </div>

    <img src="<?= e(slab_image($post['image'])) ?>" alt="" fetchpriority="high" width="1200"
      height="600" class="post-hero" />

    <div class="post-layout">

      <!-- ASIDE: contents + share -->
      <aside class="post-aside">
        <?php if (count($headings) > 1): ?>
          <nav class="post-toc" aria-label="On this page">
            <p class="post-toc-title">On this page</p>
            <ol>
              <?php foreach ($headings as $h): ?>
                <li><a href="#<?= e($h['id']) ?>"><?= e($h['text']) ?></a></li>
              <?php endforeach; ?>
            </ol>
          </nav>
        <?php endif; ?>

        <div class="post-share">
          <p class="post-toc-title">Share</p>
          <div class="post-share-links">
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($postUrl) ?>"
              target="_blank" rel="noopener noreferrer" aria-label="Share on LinkedIn">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z" />
                <rect x="2" y="9" width="4" height="12" />
                <circle cx="4" cy="4" r="2" />
              </svg>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($postUrl) ?>" target="_blank"
              rel="noopener noreferrer" aria-label="Share on Facebook">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
              </svg>
            </a>
            <a href="mailto:?subject=<?= rawurlencode($post['title']) ?>&amp;body=<?= rawurlencode($postUrl) ?>"
              aria-label="Share by email">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
              </svg>
            </a>
            <button type="button" class="copy-link" data-url="<?= e($postUrl) ?>" aria-label="Copy link">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
              </svg>
            </button>
          </div>
        </div>
      </aside>

      <!-- ARTICLE -->
      <article class="prose post-body">
        <?php foreach ($post['body'] as $i => [$tag, $text]): ?>
          <?php if ($tag === 'h2'): ?>
            <h2 id="section-<?= $i ?>"><?= e($text) ?></h2>
          <?php else: ?>
            <<?= $tag ?>><?= e($text) ?></<?= $tag ?>>
          <?php endif; ?>
        <?php endforeach; ?>

        <div class="post-cta">
          <h3>Sourcing material for a project?</h3>
          <p>Our <?= e(site_city()) ?> gallery holds 2,000+ slabs across granite, quartzite, marble, quartz, porcelain and
            more. Tell us what you're quoting and we'll come back with availability.</p>
          <div class="post-cta-actions">
            <a href="<?= $base_url ?>contact" class="btn-primary">Talk to Our Team</a>
            <a href="<?= $base_url ?>slabs" class="btn-outline">Browse Inventory</a>
          </div>
        </div>
      </article>
    </div>

    <!-- PREV / NEXT -->
    <?php if ($neighbours['prev'] || $neighbours['next']): ?>
      <nav class="post-nav" aria-label="More articles">
        <?php if ($neighbours['prev']): [$pSlug, $pPost] = $neighbours['prev']; ?>
          <a href="<?= $base_url ?>blog/<?= e($pSlug) ?>" class="post-nav-link" rel="prev">
            <span class="post-nav-dir">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <polyline points="15 18 9 12 15 6" />
              </svg>
              Previous
            </span>
            <span class="post-nav-title"><?= e($pPost['title']) ?></span>
          </a>
        <?php else: ?><span></span><?php endif; ?>

        <?php if ($neighbours['next']): [$nSlug, $nPost] = $neighbours['next']; ?>
          <a href="<?= $base_url ?>blog/<?= e($nSlug) ?>" class="post-nav-link is-next" rel="next">
            <span class="post-nav-dir">
              Next
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </span>
            <span class="post-nav-title"><?= e($nPost['title']) ?></span>
          </a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>

    <!-- RELATED -->
    <?php if ($related): ?>
      <section class="pb-section">
        <div class="section-head">
          <div>
            <span class="section-label">Keep Reading</span>
            <h2>Related Articles</h2>
          </div>
          <a href="<?= $base_url ?>blog" class="btn-text">
            All articles
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <line x1="4" y1="12" x2="19" y2="12" />
              <polyline points="13 6 19 12 13 18" />
            </svg>
          </a>
        </div>

        <div class="products-grid">
          <?php foreach ($related as $slug => $other): ?>
            <article class="product-card">
              <a href="<?= $base_url ?>blog/<?= e($slug) ?>" class="product-card-img" aria-hidden="true" tabindex="-1">
                <img src="<?= e(slab_image($other['image'])) ?>" alt="" loading="lazy" width="600"
                  height="450" />
                <span class="product-badge"><?= e(BLOG_CATEGORIES[$other['category']] ?? 'Article') ?></span>
              </a>
              <div class="product-card-body">
                <h3><a href="<?= $base_url ?>blog/<?= e($slug) ?>"><?= e($other['title']) ?></a></h3>
                <p class="post-excerpt"><?= e($other['excerpt']) ?></p>
                <div class="post-meta">
                  <span class="post-date">
                    <?= date('M j, Y', strtotime($other['published'])) ?> · <?= $other['read'] ?> min
                  </span>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </div>
</main>

<script type="application/ld+json">
<?= json_encode([
    '@context'      => 'https://schema.org',
    '@type'         => 'BlogPosting',
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $postUrl],
    'headline'      => $post['title'],
    'description'   => $post['excerpt'],
    'image'         => slab_image($post['image']),
    'datePublished' => date('c', strtotime($post['published'])),
    'dateModified'  => date('c', strtotime($postUpdated)),
    'author'        => ['@type' => 'Organization', 'name' => site_name(), 'url' => $base_url],
    'publisher'     => [
        '@type' => 'Organization',
        'name'  => site_name(),
        'logo'  => ['@type' => 'ImageObject', 'url' => has_logo() ? logo_url() : asset('assets/favicon.svg')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
