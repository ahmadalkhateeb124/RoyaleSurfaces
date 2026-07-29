<?php
$finishes = [
  ['Polished', 'Ground and buffed to a full gloss. Deepens colour and makes veining pop, and the closed surface resists staining best.',
   'Formal kitchens, bathrooms, anywhere you want maximum depth of colour.',
   'Shows fingerprints, water spots and etching more than any other finish. Becomes a mirror in direct sun.'],
  ['Honed', 'Ground smooth and stopped before the gloss. Soft, matte, contemporary.',
   'Marble that will see daily use; floors where polished would be slippery.',
   'More open surface, so seal it properly. Light honed stone shows oil marks.'],
  ['Leathered', 'Brushed to a subtle texture with a low sheen. You feel the crystal structure under your hand.',
   'Dark granite, outdoor kitchens, busy family kitchens where fingerprints matter.',
   'Texture holds a little more dust; not every stone takes leathering well.'],
  ['Brushed', 'Similar to leathered but softer and more uniform. A gentle satin.',
   'Limestone and travertine where a full polish would look wrong.',
   'Less dramatic than leathering — the difference reads mostly by touch.'],
  ['Matte', 'A flat, low-reflectance finish, most common on engineered quartz and porcelain.',
   'Modern schemes; rentals and commercial work where marks must not show.',
   'Some matte quartz shows grease marks that wipe off but reappear.'],
];
?>

<div class="pb-section">
  <?php foreach ($finishes as $i => $f): ?>
    <section class="finish-row" data-reveal>
      <div class="finish-num"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></div>
      <div class="finish-body">
        <h2><?= e($f[0]) ?></h2>
        <p class="finish-desc"><?= e($f[1]) ?></p>
        <dl class="finish-meta">
          <div><dt>Best for</dt><dd><?= e($f[2]) ?></dd></div>
          <div><dt>Watch out</dt><dd><?= e($f[3]) ?></dd></div>
        </dl>
      </div>
    </section>
  <?php endforeach; ?>

  <div class="panel">
    <h2>Finishes look different in person</h2>
    <p>A photograph cannot show sheen. The gap between honed and leathered is obvious under your hand and almost invisible on a screen — which is why we keep samples of each finish on the floor.</p>
    <a href="<?= $base_url ?>contact" class="btn-primary">See Them in the Gallery</a>
  </div>
</div>
