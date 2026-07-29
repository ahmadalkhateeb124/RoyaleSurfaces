<?php
// A "Request Quote" link from the Slabs page arrives as ?inquiry=Taj+Mahal —
// prefill the message so the customer doesn't retype it.
$inquiry = trim((string) ($_GET['inquiry'] ?? ''));
$prefill = $inquiry !== ''
    ? "I'd like a quote on " . $inquiry . ". Please send availability and pricing."
    : '';

// Bot filter: JavaScript copies this into a hidden field before submitting, so a
// scripted POST that never runs JS fails the check. It is noise reduction, not
// a security control — validation and escaping do the real work.
if (empty($_SESSION['form_nonce'])) {
    $_SESSION['form_nonce'] = bin2hex(random_bytes(16));
}
?>

<main id="main" class="pb-section">
  <div class="container">

    <?php
    $hero = [
        'crumbs'  => ['Contact'],
        'eyebrow' => 'Get in Touch',
        'title'   => e(site_city()) . ' Showroom',
        'lead'    => 'Our 26,900 sq ft slab gallery is open to fabricators, builders, designers and homeowners alike.
            Schedule a visit to view current inventory in person, or send us your material list and we will come back
            with availability.',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <div class="contact-grid">

      <!-- LEFT: Info + Map -->
      <div>
        <div class="contact-info-list">
          <div class="contact-info-item">
            <div class="contact-info-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
            </div>
            <div>
              <div class="contact-info-label">Location</div>
              <div class="contact-info-value">
                <a href="<?= e(site_directions_url()) ?>" target="_blank" rel="noopener noreferrer">
                  <?= e(site_street()) ?><br /><?= e(site_city()) ?>, <?= e(site_state()) ?> <?= e(site_zip()) ?>
                </a>
              </div>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-info-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                  d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.37 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.15a16 16 0 0 0 6.09 6.09l.87-.87a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
              </svg>
            </div>
            <div>
              <div class="contact-info-label">Direct Line</div>
              <div class="contact-info-value">
                <a href="<?= e(tel_link(site_phone())) ?>"><?= e(site_phone()) ?></a>
              </div>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-info-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
              </svg>
            </div>
            <div>
              <div class="contact-info-label">Email</div>
              <div class="contact-info-value">
                <a href="mailto:<?= e(site_email()) ?>"><?= e(site_email()) ?></a>
              </div>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-info-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
            </div>
            <div>
              <div class="contact-info-label">Opening Hours</div>
              <div class="contact-info-value">
                <?php foreach (site_hours() as $i => $h): ?>
                  <?= $i ? '<br />' : '' ?><?= e($h['days']) ?>: <?= e($h['time']) ?>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="map-embed">
          <iframe src="<?= e(site_map_embed()) ?>" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade" allowfullscreen
            title="<?= e(site_name()) ?> showroom location map"></iframe>
        </div>

        <a href="<?= e(site_directions_url()) ?>" target="_blank" rel="noopener noreferrer" class="map-link">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
            <circle cx="12" cy="10" r="3" />
          </svg>
          <span>
            <strong>Open in Google Maps</strong>
            <small><?= e(site_address()) ?></small>
          </span>
          <svg class="map-link-go" viewBox="0 0 24 24" aria-hidden="true">
            <line x1="4" y1="12" x2="19" y2="12" />
            <polyline points="13 6 19 12 13 18" />
          </svg>
        </a>
      </div>

      <!-- RIGHT: Form -->
      <div>
        <div class="contact-form-wrap">
          <h2 class="contact-form-title">Schedule a Showroom Visit</h2>
          <p class="contact-form-note">Tell us about your project — we reply within one business day.</p>

          <div id="formSuccess" class="form-success" tabindex="-1" hidden>
            <h3>Inquiry Submitted</h3>
            <p>Thank you. One of our team will contact you within one business day. For anything urgent, call
              <a href="<?= e(tel_link(site_phone())) ?>"><?= e(site_phone()) ?></a>.</p>
          </div>

          <p id="formStatus" class="form-status" role="alert" hidden></p>

          <form id="contactForm" action="<?= $base_url ?>inc/send_inquiry.php" method="post" novalidate
            data-nonce="<?= e($_SESSION['form_nonce']) ?>">

            <!-- Bot traps. Two decoy fields with plausible names, hidden from
                 humans by CSS and skipped by tab order; a real visitor can never
                 fill them, an automated poster fills everything it finds. -->
            <div class="form-hp" aria-hidden="true">
              <label for="website">Website</label>
              <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" />
              <label for="company_url">Company URL</label>
              <input type="text" id="company_url" name="company_url" tabindex="-1" autocomplete="off" />
            </div>

            <input type="hidden" name="started_at" value="<?= time() ?>" />
            <input type="hidden" name="js_token" value="" />
            <input type="hidden" name="inquiry" value="<?= e($inquiry) ?>" />

            <div class="form-row">
              <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" placeholder="John Smith" autocomplete="name" required />
                <span class="form-error" id="nameError" hidden></span>
              </div>
              <div class="form-group">
                <label for="company">Company Name</label>
                <input type="text" id="company" name="company" placeholder="Apex Stone Co. (optional)"
                  autocomplete="organization" />
                <span class="form-error" id="companyError" hidden></span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" placeholder="(214) 555-0100" autocomplete="tel" required />
                <span class="form-error" id="phoneError" hidden></span>
              </div>
              <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" placeholder="john@apexstone.com" autocomplete="email"
                  required />
                <span class="form-error" id="emailError" hidden></span>
              </div>
            </div>

            <div class="form-group">
              <label for="message">Project Details / Inquiry *</label>
              <textarea id="message" name="message"
                placeholder="Materials needed, quantities, timeline, or when you'd like to visit…"
                required><?= e($prefill) ?></textarea>
              <span class="form-error" id="messageError" hidden></span>
            </div>

            <button type="submit" class="btn-submit">Submit Inquiry</button>

            <p class="form-privacy">By submitting you agree to our
              <a href="<?= $base_url ?>privacy-policy">Privacy Policy</a>. We never share your details.</p>
          </form>
        </div>
      </div>

    </div>
  </div>
</main>
