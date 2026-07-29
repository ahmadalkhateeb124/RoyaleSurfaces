<main id="main">
  <div class="container">

        <?php
    $hero = [
        'crumbs' => ['Privacy Policy'],
        'title'  => 'Privacy Policy',
        'lead'   => 'Last updated ' . date('F Y') . '.',
    ];
    include __DIR__ . '/../parts/page-header.php';
    ?>

    <div class="prose">
      <h2>Who we are</h2>
      <p><?= e(site_name()) ?> is a wholesale natural stone supplier located at <?= e(site_address()) ?>. This policy
        explains what information we collect through <?= e(SITE_DOMAIN) ?> and how we use it.</p>

      <h2>Information we collect</h2>
      <p>We only collect information you choose to give us. When you submit the inquiry form we receive:</p>
      <ul>
        <li>Your name and company name</li>
        <li>Your phone number and email address</li>
        <li>The message and any material details you include</li>
      </ul>
      <p>Our web server also records standard technical data such as IP address, browser type and pages visited. This is
        used for security and to understand how the site is used.</p>

      <h2>How we use it</h2>
      <p>We use your information solely to respond to your inquiry, prepare quotes, set up an account and
        communicate about orders. We do not sell, rent or trade your information to third parties, and we do not send
        marketing email unless you ask us to.</p>

      <h2>Who we share it with</h2>
      <p>Your details are shared only with service providers that make this site work — our web host and our email
        provider — and only to the extent required to deliver your message to us. We may also disclose information where
        required by law.</p>

      <h2>Cookies</h2>
      <p>This site uses a single session cookie, which keeps the inquiry form working correctly and prevents automated
        spam submissions. It contains no personal information and expires when you close your browser. We do not run
        third-party advertising or tracking cookies.</p>
      <p>Note that the embedded Google Map on our contact page is served by Google and is subject to Google's own
        privacy policy.</p>

      <h2>How long we keep it</h2>
      <p>Inquiry emails are retained in our business records for as long as needed to serve your account and to meet
        legal and accounting obligations.</p>

      <h2>Your choices</h2>
      <p>You can ask us to provide a copy of, correct or delete the information we hold about you. Email
        <a href="mailto:<?= e(site_email()) ?>"><?= e(site_email()) ?></a> or call <?= e(site_phone()) ?> and we will respond
        promptly.</p>

      <h2>Security</h2>
      <p>This site is served over HTTPS and form submissions are transmitted encrypted. No method of transmission is
        completely secure, so please do not send sensitive financial information through the website form.</p>

      <h2>Children</h2>
      <p>This is a business-to-business website and is not directed to anyone under 18. We do not knowingly collect
        information from children.</p>

      <h2>Changes</h2>
      <p>We may update this policy from time to time. The revision date at the top of this page always reflects the
        current version.</p>

      <h2>Contact</h2>
      <p><?= e(site_name()) ?><br />
        <?= e(site_street()) ?><br />
        <?= e(site_city()) ?>, <?= e(site_state()) ?> <?= e(site_zip()) ?><br />
        <a href="mailto:<?= e(site_email()) ?>"><?= e(site_email()) ?></a> ·
        <a href="<?= e(tel_link(site_phone())) ?>"><?= e(site_phone()) ?></a>
      </p>
    </div>

  </div>
</main>
