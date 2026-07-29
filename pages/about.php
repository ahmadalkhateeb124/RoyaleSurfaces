<?php
/** Quarry origins — each entry is a real sourcing keyword ("Brazilian quartzite"). */
$origins = [
    ['country' => 'Brazil',  'materials' => 'Quartzite &amp; exotic granite', 'note' => 'Taj Mahal, Macaubas, Via Lactea. Our largest single source and where most of our bookmatched pairs originate.'],
    ['country' => 'Italy',   'materials' => 'Classic white marble',           'note' => 'Carrara and Calacatta from the Apuan Alps, selected block by block rather than bought by container lot.'],
    ['country' => 'India',   'materials' => 'Granite',                        'note' => 'Absolute Black and the dense, consistent granites that carry our highest-volume commercial work.'],
    ['country' => 'Spain',   'materials' => 'Marble &amp; porcelain',         'note' => 'Nero Marquina, plus large-format porcelain from the Castellón manufacturing cluster.'],
    ['country' => 'Turkey',  'materials' => 'Travertine &amp; limestone',     'note' => 'Silver and walnut travertine in the honed finishes specified for spa and wet-room work.'],
    ['country' => 'Greece',  'materials' => 'Specialty marble',               'note' => 'Volakas and Thassos when a project calls for something outside the usual Italian palette.'],
];

/** Who we sell to — matches the search intent of each audience. */
$audiences = [
    ['title' => 'Stone Fabricators',   'body' => 'Our core customer. Deep stock of proven materials, lot-matched bundles, and loading that gets your truck turned around fast so your saw is not sitting idle.'],
    ['title' => 'Home Builders',       'body' => 'Repeatable material across a subdivision or spec-home program, with pricing held for the length of the build and stock reserved against your schedule.'],
    ['title' => 'Interior Designers',  'body' => 'Bring a client to the gallery to select their actual slab. We tag and hold it, then release it to whichever fabricator wins the job.'],
    ['title' => 'Commercial GCs',      'body' => 'Hotel, restaurant and multi-family work where two hundred units must match. We reserve full blocks and stage delivery to your construction sequence.'],
    ['title' => 'Homeowners',          'body' => 'Renovating your own kitchen or bath? Come pick the actual slab rather than a sample chip. We will tag it, hold it, and release it to whichever fabricator you hire.'],
];

/** How material moves from the quarry to a fabricator's A-frame. */
$journey = [
    ['n' => '01', 'title' => 'Quarry Selection',     'body' => 'We buy direct, not through brokers. On exotics we select blocks at the quarry so we know what is inside before it is ever sawn.'],
    ['n' => '02', 'title' => 'Pre-Ship Inspection',  'body' => 'Bundles are photographed and checked for cracks, fissures and colour drift before the container is sealed for shipping.'],
    ['n' => '03', 'title' => 'Receiving &amp; Rejection', 'body' => 'Every container is re-inspected on arrival in Dallas. Material that does not meet grade is rejected outright rather than pushed onto the floor.'],
    ['n' => '04', 'title' => 'Catalogue by Lot',     'body' => 'Each slab is tagged with its bundle and lot number the day it lands, which is what makes guaranteed matching possible months later.'],
    ['n' => '05', 'title' => 'Gallery Staging',      'body' => 'Slabs are racked upright under consistent lighting so what you approve in the gallery is what arrives at your shop.'],
];
?>

