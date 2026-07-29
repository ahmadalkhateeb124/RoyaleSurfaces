<main id="main">
  <div class="container error-page">
    <div class="error-code" aria-hidden="true">404</div>
    <h1>Page Not Found</h1>
    <p class="lead" style="margin:0 auto 36px;">The page you're looking for doesn't exist or has been moved. Try one of
      the links below, or call us on <a href="<?= e(tel_link(site_phone())) ?>"
        style="color:var(--accent);"><?= e(site_phone()) ?></a>.</p>

    <a href="<?= $base_url ?>" class="btn-primary">Back to Home</a>

    <nav class="error-links" aria-label="Helpful links">
      <?php foreach (SITE_NAV as $slug => $label): ?>
        <a href="<?= $base_url . $slug ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="<?= $base_url ?>contact">Contact</a>
    </nav>
  </div>
</main>
