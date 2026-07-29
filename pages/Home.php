<?php
require_once __DIR__ . '/../inc/posts.php';
require_once __DIR__ . '/../inc/faqs.php';

$homeFaqs = faqs_home();
?>

<main id="main">

  <!-- HERO -->
  <section class="hero">
    <img class="hero-img" src="<?= e(asset('assets/images/hero-home.jpg')) ?>"
      alt="Dark textured natural stone slab surface" fetchpriority="high" width="1920" height="1080" />
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <span class="hero-eyebrow">Wholesale · <?= e(site_city()) ?>, <?= e(site_state()) ?></span>
      <h1>Texas Stone,<br />Straight from the Quarry.</h1>
      <p>Full-slab inventory, lot-matched sourcing and wholesale pricing for Texas fabricators, contractors,
        designers and homeowners. Buy one slab or a full container.</p>
      <div class="hero-ctas">
        <a href="<?= $base_url ?>slabs" class="btn-primary">View Inventory</a>
        <a href="<?= $base_url ?>contact" class="btn-outline">Schedule a Visit</a>
      </div>
    </div>
  </section>

  <!-- TRUST SIGNALS -->
  <section class="trust-section">
    <div class="container">
      <div class="trust-grid">
        <div class="trust-item" data-reveal>
          <div class="trust-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
            </svg>
          </div>
          <h3>Precision Calibration</h3>
          <p>Extensive stock of perfectly calibrated 2cm and 3cm slabs, gauged and ready for seamless fabrication
            straight off the A-frame.</p>
        </div>
        <div class="trust-item" data-reveal>
          <div class="trust-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
          </div>
          <h3>Guaranteed Lot Matching</h3>
          <p>We source and catalogue by bundle and lot number, so a 20-slab commercial run matches across every single
            piece — no surprises on the saw.</p>
        </div>
        <div class="trust-item" data-reveal>
          <div class="trust-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <rect x="1" y="3" width="15" height="13" />
              <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
              <circle cx="5.5" cy="18.5" r="2.5" />
              <circle cx="18.5" cy="18.5" r="2.5" />
            </svg>
          </div>
          <h3>Same-Day Loading</h3>
          <p>26,900 sq ft indoor gallery with industrial cranes and an experienced crew. Pull up, get loaded, get back
            to
            the shop.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- WHY US -->
  <section class="section-pad">
    <div class="container">
      <div class="about-2col" data-reveal>
        <?php
        $mediaVideo    = 'about.mp4';
        $mediaFallback = 'about-warehouse.jpg';
        $mediaAlt      = 'Slab bundles organised by lot number inside the Royale Surfaces Dallas warehouse';
        include __DIR__ . '/../parts/media.php';
        ?>
        <div class="about-body">
          <span class="section-label">Why Royale Surfaces</span>
          <h2>A Supplier That Works Like a Shop.</h2>
          <p>Most stone yards are built for browsing. Ours is built for selecting — wide aisles, forklift access, slabs
            stored upright under proper lighting, and staff who know the difference between a bookmatched pair and two
            slabs that happen to look similar.</p>
          <p>We import directly from quarries in Brazil, Italy, India and Spain. Every container is inspected before it
            hits our floor, and inconsistent material is rejected at the door rather than passed on to you. What reaches
            our gallery is material we would be comfortable cutting ourselves.</p>
          <p>Buying direct means fewer hands between the quarry and your job — and pricing that reflects it, whether
            you are fitting one kitchen or twenty.</p>
          <a href="<?= $base_url ?>about" class="btn-text">
            More about our operation
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <line x1="4" y1="12" x2="19" y2="12" />
              <polyline points="13 6 19 12 13 18" />
            </svg>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- MATERIAL CATEGORIES -->
  <section class="categories-section">
    <div class="container">
      <div class="section-head" data-reveal>
        <div>
          <span class="section-label">Materials</span>
          <h2>Premium Stone Categories</h2>
        </div>
        <a href="<?= $base_url ?>slabs" class="btn-text">
          Browse full inventory
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="4" y1="12" x2="19" y2="12" />
            <polyline points="13 6 19 12 13 18" />
          </svg>
        </a>
      </div>

      <div class="categories-grid">
        <?php foreach (SITE_MATERIALS as $slug => $m): ?>
          <a href="<?= $base_url ?>slabs?type=<?= e($slug) ?>" class="category-card" data-reveal>
            <img src="<?= e(asset('assets/images/' . $m['image'])) ?>"
              alt="<?= e($m['label']) ?> slab available at Royale Surfaces Dallas" loading="lazy" width="600"
              height="800" />
            <div class="category-overlay">
              <h3><?= e($m['label']) ?></h3>
              <span><?= e($m['kind']) ?></span>
            </div>
          </a>
        <?php endforeach; ?>

        <!-- Fills the 8th cell so the 7 categories read as a complete grid -->
        <a href="<?= $base_url ?>slabs" class="category-card category-cta" data-reveal>
          <span class="section-label">All Materials</span>
          <h3>View Full Inventory</h3>
          <p>Every slab on the floor, filterable by category.</p>
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="4" y1="12" x2="19" y2="12" />
            <polyline points="13 6 19 12 13 18" />
          </svg>
        </a>
      </div>

      <p class="section-note" data-reveal>Granite for heat and outdoor exposure. Quartzite when a client wants marble
        looks that survive a working kitchen. Marble where the patina is the point. Engineered quartz when every unit in
        a build has to match. Not sure which fits the job?
        <a href="<?= $base_url ?>blog/choosing-the-right-material">Read our material comparison guide</a>.
      </p>
    </div>
  </section>

  <!-- SERVICES PREVIEW -->
  <section class="section-pad" style="background:var(--card);border-block:1px solid var(--border);">
    <div class="container">
      <div class="section-head" data-reveal>
        <div>
          <span class="section-label">Our Services</span>
          <h2>How We Support Your Project</h2>
        </div>
        <a href="<?= $base_url ?>services" class="btn-text">
          All services
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="4" y1="12" x2="19" y2="12" />
            <polyline points="13 6 19 12 13 18" />
          </svg>
        </a>
      </div>

      <div class="mini-grid">
        <div class="mini-card" data-reveal>
          <h3>Wholesale Slab Supply</h3>
          <p>Deep stock of core materials so your project never waits on a container.</p>
        </div>
        <div class="mini-card" data-reveal>
          <h3>Wholesale Pricing</h3>
          <p>Tiered volume pricing that rewards repeat buyers, with no minimum order.</p>
        </div>
        <div class="mini-card" data-reveal>
          <h3>Custom Quarry Sourcing</h3>
          <p>Specific exotics or large uniform quantities sourced direct to your spec.</p>
        </div>
        <div class="mini-card" data-reveal>
          <h3>Dedicated Point of Contact</h3>
          <p>One person who knows your project, your timeline and the material you are after.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- RECENT WORK -->
  <section class="section-pad">
    <div class="container">
      <div class="section-head" data-reveal>
        <div>
          <span class="section-label">Finished Work</span>
          <h2>Our Material, Their Craft</h2>
        </div>
        <a href="<?= $base_url ?>gallery" class="btn-text">
          View project gallery
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="4" y1="12" x2="19" y2="12" />
            <polyline points="13 6 19 12 13 18" />
          </svg>
        </a>
      </div>

      <div class="work-grid">
        <a href="<?= $base_url ?>gallery" class="work-card" data-reveal>
          <img src="<?= e(asset('assets/images/gallery-kitchen.jpg')) ?>"
            alt="Kitchen island fabricated from Taj Mahal quartzite" loading="lazy" width="600" height="450" />
          <div class="work-info">
            <h3>Monolithic Kitchen Island</h3>
            <span>Taj Mahal Quartzite</span>
          </div>
        </a>
        <a href="<?= $base_url ?>gallery" class="work-card" data-reveal>
          <img src="<?= e(asset('assets/images/gallery-bathroom.jpg')) ?>"
            alt="Floating master vanity fabricated from Nero Marquina marble" loading="lazy" width="600" height="450" />
          <div class="work-info">
            <h3>Floating Master Vanity</h3>
            <span>Nero Marquina Marble</span>
          </div>
        </a>
        <a href="<?= $base_url ?>gallery" class="work-card" data-reveal>
          <img src="<?= e(asset('assets/images/gallery-outdoor.jpg')) ?>"
            alt="Outdoor kitchen built with leathered Absolute Black granite" loading="lazy" width="600" height="450" />
          <div class="work-info">
            <h3>Outdoor Living Feature</h3>
            <span>Absolute Black Granite</span>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- SERVICE AREA -->
  <section class="section-pad" style="background:var(--card);border-block:1px solid var(--border);">
    <div class="container">
      <div class="areas-layout">
        <div data-reveal>
          <span class="section-label">Coverage</span>
          <h2>Supplying Fabricators Across Texas</h2>
          <p class="lead">Our gallery sits in the <?= e(site_city()) ?> Design District, minutes from I-35 — an easy
            A-frame run for shops across DFW and a straightforward haul from Houston, Austin and San Antonio. We also
            arrange delivery and dedicated container logistics for partners further out.</p>
          <p style="margin-top:24px;">
            <a href="<?= e(site_directions_url()) ?>" target="_blank" rel="noopener noreferrer" class="btn-text">
              Get directions to the showroom
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <line x1="4" y1="12" x2="19" y2="12" />
                <polyline points="13 6 19 12 13 18" />
              </svg>
            </a>
          </p>
        </div>
        <ul class="area-list" data-reveal>
          <?php foreach (SITE_AREAS as $city): ?>
            <li><?= e($city) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section-pad">
    <div class="container">
      <div class="faq-layout">
        <div data-reveal>
          <span class="section-label">Common Questions</span>
          <h2>Before You Open an Account</h2>
          <p class="lead">The questions we get asked most by new customers. Anything
            we haven't covered, <a href="<?= $base_url ?>contact" style="color:var(--accent);">just ask</a>.</p>
        </div>

        <div class="faq-list" data-reveal>
          <?php foreach ($homeFaqs as $i => $faq): ?>
            <details class="faq-item" <?= $i === 0 ? ' open' : '' ?>>
              <summary><?= e($faq['question']) ?></summary>
              <p><?= e($faq['answer']) ?></p>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- LATEST FROM THE BLOG -->
  <section class="section-pad" style="background:var(--card);border-block:1px solid var(--border);">
    <div class="container">
      <div class="section-head" data-reveal>
        <div>
          <span class="section-label">Insights</span>
          <h2>From the Floor</h2>
        </div>
        <a href="<?= $base_url ?>blog" class="btn-text">
          Read the blog
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="4" y1="12" x2="19" y2="12" />
            <polyline points="13 6 19 12 13 18" />
          </svg>
        </a>
      </div>

      <div class="products-grid">
        <?php foreach (array_slice(blog_posts(), 0, 3, true) as $slug => $post): ?>
          <article class="product-card" data-reveal>
            <a href="<?= $base_url ?>blog/<?= e($slug) ?>" class="product-card-img" aria-hidden="true" tabindex="-1">
              <img src="<?= e(slab_image($post['image'])) ?>" alt="" loading="lazy" width="600" height="450" />
              <span class="product-badge"><?= $post['read'] ?> min read</span>
            </a>
            <div class="product-card-body">
              <h3><a href="<?= $base_url ?>blog/<?= e($slug) ?>"><?= e($post['title']) ?></a></h3>
              <p class="post-excerpt"><?= e($post['excerpt']) ?></p>
              <div class="post-meta">
                <span class="post-date"><?= date('M j, Y', strtotime($post['published'])) ?></span>
                <a href="<?= $base_url ?>blog/<?= e($slug) ?>" class="btn-text">
                  Read
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <line x1="4" y1="12" x2="19" y2="12" />
                    <polyline points="13 6 19 12 13 18" />
                  </svg>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</main>

<!-- CTA BANNER -->
<section class="cta-banner">
  <div class="container">
    <h2>Ready to Source?</h2>
    <p>Get in touch today for live inventory, lot-matched bundles and wholesale pricing on any quantity.</p>
    <a href="<?= $base_url ?>contact" class="btn-dark">Request a Quote</a>
  </div>
</section>