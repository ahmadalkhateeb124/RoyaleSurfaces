<main id="main">
  <div class="container">
    <?php
    $hero = [
        'crumbs'  => ['Trade Accounts'],
        'eyebrow' => 'For the Trade',
        'title'   => 'Open a Trade Account',
        'lead'    => 'Fabricators, builders and design firms can request material straight from the inventory pages — '
                   . 'no phone tag, no re-typing slab names into an email. Approval takes about a business day.',
        'aside'   => '<a href="' . $base_url . 'trade/register" class="btn-primary">Apply Now</a>',
    ];
    include __DIR__ . '/../../parts/page-header.php';
    ?>

    <?php if ($notice): ?><div class="form-status" style="margin-bottom:24px;"><?= e($notice) ?></div><?php endif; ?>

    <section class="section-pad" style="padding-top:0;">
      <div class="mini-grid">
        <div class="mini-card" data-reveal>
          <h3>Build a Request List</h3>
          <p>Add materials as you browse, set quantities and size notes, then send the lot in one go.</p>
        </div>
        <div class="mini-card" data-reveal>
          <h3>Track Every Request</h3>
          <p>See what you sent and where it stands — quoted, confirmed, ready for collection.</p>
        </div>
        <div class="mini-card" data-reveal>
          <h3>Nothing Is Charged</h3>
          <p>A request is not an order. We confirm availability and pricing before anything is committed.</p>
        </div>
        <div class="mini-card" data-reveal>
          <h3>Lot Matching Noted</h3>
          <p>Flag bookmatching or matched lots per line so the requirement reaches us with the request.</p>
        </div>
      </div>
    </section>

    <section class="section-pad" style="padding-top:0;">
      <div class="about-2col" style="margin-bottom:0;">
        <div class="about-body" data-reveal>
          <span class="section-label">How It Works</span>
          <h2>Four Steps, One Business Day.</h2>
          <ul class="check-list">
            <li><strong>Apply</strong> — company details, contact and a resale certificate if you have one</li>
            <li><strong>We verify</strong> — usually the same day, always within one business day</li>
            <li><strong>Browse and add</strong> — every slab page gains a quantity box once you are signed in</li>
            <li><strong>Send</strong> — we come back with availability and pricing, then hold the lots you confirm</li>
          </ul>
          <p style="margin-top:22px;">Not a business? You do not need an account — just
            <a href="<?= $base_url ?>contact" style="color:var(--accent);">send us a message</a> or walk into the
            gallery. Accounts exist to save repeat buyers time, not to gate the inventory.</p>

          <div class="hero-ctas" style="margin-top:28px;">
            <a href="<?= $base_url ?>trade/register" class="btn-primary">Apply for an Account</a>
            <a href="<?= $base_url ?>trade/login" class="btn-outline">Sign In</a>
          </div>
        </div>
        <img src="<?= e(asset('assets/images/slabtr.jpeg')) ?>"
          alt="Slab bundles racked by lot number at the Royale Surfaces Dallas warehouse" loading="lazy"
          width="800" height="1000" />
      </div>
    </section>

    <div class="pb-section"></div>
  </div>
</main>

<section class="cta-banner">
  <div class="container">
    <h2>Already Buying From Us?</h2>
    <p>Apply with the same company details and we will link the account to your existing history.</p>
    <a href="<?= $base_url ?>trade/register" class="btn-dark">Apply for an Account</a>
  </div>
</section>
