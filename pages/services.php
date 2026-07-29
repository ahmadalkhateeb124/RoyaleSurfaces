<?php
/**
 * Services. Each entry carries a bullet list — those lines are where the
 * long-tail search terms live ("slab reservation", "container programme",
 * "full-slab photos") without stuffing them into the prose.
 */
$services = [
    [
        'title' => 'Wholesale Slab Supply',
        'icon'  => '<path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>',
        'body'  => 'Access thousands of slabs held in our Dallas facility across granite, quartzite, marble, engineered
                    quartz, porcelain, natural stone and solid surfaces. We deliberately hold depth in a smaller set of
                    proven materials rather than one slab of everything, so your recurring work never stalls waiting on
                    a container.',
        'points' => [
            'Full bundles and lot-matched sets from a single block',
            'Calibrated 2cm and 3cm stock ready for immediate fabrication',
            'Weekly restocking with new container arrivals',
            'Remnant and partial-bundle pricing for smaller jobs',
        ],
    ],
    [
        'title' => 'Wholesale Volume Pricing',
        'icon'  => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'body'  => 'Tiered pricing driven by volume, not by who you are. We run one price list, so the number you are
                    quoted reflects what you buy rather than which door you came through. For fabricators and builders
                    that means margins you can rely on when you bid.',
        'points' => [
            'Volume tiers based on rolling quarterly totals, not single orders',
            'Pricing held for the duration of a quoted project',
            'One price list — your tier depends on volume, nothing else',
            'Net terms available on established business accounts',
        ],
    ],
    [
        'title' => 'Custom Quarry Sourcing',
        'icon'  => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'body'  => 'Working on a large commercial build or a high-end residential estate that needs something we do not
                    stock? Our direct relationships with quarries in Brazil, Italy, India, Spain and Turkey let us
                    source specific exotics or large quantities of uniform material to your specification.',
        'points' => [
            'Block-level selection at the quarry for exotic material',
            'Pre-shipment photography and inspection before the container seals',
            'Realistic lead times quoted up front, not optimistic ones',
            'Full-block purchase for projects that must match across hundreds of units',
        ],
    ],
    [
        'title' => 'Dedicated Account Management',
        'icon'  => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'body'  => 'Every regular customer gets one named contact who learns your work — the materials you
                    repeat, the thicknesses you run, the lead times your builders hold you to. You are not re-explaining
                    your business to whoever answers the phone.',
        'points' => [
            'Full-slab photography sent before you commit to a lot',
            'Physical samples couriered to your shop or your client',
            'Accurate technical data: thickness, finish, absorption, origin',
            'Proactive alerts when material you buy repeatedly lands',
        ],
    ],
    [
        'title' => 'Slab Reservation &amp; Holds',
        'icon'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'body'  => 'Natural stone is finite — a slab your client fell in love with on Tuesday can be gone by Friday.
                    We can tag and hold specific lots while a job is being finalised, with the hold confirmed
                    in writing rather than agreed verbally at the counter.',
        'points' => [
            'Standard 14-day holds on identified lots',
            'Extended holds on established accounts and larger projects',
            'Block-matching guaranteed across everything you reserved',
            'Client-selected slabs tagged and released to whichever fabricator you hire',
        ],
    ],
    [
        'title' => 'Loading, Delivery &amp; Logistics',
        'icon'  => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        'body'  => 'Industrial overhead cranes and a crew that loads A-frames every working day. Most DFW shops run a
                    pickup and are back at the saw the same morning. For partners further out, we arrange delivery
                    across Texas and dedicated container logistics for ongoing sourcing programmes.',
        'points' => [
            'Same-day loading during opening hours, no appointment needed',
            'Delivery across DFW, Houston, Austin, San Antonio and beyond',
            'Staged delivery sequenced to a commercial construction schedule',
            'Direct-to-site container programmes for volume partners',
        ],
    ],
];

/** Service-specific questions. Visible content first; schema mirrors it below. */
$faq = [
    [
        'q' => 'Do I need to be a fabricator to buy from you?',
        'a' => 'No. Fabricators, builders and designers make up most of our volume, but homeowners and private clients are welcome to visit and buy directly. If you are a business and want a trading account with volume tiers and net terms, send us your company details and a resale certificate — verification usually takes one business day.',
    ],
    [
        'q' => 'Do I need an appointment to visit the gallery?',
        'a' => 'No. Anyone can walk the floor during opening hours without booking. If you would like slabs pulled and staged before you arrive, send your material list ahead and we will have candidates lit and ready.',
    ],
    [
        'q' => 'Can you hold material while I wait on a client decision?',
        'a' => 'Yes. Standard holds run 14 days on identified lots, confirmed in writing. Established accounts and larger commercial projects can negotiate longer reservation windows.',
    ],
    [
        'q' => 'Do you fabricate or install?',
        'a' => 'No — we supply slabs only. We are happy to advise on material suitability and can introduce you to fabricators we work with, but templating, cutting, seam placement and installation stay with the fabrication shop.',
    ],
    [
        'q' => 'What if a slab arrives damaged?',
        'a' => 'Material must be inspected at the time of loading, which is why our crew stages every slab full-face before it goes on the A-frame. Anything you flag at the yard we replace or credit on the spot.',
    ],
    [
        'q' => 'Do you deliver outside the Dallas area?',
        'a' => 'Yes. We deliver across Texas, including Houston, Austin and San Antonio, and we arrange dedicated container logistics for partners running ongoing sourcing programmes.',
    ],
];
?>

