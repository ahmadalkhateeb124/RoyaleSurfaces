<?php
/** Alphabetical term list. Each term is an anchor target so it can be linked to. */
$terms = [
  'A-Frame' => 'The steel rack slabs are transported and stored on. "Loading your A-frame" means securing slabs upright on the frame on your truck or trailer.',
  'Backsplash' => 'The surface between the countertop and the wall cabinets. A full-slab backsplash uses the same stone as the counter instead of tile, so there are no grout lines.',
  'Block' => 'The raw piece cut from the quarry face, before it is sawn into slabs. Buying a whole block guarantees every slab matches — the only reliable way to supply a large project.',
  'Bookmatch' => 'Two consecutive slabs opened like the pages of a book, so the veining mirrors across the seam. Cannot be recreated once the block is gone.',
  'Bundle' => 'The set of slabs cut from one block, kept together and numbered in sequence. Buying within a bundle is what makes lot matching possible.',
  'Calibration' => 'Grinding the back of a slab to a consistent thickness. Calibrated slabs sit flat and need less shimming during install.',
  'Chip' => 'A small break at an edge or corner, usually from handling. Inspect for chips at loading — once a slab leaves the yard, claims cannot be accepted.',
  'Cut-out' => 'A hole made in the slab for a sink, cooktop or outlet. Made at the fabrication shop, not on site.',
  'Dolomitic Marble' => 'A stone often sold as quartzite that is chemically closer to marble and will etch. Test with acid before committing a client to it.',
  'Edge Profile' => 'The shape machined onto the exposed edge — eased, bullnose, ogee, mitred. A mitred edge makes a 3cm slab look far thicker than it is.',
  'Etching' => 'A dull mark left when acid dissolves the surface of a calcium-based stone. It is physical damage, not a stain, so no sealer prevents it and cleaning will not remove it.',
  'Fissure' => 'A natural separation along the crystal structure. Normal in natural stone and not a defect, unlike a crack.',
  'Full-Slab Backsplash' => 'A backsplash cut from the slab directly above the counter piece so the veining runs unbroken up the wall.',
  'Honed' => 'A smooth matte finish with no shine. Hides etching and fingerprints far better than polished, which is why it suits marble.',
  'Leathered' => 'A textured finish with a soft sheen and slight ripple. Hides wear and water spots, and performs well outdoors.',
  'Lot Number' => 'The identifier tying a slab to its block and bundle. It is what lets us promise that twenty slabs will match months after you first saw them.',
  'Mitre' => 'Two pieces cut at 45° and joined to form a corner, used on waterfall legs and thick-look edges.',
  'Porosity' => 'How readily a stone absorbs liquid. High porosity means more sealing and faster staining. Engineered quartz and porcelain are effectively non-porous.',
  'Remnant' => 'A usable offcut left after a job. Ideal for vanities, small islands and hearths, usually at a substantial discount.',
  'Sealer' => 'An impregnating treatment that slows liquid absorption. It reduces staining; it does not prevent etching.',
  'Slab' => 'A single sheet cut from a block, typically around 120 by 70 inches, sold whole rather than by the square foot.',
  'Veining' => 'The mineral pattern running through natural stone. Directional veining produces the strongest waterfall and bookmatch effects.',
  'Waterfall' => 'An island where the countertop turns down the sides to the floor. Needs consecutive slabs so the veining carries around the mitre.',
];
ksort($terms);
$groups = [];
foreach ($terms as $term => $def) { $groups[strtoupper($term[0])][$term] = $def; }
?>

<nav class="glossary-jump" aria-label="Jump to letter">
  <?php foreach (range('A', 'Z') as $l): ?>
    <?php if (isset($groups[$l])): ?>
      <a href="#letter-<?= $l ?>"><?= $l ?></a>
    <?php else: ?>
      <span><?= $l ?></span>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>

<div class="pb-section">
  <?php foreach ($groups as $letter => $items): ?>
    <section class="glossary-group" id="letter-<?= e($letter) ?>">
      <h2 class="glossary-letter"><?= e($letter) ?></h2>
      <dl class="glossary-list">
        <?php foreach ($items as $term => $def): ?>
          <div id="term-<?= e(strtolower(str_replace(' ', '-', $term))) ?>">
            <dt><?= e($term) ?></dt>
            <dd><?= e($def) ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </section>
  <?php endforeach; ?>
</div>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'DefinedTermSet',
    'name' => 'Natural Stone Glossary',
    'hasDefinedTerm' => array_map(fn($t, $d) => [
        '@type' => 'DefinedTerm', 'name' => $t, 'description' => $d,
    ], array_keys($terms), array_values($terms)),
], JSON_UNESCAPED_SLASHES) ?>
</script>
