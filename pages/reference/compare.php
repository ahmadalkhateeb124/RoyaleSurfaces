<?php
$rows = [
  ['Granite',        'Natural',    'Very high', 'Excellent', 'Low',      'Yes', 'Annual',   '$$',   'granite'],
  ['Quartzite',      'Natural',    'Very high', 'Excellent', 'Low',      'Yes', 'Annual',   '$$$',  'quartzite'],
  ['Marble',         'Natural',    'Medium',    'Good',      'Medium',   'No',  'Twice/yr', '$$$',  'marble'],
  ['Quartz',         'Engineered', 'High',      'Poor',      'None',     'No',  'Never',    '$$',   'quartz'],
  ['Porcelain',      'Engineered', 'Very high', 'Excellent', 'None',     'Yes', 'Never',    '$$$',  'porcelain'],
  ['Solid Surface',  'Engineered', 'Low',       'Poor',      'None',     'No',  'Never',    '$',    'solid-surfaces'],
];
?>

<div class="table-scroll pb-section">
  <table class="compare-table">
    <thead>
      <tr><th>Material</th><th>Type</th><th>Hardness</th><th>Heat</th><th>Porosity</th><th>Outdoor</th><th>Sealing</th><th>Price</th></tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><a href="<?= $base_url . e($r[8]) ?>" class="row-link"><?= e($r[0]) ?></a></td>
          <td><?= e($r[1]) ?></td>
          <td><?= e($r[2]) ?></td>
          <td class="<?= $r[3] === 'Poor' ? 'is-bad' : ($r[3] === 'Excellent' ? 'is-good' : '') ?>"><?= e($r[3]) ?></td>
          <td><?= e($r[4]) ?></td>
          <td class="<?= $r[5] === 'Yes' ? 'is-good' : 'is-bad' ?>"><?= e($r[5]) ?></td>
          <td><?= e($r[6]) ?></td>
          <td><?= e($r[7]) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="prose" style="margin-top:44px;max-width:78ch;">
    <h2>Reading this table</h2>
    <p><strong>Heat</strong> is the column that catches people out. Engineered quartz is excellent at almost everything except heat — a pan straight off the burner leaves a permanent mark, and no warranty covers it.</p>
    <p><strong>Outdoor</strong> is binary, not a preference. UV destroys the resin in engineered quartz and solid surface. Outdoors means granite, porcelain or quarried natural stone.</p>
    <p><strong>Porosity</strong> drives how much maintenance the owner actually does. Non-porous materials never need sealing, which matters more in a rental or a restaurant than in an owner-occupied kitchen.</p>
    <p>Price is relative and moves with the specific stone — an exotic quartzite can cost several times a common granite. <a href="<?= $base_url ?>contact">Ask us for current pricing</a> on anything you are considering.</p>
  </div>
</div>
