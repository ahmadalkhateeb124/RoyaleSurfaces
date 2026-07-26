<main>
  <div class="container">
    <div class="page-header text-center">
      <span class="section-label">Our Blog</span>
      <h1>Insights & Updates</h1>
      <p>Stay informed with the latest from Royale Surfaces — material trends, project spotlights, and industry tips.</p>
    </div>

    <?php
    $posts = [];
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY COALESCE(published_at, created_at) DESC");
            $posts = $stmt->fetchAll();
        } catch (Throwable $e) {
            $posts = [];
        }
    }
    ?>

    <?php if (!empty($posts)): ?>
      <div class="products-grid" id="blogGrid">
        <?php foreach ($posts as $post): ?>
          <div class="product-card">
            <div class="product-card-img">
              <img src="<?= htmlspecialchars($post['featured_image'] ?: $base_url . 'assets/images/hero-stone.jpg') ?>" alt="<?= htmlspecialchars($post['title_ar'] ?? '') ?>" loading="lazy" />
              <span class="product-badge">Blog</span>
            </div>
            <div class="product-card-body">
              <h3><?= htmlspecialchars($post['title_ar'] ?? '') ?></h3>
              <p class="muted" style="font-size:15px;line-height:1.7;margin-bottom:16px;">
                <?= htmlspecialchars(mb_substr(strip_tags($post['excerpt_ar'] ?? $post['title_ar'] ?? ''), 0, 150)) ?>
              </p>
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="muted" style="font-size:13px;">
                  <?= date('M j, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?>
                </span>
                <a href="blog-details?puid=<?= htmlspecialchars($post['slug']) ?>" class="btn-quote">Read More</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align:center;padding:64px 0;">
        <h2>No Posts Yet</h2>
        <p class="muted">Check back soon for new content.</p>
      </div>
    <?php endif; ?>
  </div>
</main>

<section class="cta-banner">
  <div class="container">
    <h2>Stay Connected</h2>
    <p>Follow our journey and get the latest material insights directly in your inbox.</p>
    <a href="contact" class="btn-dark">Contact Us</a>
  </div>
</section>