<main id="main">
  <div class="container">
    <?php
    $hero = [
        'crumbs'  => ['Trade' => 'trade/login', 'Apply'],
        'eyebrow' => 'Trade Accounts',
        'title'   => 'Apply for an Account',
        'lead'    => 'Trade accounts let you browse inventory and send reservation requests straight from the site. '
                   . 'We verify new applications within one business day.',
    ];
    include __DIR__ . '/../../parts/page-header.php';
    ?>

    <div class="trade-form-wrap pb-section">
      <?php foreach ($errors as $err): ?>
        <div class="form-status is-error"><?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="post" class="contact-form-wrap" data-nonce="<?= e($_SESSION['form_nonce'] ?? '') ?>">
        <input type="hidden" name="action" value="register" />
        <?php include __DIR__ . '/../../parts/bot-trap.php'; ?>
        <h2 class="contact-form-title">Your business</h2>
        <p class="contact-form-note">No payment details are collected — accounts are for requesting material only.</p>

        <div class="form-row">
          <div class="form-group">
            <label for="company">Company Name *</label>
            <input type="text" id="company" name="company" required value="<?= e($_POST['company'] ?? '') ?>" />
          </div>
          <div class="form-group">
            <label for="contact_name">Contact Name *</label>
            <input type="text" id="contact_name" name="contact_name" required value="<?= e($_POST['contact_name'] ?? '') ?>" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>" />
          </div>
          <div class="form-group">
            <label for="phone">Phone *</label>
            <input type="tel" id="phone" name="phone" required autocomplete="tel" value="<?= e($_POST['phone'] ?? '') ?>" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city" placeholder="Dallas, TX" value="<?= e($_POST['city'] ?? '') ?>" />
          </div>
          <div class="form-group">
            <label for="tax_id">Resale / Tax ID</label>
            <input type="text" id="tax_id" name="tax_id" value="<?= e($_POST['tax_id'] ?? '') ?>" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="password">Password * <span style="text-transform:none;letter-spacing:0;">min 8 characters</span></label>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" />
          </div>
          <div class="form-group">
            <label for="confirm">Confirm Password *</label>
            <input type="password" id="confirm" name="confirm" required minlength="8" autocomplete="new-password" />
          </div>
        </div>

        <button type="submit" class="btn-submit">Submit Application</button>
        <p class="form-privacy">Already have an account? <a href="<?= $base_url ?>trade/login">Sign in</a></p>
      </form>
    </div>
  </div>
</main>