<main id="main">
  <div class="container">

        <?php
    $hero = [
        'crumbs'  => ['About'],
        'eyebrow' => 'Who We Are',
        'title'   => 'We Buy at the Block.',
        'lead'    => 'Royale Surfaces is a wholesale natural stone supplier in ' . e(site_city()) . ', Texas. We exist to
            solve a single problem: get consistent, high-grade stone into your hands without the markup and the
            runaround.',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <div class="about-2col" data-reveal>
      <?php
      $mediaVideo    = 'about.mp4';
      $mediaFallback = 'about-warehouse.jpg';
      $mediaAlt      = 'Slab bundles racked by lot number inside the Royale Surfaces wholesale stone '
                     . 'warehouse in Dallas, Texas';
      include __DIR__ . '/../parts/media.php';
      ?>
      <div class="about-body">
        <span class="section-label">Our Ethos</span>
        <h2>Wholesale Pricing, Whoever You Are.</h2>
        <p>Plenty of stone yards in Texas quote one price to a fabricator and a very different one to the homeowner who
          walks in behind them. We do not run two price lists. What changes your number here is volume and nothing
          else — the same tiers apply whether you buy fifty slabs a month or one for your own kitchen.</p>
        <p>Because we import directly and hold deep stock in a focused set of materials rather than one slab of
          everything, we can keep pricing sharp across the board. Fabricators, builders and designers make up most of
          our volume and earn the deepest tiers, but the door is open to anyone sourcing stone for a real project.</p>
        <p>What that buys you in practice is a warehouse organised for selecting rather than wandering — wide aisles,
          forklift access to every rack, slabs stored upright under consistent light, and staff who understand seam
          placement, calibration tolerance and why a 2cm and a 3cm slab are not interchangeable on your job.</p>
      </div>
    </div>

    <div class="stats-box" data-reveal>
      <div class="stats-grid">
        <div>
          <div class="stat-number">50k+</div>
          <div class="stat-label">Sq Ft Facility</div>
          <div class="stat-desc">Indoor, well-lit viewing gallery and a covered stock yard in the Dallas Design
            District.</div>
        </div>
        <div>
          <div class="stat-number">2,000+</div>
          <div class="stat-label">Slabs in Stock</div>
          <div class="stat-desc">Immediate availability across all seven material categories, restocked weekly.</div>
        </div>
        <div>
          <div class="stat-number">6</div>
          <div class="stat-label">Countries Sourced</div>
          <div class="stat-desc">Imported direct from quarries in Brazil, Italy, India, Spain, Turkey and Greece.</div>
        </div>
      </div>
    </div>

    <!-- SOURCING -->
    <section class="section-pad" style="padding-top:0;">
      <div class="section-head" data-reveal>
        <div>
          <span class="section-label">Direct Import</span>
          <h2>Where Our Stone Comes From</h2>
        </div>
      </div>
      <p class="lead" style="margin-bottom:40px;">We import directly from quarries and manufacturers rather than buying
        from domestic distributors. That removes a markup layer, but more importantly it means we control which blocks
        end up on our floor instead of receiving whatever a broker had left.</p>

      <div class="origin-grid">
        <?php foreach ($origins as $o): ?>
          <div class="origin-card" data-reveal>
            <h3><?= $o['country'] ?></h3>
            <span class="origin-materials"><?= $o['materials'] ?></span>
            <p><?= $o['note'] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- JOURNEY -->
    <section class="process-box" data-reveal>
      <h2>From Quarry to Your A-Frame</h2>
      <p class="lead" style="text-align:center;margin:0 auto 48px;">Five checkpoints stand between a block in a quarry
        and a slab on your saw. Skipping any one of them is how inconsistent material reaches a fabricator.</p>
      <div class="process-steps process-steps-5">
        <?php foreach ($journey as $j): ?>
          <div class="process-step">
            <div class="step-num"><?= $j['n'] ?></div>
            <div class="step-title"><?= $j['title'] ?></div>
            <p class="step-desc"><?= $j['body'] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- AUDIENCES -->
    <section class="section-pad">
      <div class="section-head" data-reveal>
        <div>
          <span class="section-label">Our Customers</span>
          <h2>Who We Supply</h2>
        </div>
        <a href="<?= $base_url ?>contact" class="btn-text">
          Get in touch
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="4" y1="12" x2="19" y2="12" />
            <polyline points="13 6 19 12 13 18" />
          </svg>
        </a>
      </div>

      <div class="mini-grid">
        <?php foreach ($audiences as $a): ?>
          <div class="mini-card" data-reveal>
            <h3><?= e($a['title']) ?></h3>
            <p><?= e($a['body']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- FACILITY -->
    <section class="section-pad" style="padding-top:0;">
      <div class="about-2col" data-reveal style="margin-bottom:0;">
        <div class="about-body">
          <span class="section-label">The Facility</span>
          <h2>Inside Our Dallas Gallery</h2>
          <p>Our 26,900 square foot facility sits in the Dallas Design District, minutes from I-35 and Stemmons — close
            enough that most DFW shops can run a pickup and be back at the saw inside a morning.</p>
          <ul class="check-list">
            <li>Indoor climate-managed viewing gallery with consistent colour-accurate lighting</li>
            <li>Slabs racked upright and full-face visible — no digging through stacks</li>
            <li>Industrial overhead cranes and a crew that loads A-frames daily</li>
            <li>Covered outdoor stock yard for bulk granite and commercial-volume bundles</li>
            <li>Dedicated staging bays where reserved lots are held under your account name</li>
            <li>On-site parking and turnaround room for full-size flatbeds and trailers</li>
          </ul>
          <p>You are welcome to walk the floor unaccompanied. If you would rather have someone pull options
            before you arrive, send your material list ahead and we will have candidates staged and lit when you get
            here.</p>
        </div>
        <?php
        $mediaVideo    = 'about1.mp4';
        $mediaClip     = 15;              // only the opening is worth showing
        $mediaFallback = 'hero-stone.jpg';
        $mediaAlt      = 'Natural stone slab surface detail showing veining and finish quality';
        include __DIR__ . '/../parts/media.php';
        ?>
      </div>
    </section>

    <!-- PILLARS -->
    <div class="feature-grid">
      <div class="feature-card" data-reveal>
        <h3>Quality Sourcing</h3>
        <p>Direct quarry relationships give us first access to the finest material. We don't buy from middlemen — we
          travel to the source and select blocks ourselves, which is the only way to know what the veining looks like
          before the saw opens it.</p>
      </div>
      <div class="feature-card" data-reveal>
        <h3>Lot Consistency</h3>
        <p>Every slab is catalogued by bundle and lot number the day it arrives. When you need twenty matching slabs for
          a hotel project six months from now, that record is what makes the guarantee possible rather than hopeful.</p>
      </div>
      <div class="feature-card" data-reveal>
        <h3>Long-Term Partnership</h3>
        <p>We grow when you grow. Tiered pricing rewards volume buyers and repeat customers with early access to new
          arrivals, first refusal on exotics, and reservation windows that match how your jobs actually close.</p>
      </div>
    </div>

  </div>
</main>

<section class="cta-banner">
  <div class="container">
    <h2>Start Your Project</h2>
    <p>Tell us what you are building and we will come back with material options, availability and pricing.</p>
    <a href="<?= $base_url ?>contact" class="btn-dark">Contact Our Team</a>
  </div>
</section>
