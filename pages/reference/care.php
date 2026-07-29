<?php
$care = [
  'granite'   => ['seal' => 'Once a year', 'heat' => 'Excellent', 'acid' => 'Resistant',
    'do' => ['Wipe with warm water and a pH-neutral stone cleaner', 'Reseal when the water test shows darkening', 'Use it outdoors without concern'],
    'avoid' => ['Vinegar, bleach and general bathroom sprays', 'Abrasive pads on a polished finish']],
  'quartzite' => ['seal' => 'Once a year', 'heat' => 'Excellent', 'acid' => 'Resistant if true quartzite',
    'do' => ['Confirm it is true quartzite, not dolomitic marble', 'Seal annually, more often on light colours', 'Wipe oil and wine promptly'],
    'avoid' => ['Assuming every stone sold as quartzite behaves the same', 'Acidic cleaners until you have verified the material']],
  'marble'    => ['seal' => 'Twice a year', 'heat' => 'Good', 'acid' => 'Etches readily',
    'do' => ['Choose honed over polished to disguise etching', 'Blot spills rather than wiping them across', 'Set the expectation of patina at handover'],
    'avoid' => ['Lemon, vinegar, wine and tomato left sitting', 'Believing a sealer will stop etching — it will not']],
  'quartz'    => ['seal' => 'Never needed', 'heat' => 'Poor — scorches', 'acid' => 'Resistant',
    'do' => ['Clean with soap and water', 'Always use a trivet under hot cookware', 'Keep it indoors'],
    'avoid' => ['Direct heat from pans or appliances', 'Outdoor installation — UV yellows the resin permanently']],
  'porcelain' => ['seal' => 'Never needed', 'heat' => 'Excellent', 'acid' => 'Immune',
    'do' => ['Clean with almost anything', 'Use indoors or out', 'Support large-format sheets properly during install'],
    'avoid' => ['Sharp impact on unsupported edges', 'Cutting it without a fabricator experienced in porcelain']],
  'natural-stone' => ['seal' => 'Twice a year', 'heat' => 'Good', 'acid' => 'Varies — usually sensitive',
    'do' => ['Seal travertine and limestone more often than granite', 'Fill open pores if you want a smooth surface', 'Use pH-neutral cleaner only'],
    'avoid' => ['Acidic products of any kind', 'Standing water on honed limestone']],
];
?>

<div class="section-pad" style="padding-top:0;">
  <div class="prose" style="max-width:78ch;" data-reveal>
    <h2>The rule that covers ninety per cent of it</h2>
    <p>Warm water and a pH-neutral stone cleaner. Nothing acidic, nothing abrasive. Most surface damage we see was caused by a cleaning product, not by the stone failing.</p>
    <p>A sealer slows absorption so spills can be wiped before they stain. It does not stop etching — etching is acid dissolving the surface, which is physical damage a coating cannot prevent.</p>
    <h2>The water test</h2>
    <p>Leave a few drops of water on the surface for fifteen minutes and wipe it off. If the stone underneath has darkened, it is ready to be resealed. Takes a minute and beats guessing.</p>
  </div>
</div>

<div class="pb-section">
  <?php foreach ($care as $key => $c): ?>
    <?php $m = SITE_MATERIALS[$key] ?? null; if (!$m) continue; ?>
    <section class="care-block" data-reveal>
      <div class="care-head">
        <h2><?= e($m['label']) ?></h2>
        <a href="<?= $base_url . e($key) ?>" class="btn-text">
          View <?= e($m['label']) ?>
          <svg viewBox="0 0 24 24" aria-hidden="true"><line x1="4" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/></svg>
        </a>
      </div>
      <dl class="care-specs">
        <div><dt>Sealing</dt><dd><?= e($c['seal']) ?></dd></div>
        <div><dt>Heat</dt><dd><?= e($c['heat']) ?></dd></div>
        <div><dt>Acid</dt><dd><?= e($c['acid']) ?></dd></div>
      </dl>
      <div class="care-cols">
        <div><h3>Do</h3><ul class="check-list"><?php foreach ($c['do'] as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul></div>
        <div><h3>Avoid</h3><ul class="cross-list"><?php foreach ($c['avoid'] as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul></div>
      </div>
    </section>
  <?php endforeach; ?>
</div>
