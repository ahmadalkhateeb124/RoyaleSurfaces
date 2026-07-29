<?php
$steps = [
  ['Set the thickness and the edge', 'These narrow the field before you fall in love with anything. Most kitchens are 3cm; vanities are often 2cm. A mitred edge changes both budget and slab count.'],
  ['Choose the material by how the room is used', 'Heat, acid and traffic decide this, not colour. An outdoor kitchen rules out engineered quartz entirely. A busy family kitchen argues against polished marble. Narrow to two or three materials before looking at slabs.'],
  ['Measure roughly and work back to slab count', 'Bring approximate dimensions. We work back to how many slabs the job needs, allowing for cut-outs, seams and matching. Most kitchens land between two and three.'],
  ['Select your actual slabs in person', 'Natural stone varies piece to piece. A sample chip tells you almost nothing about how a full slab will look. Walk the gallery and pick the pieces you are buying.'],
  ['Tag and hold them', 'Once you have chosen, we tag those specific slabs under your name and hold them while the job is finalised. Standard holds run 14 days.'],
  ['Get your fabricator to template', 'Templating happens after cabinets are installed and level. The fabricator produces an exact pattern — this is the point where seam placement and veining direction get decided.'],
  ['Inspect at loading', 'Whether you collect or your fabricator does, material is inspected as it goes on the A-frame. Once slabs leave the yard, claims for visible damage cannot be accepted.'],
];
?>

<div class="pb-section">
  <div class="process-box" data-reveal>
    <h2>The Order to Do It In</h2>
    <div class="guide-steps">
      <?php foreach ($steps as $i => [$t, $d]): ?>
        <div class="guide-step">
          <div class="step-num"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></div>
          <div><h3><?= e($t) ?></h3><p class="step-desc"><?= e($d) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="prose" style="max-width:78ch;">
    <h2>What to ask a fabricator</h2>
    <ul>
      <li>Where will the seams fall, and can I see that on the template?</li>
      <li>Will the veining be matched across the seam?</li>
      <li>Is the edge included in the quote, or priced separately?</li>
      <li>Who is responsible if a slab breaks during fabrication?</li>
      <li>What is the lead time from template to install?</li>
    </ul>

    <h2>Where the budget actually goes</h2>
    <p>The slab is usually less than half the finished cost. Fabrication, edge work, cut-outs, delivery and installation make up the rest. A cheaper stone with an elaborate edge can easily cost more installed than a better stone with a simple one.</p>

    <h2>The mistake we see most</h2>
    <p>Buying one slab at a time as the job progresses. Stone is finite — the block your first slab came from sells out, and the replacement never quite matches. Buy the whole job at once, or reserve the lot up front.</p>

    <p><a href="<?= $base_url ?>contact">Bring us your dimensions</a> and we will tell you honestly what the job needs.</p>
  </div>
</div>

<script type="application/ld+json">
<?= json_encode([
    '@context'      => 'https://schema.org',
    '@type'         => 'HowTo',
    'name'          => 'How to Buy Stone Countertop Slabs',
    'description'   => 'The order to buy granite, marble or quartz countertop slabs in, from setting thickness to inspecting at loading.',
    'dateModified'  => SITE_GUIDES_REVIEWED,
    'step'          => array_map(fn($s) => [
        '@type' => 'HowToStep',
        'name'  => $s[0],
        'text'  => $s[1],
    ], $steps),
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
