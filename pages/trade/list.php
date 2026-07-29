<?php
/**
 * Stock can move while a list sits open, so every view re-checks it. Lines that
 * went sold out are dropped and over-quantities trimmed, with a plain note
 * saying what changed — a trader must never submit a list that cannot be filled.
 */
$stockChanges = cart_reconcile();
$items = cart_items();
?>
<main id="main">
  <div class="container">
    <?php
    $hero = [
        'crumbs'  => ['Trade' => 'trade', 'Request List'],
        'eyebrow' => 'Trade Account',
        'title'   => 'Your Request List',
        'lead'    => 'Set the quantity and any size or finish notes for each material, then send it over. '
                   . 'Nothing is charged — we come back with availability and pricing.',
    ];
    include __DIR__ . '/../../parts/page-header.php';
    include __DIR__ . '/_nav.php';
    ?>

    <?php if ($notice): ?><div class="form-status" style="margin-bottom:20px;"><?= e($notice) ?></div><?php endif; ?>
    <?php foreach ($stockChanges as $change): ?>
      <div class="form-status is-error"><?= e($change) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $err): ?><div class="form-status is-error"><?= e($err) ?></div><?php endforeach; ?>

    <?php if (!$items): ?>
      <div class="filter-empty" style="margin-bottom:96px;">
        <p>Your list is empty. <a href="<?= $base_url ?>slabs">Browse the inventory</a> and add the materials you need.</p>
      </div>
    <?php else: ?>
      <div class="req-lines">
        <?php foreach ($items as $slug => $it): ?>
          <?php
          $lineStock = slab_stock_for($slug);
          [$lineText, $lineTone] = slab_stock_label($lineStock);
          ?>
          <article class="req-line">
            <img src="<?= e(slab_image($it['image'])) ?>" alt="" width="120" height="90" loading="lazy" />

            <div class="req-info">
              <span class="req-type"><?= e(SITE_MATERIALS[$it['type']]['label'] ?? $it['type']) ?></span>
              <h3><a href="<?= $base_url ?>slabs/<?= e($slug) ?>"><?= e($it['name']) ?></a></h3>
              <?php if ($it['thickness'] !== ''): ?>
                <span class="req-sub"><?= e($it['thickness']) ?></span>
              <?php endif; ?>
              <span class="stock-note is-<?= $lineTone ?>"><?= e($lineText) ?></span>
            </div>

            <form method="post" class="req-form">
              <input type="hidden" name="action" value="cart_update" />
              <input type="hidden" name="slug" value="<?= e($slug) ?>" />

              <label>
                <span>Slabs <small>max <?= $lineStock ?></small></span>
                <input type="number" name="quantity" value="<?= (int) $it['quantity'] ?>" min="1"
                  max="<?= $lineStock ?>" />
              </label>
              <label>
                <span>Size needed</span>
                <input type="text" name="size" value="<?= e($it['size']) ?>" placeholder='130" × 80"' />
              </label>
              <label class="req-notes">
                <span>Notes</span>
                <input type="text" name="notes" value="<?= e($it['notes']) ?>" placeholder="Finish, lot matching, thickness…" />
              </label>
              <button type="submit" class="btn-quote">Update</button>
            </form>

            <form method="post" class="req-remove">
              <input type="hidden" name="action" value="cart_remove" />
              <input type="hidden" name="slug" value="<?= e($slug) ?>" />
              <button type="submit" aria-label="Remove <?= e($it['name']) ?>">
                <svg viewBox="0 0 24 24"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
              </button>
            </form>
          </article>
        <?php endforeach; ?>
      </div>

      <form method="post" class="req-submit">
        <input type="hidden" name="action" value="submit_order" />
        <h2>Send this request</h2>
        <p class="req-total"><?= cart_lines() ?> material<?= cart_lines() === 1 ? '' : 's' ?> · <?= cart_count() ?> slab<?= cart_count() === 1 ? '' : 's' ?></p>

        <div class="form-row">
          <div class="form-group">
            <label for="needed_by">Needed by <span style="text-transform:none;letter-spacing:0;">optional</span></label>
            <input type="date" id="needed_by" name="needed_by" />
          </div>
          <div class="form-group">
            <label for="notes">Anything else we should know?</label>
            <input type="text" id="notes" name="notes" placeholder="Job name, delivery vs pickup, lot matching…" />
          </div>
        </div>

        <button type="submit" class="btn-primary">Send Request</button>
        <p class="req-note">Every slab on this list is in stock right now. You will get an email copy immediately. We confirm availability and pricing within one
          business day — nothing is committed until you approve the quote.</p>
      </form>
    <?php endif; ?>

    <div class="pb-section"></div>
  </div>
</main>
