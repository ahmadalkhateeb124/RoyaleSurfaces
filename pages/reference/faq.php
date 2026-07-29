<?php
/** Groups and questions come from the portal; see inc/faqs.php. */
require_once __DIR__ . '/../../inc/faqs.php';

$groups = faqs_grouped();
$total  = count(faqs_all());
?>

<div class="pb-section">
  <?php if (!$groups): ?>
    <div class="filter-empty">
      <p>No questions published yet. <a href="<?= $base_url ?>contact">Ask us directly</a> and we will add it here.</p>
    </div>
  <?php else: ?>
    <p class="filter-count"><?= $total ?> question<?= $total === 1 ? '' : 's' ?> across
      <?= count($groups) ?> topic<?= count($groups) === 1 ? '' : 's' ?></p>

    <?php foreach ($groups as $heading => $items): ?>
      <section class="faq-group" data-reveal>
        <h2 class="faq-group-title"><?= e($heading) ?></h2>
        <div class="faq-list">
          <?php foreach ($items as $f): ?>
            <details class="faq-item" id="q-<?= (int) $f['id'] ?>">
              <summary><?= e($f['question']) ?></summary>
              <p><?= nl2br(e($f['answer'])) ?></p>
            </details>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if ($groups): ?>
  <script type="application/ld+json">
  <?= json_encode(faqs_schema(faqs_all()), JSON_UNESCAPED_SLASHES) ?>
  </script>
<?php endif; ?>
