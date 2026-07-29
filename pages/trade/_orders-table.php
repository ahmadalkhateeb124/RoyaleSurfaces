<?php /** Expects $recent (array of order rows). */ ?>
<div class="table-scroll">
  <table class="compare-table">
    <thead>
      <tr><th>Reference</th><th>Sent</th><th>Items</th><th>Needed by</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php foreach ($recent as $o): [$label, $tone] = order_status_label($o['status']); ?>
        <tr>
          <td><a href="<?= $base_url ?>trade/orders#o<?= (int) $o['id'] ?>" class="row-link"><?= e($o['reference']) ?></a></td>
          <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
          <td><?= (int) $o['line_count'] ?> line<?= $o['line_count'] == 1 ? '' : 's' ?> · <?= (int) $o['slab_count'] ?> slab<?= $o['slab_count'] == 1 ? '' : 's' ?></td>
          <td><?= $o['needed_by'] ? date('M j, Y', strtotime($o['needed_by'])) : '—' ?></td>
          <td><span class="order-state is-<?= e($o['status']) ?>"><?= e($label) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
