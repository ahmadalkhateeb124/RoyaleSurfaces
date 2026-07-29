<?php $orders = orders_for_account($pdo, $me['id']); ?>
<main id="main">
  <div class="container">
    <?php
    $hero = [
        'crumbs'  => ['Trade' => 'trade', 'My Requests'],
        'eyebrow' => 'Trade Account',
        'title'   => 'My Requests',
        'lead'    => 'Everything you have sent us, and where each one has got to.',
    ];
    include __DIR__ . '/../../parts/page-header.php';
    include __DIR__ . '/_nav.php';
    ?>

    <?php if ($notice): ?><div class="form-status" style="margin-bottom:24px;"><?= e($notice) ?></div><?php endif; ?>

    <?php if (!$orders): ?>
      <div class="filter-empty" style="margin-bottom:96px;">
        <p>No requests yet. <a href="<?= $base_url ?>slabs">Browse the inventory</a> to get started.</p>
      </div>
    <?php else: ?>
      <div class="order-list pb-section">
        <?php foreach ($orders as $o): [$label, $tone] = order_status_label($o['status']); ?>
          <details class="order" id="o<?= (int) $o['id'] ?>"<?= $o === $orders[0] ? ' open' : '' ?>>
            <summary>
              <span class="order-ref">
                <strong><?= e($o['reference']) ?></strong>
                <span><?= (int) $o['line_count'] ?> material<?= $o['line_count'] == 1 ? '' : 's' ?> ·
                  <?= (int) $o['slab_count'] ?> slab<?= $o['slab_count'] == 1 ? '' : 's' ?></span>
              </span>

              <span class="order-when">
                <time datetime="<?= e($o['created_at']) ?>"><?= date('M j, Y', strtotime($o['created_at'])) ?></time>
                <?php if ($o['needed_by']): ?>
                  <span>Needed <?= date('M j', strtotime($o['needed_by'])) ?></span>
                <?php endif; ?>
              </span>

              <span class="order-state is-<?= e($o['status']) ?>"><?= e($label) ?></span>

              <svg class="order-chev" viewBox="0 0 24 24" aria-hidden="true">
                <polyline points="6 9 12 15 18 9" />
              </svg>
            </summary>

            <div class="order-body">
              <div class="table-scroll">
                <table class="order-table">
                  <thead><tr><th>Material</th><th>Slabs</th><th>Size</th><th>Notes</th></tr></thead>
                  <tbody>
                    <?php foreach (order_items($pdo, (int) $o['id']) as $i): ?>
                      <tr>
                        <td>
                          <?php if ($i['slab_slug'] !== ''): ?>
                            <a href="<?= $base_url ?>slabs/<?= e($i['slab_slug']) ?>"><?= e($i['slab_name']) ?></a>
                          <?php else: ?>
                            <?= e($i['slab_name']) ?>
                          <?php endif; ?>
                          <?php if ($i['slab_type'] !== ''): ?>
                            <span class="order-mat"><?= e(SITE_MATERIALS[$i['slab_type']]['label'] ?? $i['slab_type']) ?></span>
                          <?php endif; ?>
                        </td>
                        <td class="order-qty" data-label="Slabs"><?= (int) $i['quantity'] ?></td>
                        <td class="nowrap" data-label="Size"><?= e($i['size_note'] ?: '—') ?></td>
                        <td data-label="Notes"><?= e($i['item_notes'] ?: '—') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <?php if ($o['needed_by'] || $o['notes']): ?>
                <dl class="order-meta">
                  <?php if ($o['needed_by']): ?>
                    <div><dt>Needed by</dt><dd><?= date('j F Y', strtotime($o['needed_by'])) ?></dd></div>
                  <?php endif; ?>
                  <div><dt>Sent</dt><dd><?= date('j F Y \a\t g:i A', strtotime($o['created_at'])) ?></dd></div>
                </dl>
                <?php if ($o['notes']): ?>
                  <p class="order-note"><?= nl2br(e($o['notes'])) ?></p>
                <?php endif; ?>
              <?php endif; ?>

              <div class="order-actions">
                <a href="<?= $base_url ?>contact?inquiry=<?= urlencode($o['reference']) ?>" class="btn-outline">
                  Ask about this request
                </a>
                <span class="order-hint">
                  <?php if ($o['status'] === 'new'): ?>
                    We are checking availability — expect a reply within one business day.
                  <?php elseif ($o['status'] === 'quoted'): ?>
                    We have sent you a quote by email.
                  <?php elseif ($o['status'] === 'confirmed'): ?>
                    Confirmed. Your lots are held under this reference.
                  <?php elseif ($o['status'] === 'ready'): ?>
                    Ready for collection at our <?= e(site_city()) ?> gallery.
                  <?php elseif ($o['status'] === 'completed'): ?>
                    Completed. Thank you.
                  <?php else: ?>
                    This request was cancelled.
                  <?php endif; ?>
                </span>
              </div>
            </div>
          </details>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>
