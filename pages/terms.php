<main id="main">
  <div class="container">

        <?php
    $hero = [
        'crumbs' => ['Terms of Service'],
        'title'  => 'Terms of Service',
        'lead'   => 'Last updated ' . date('F Y') . '.',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <div class="prose">
      <h2>Acceptance</h2>
      <p>By using <?= e(SITE_DOMAIN) ?> you agree to these terms. If you do not agree, please do not use the site.</p>

      <h2>Who we sell to</h2>
      <p><?= e(site_name()) ?> supplies material to fabricators, contractors, builders, design firms and private clients
        alike. Businesses may apply for a trading account with volume pricing tiers and payment terms; those
        applications are subject to verification and we may decline one at our discretion. No account is required to
        buy from us.</p>

      <h2>Inventory and availability</h2>
      <p>Slabs shown on this website represent a selection of our stock at the time of publication. Natural stone is a
        finite, non-reproducible material: inventory turns over continuously and a specific slab, lot or bundle may sell
        before you contact us. Nothing on this site constitutes an offer to sell or a guarantee of availability.</p>

      <h2>Colour, veining and photography</h2>
      <p>Natural stone varies from block to block and slab to slab. Photographs on this site, printed samples and
        on-screen colour are indicative only and will not match a specific slab exactly. Screen calibration further
        affects appearance. We strongly recommend viewing and selecting your actual slabs in person at our
        <?= e(site_city()) ?> gallery before fabrication.</p>

      <h2>Quotes, pricing and reservations</h2>
      <p>Prices quoted to account holders are confidential. Quotes are valid for the period stated on the quote
        and are subject to material remaining available. Lot reservations are held for the period agreed in writing at
        the time of reservation. Prices exclude applicable taxes, fabrication and delivery unless stated otherwise.</p>

      <h2>Inspection at pickup</h2>
      <p>Material must be inspected at the time of loading. Once slabs leave our facility, claims for visible defects,
        cracks or chips cannot be accepted. Loading assistance is provided as a courtesy; the buyer is responsible for
        the suitability, condition and safe securing of their transport and A-frames.</p>

      <h2>Fabrication</h2>
      <p>We supply slabs; we do not fabricate or install. Any advice we offer on material suitability is given in good
        faith and does not replace the fabricator's own professional judgement. The fabricator is responsible for
        template accuracy, cutting, seam placement, structural support and final installation.</p>

      <h2>Website content</h2>
      <p>All text, images, logos and layout on this site are the property of <?= e(site_name()) ?> and may not be
        reproduced without written permission. We take care to keep content accurate but do not warrant that it is
        complete or error-free, and we may change it at any time.</p>

      <h2>Limitation of liability</h2>
      <p>To the fullest extent permitted by law, <?= e(site_name()) ?> is not liable for indirect, incidental or
        consequential damages arising from use of this website or from the supply of material, including lost profits or
        project delays. Our total liability in relation to any material supplied will not exceed the amount paid for
        that material.</p>

      <h2>Governing law</h2>
      <p>These terms are governed by the laws of the State of Texas, and any dispute will be subject to the exclusive
        jurisdiction of the courts of <?= e(site_city()) ?> County, Texas.</p>

      <h2>Contact</h2>
      <p>Questions about these terms: <a href="mailto:<?= e(site_email()) ?>"><?= e(site_email()) ?></a> or
        <a href="<?= e(tel_link(site_phone())) ?>"><?= e(site_phone()) ?></a>.</p>
    </div>

  </div>
</main>