<main id="main">
  <div class="container">

        <?php
    $hero = [
        'crumbs'  => ['Services'],
        'eyebrow' => 'What We Offer',
        'title'   => 'Our Services',
        'lead'    => 'End-to-end wholesale supply for Texas fabricators, builders, designers and homeowners. From
            custom quarry sourcing to dedicated container logistics, we handle the material so you can focus on the
            build.',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <div class="services-grid">
      <?php foreach ($services as $s): ?>
        <div class="service-item" data-reveal>
          <div class="service-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><?= $s['icon'] ?></svg>
          </div>
          <h3><?= $s['title'] ?></h3>
          <p><?= $s['body'] ?></p>
          <ul class="check-list">
            <?php foreach ($s['points'] as $point): ?>
              <li><?= $point ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="process-box" data-reveal>
      <h2>The Sourcing Process</h2>
      <div class="process-steps">
        <div class="process-step">
          <div class="step-num">01</div>
          <div class="step-title">Apply</div>
          <p class="step-desc">Tell us what you are building. Businesses can register for volume tiers; private clients
            can simply book a visit.</p>
        </div>
        <div class="process-step">
          <div class="step-num">02</div>
          <div class="step-title">Select</div>
          <p class="step-desc">Browse inventory online or walk our <?= e(site_city()) ?> gallery to view physical slabs
            full-face under consistent lighting.</p>
        </div>
        <div class="process-step">
          <div class="step-num">03</div>
          <div class="step-title">Reserve</div>
          <p class="step-desc">Hold specific lots for up to 14 days while the job closes. Block-matching is guaranteed
            across everything you tag.</p>
        </div>
        <div class="process-step">
          <div class="step-num">04</div>
          <div class="step-title">Load</div>
          <p class="step-desc">Schedule pickup or delivery. Our cranes and crew load your A-frames safely, and you
            inspect every slab before it leaves.</p>
        </div>
      </div>
    </div>

    <!-- COMMERCIAL -->
    <section class="section-pad" style="padding-top:0;">
      <div class="about-2col" data-reveal style="margin-bottom:0;">
        <img src="<?= e(asset('assets/images/about-warehouse.jpg')) ?>"
          alt="Commercial volume stone slab bundles staged for a Texas construction project" loading="lazy" width="800"
          height="1000" />
        <div class="about-body">
          <span class="section-label">Commercial Projects</span>
          <h2>Volume Work, Sequenced Properly</h2>
          <p>Multi-family, hospitality and restaurant work fails on consistency, not on price. Two hundred units that
            each look slightly different is a callback problem no discount recovers.</p>
          <p>For commercial programmes we reserve whole blocks rather than individual slabs, hold them under your
            project name, and release material in stages that match your construction sequence — so you are not paying
            to store six months of stone on a jobsite.</p>
          <ul class="check-list">
            <li>Full-block reservation for guaranteed unit-to-unit consistency</li>
            <li>Staged release aligned to your build schedule</li>
            <li>Consolidated invoicing across project phases</li>
            <li>Specification support and material data for architects</li>
          </ul>
          <a href="<?= $base_url ?>contact" class="btn-text">
            Discuss a commercial programme
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <line x1="4" y1="12" x2="19" y2="12" />
              <polyline points="13 6 19 12 13 18" />
            </svg>
          </a>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="section-pad" style="padding-top:0;">
      <div class="faq-layout">
        <div data-reveal>
          <span class="section-label">Questions</span>
          <h2>Working With Us</h2>
          <p class="lead">Practical answers about accounts, holds, delivery and what we do and don't do. Anything not
            covered here, <a href="<?= $base_url ?>contact" style="color:var(--accent);">ask our team</a>.</p>
        </div>
        <div class="faq-list" data-reveal>
          <?php foreach ($faq as $i => $f): ?>
            <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
              <summary><?= e($f['q']) ?></summary>
              <p><?= e($f['a']) ?></p>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <div class="services-cta pb-section">
      <a href="<?= $base_url ?>contact" class="btn-primary">Partner With Us</a>
    </div>

  </div>
</main>

<script type="application/ld+json">
<?= json_encode([
    '@context'   => 'https://schema.org',
    '@graph'     => [
        [
            '@type'       => 'Service',
            'name'        => 'Wholesale Natural Stone Slab Supply',
            'serviceType' => 'Wholesale stone distribution',
            'provider'    => ['@id' => $base_url . '#business'],
            'areaServed'  => array_map(fn($c) => ['@type' => 'City', 'name' => $c], SITE_AREAS),
            'description' => 'Wholesale supply of granite, quartzite, marble, quartz, porcelain and natural stone slabs to fabricators, builders, designers and homeowners across Texas.',
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name'  => 'Wholesale Stone Services',
                'itemListElement' => array_map(fn($s) => [
                    '@type'       => 'Offer',
                    'itemOffered' => ['@type' => 'Service', 'name' => strip_tags(html_entity_decode($s['title']))],
                ], $services),
            ],
        ],
        [
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(fn($f) => [
                '@type'          => 'Question',
                'name'           => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $faq),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
