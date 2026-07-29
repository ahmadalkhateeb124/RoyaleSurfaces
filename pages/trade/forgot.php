<main id="main">
  <div class="container">
    <?php
    $hero = [
        'crumbs'  => ['Trade' => 'trade/login', 'Reset Password'],
        'eyebrow' => 'Trade Accounts',
        'title'   => 'Reset Your Password',
        'lead'    => 'Tell us the email on your account and we\'ll send a link to choose a new password.',
    ];
    include __DIR__ . '/../../parts/page-header.php';
    ?>

    <div class="trade-form-wrap trade-form-narrow pb-section">
      <?php foreach ($errors as $err): ?>
        <div class="form-status is-error"><?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="post" class="contact-form-wrap" data-nonce="<?= e($_SESSION['form_nonce'] ?? '') ?>">
        <input type="hidden" name="action" value="forgot_password" />
        <?php include __DIR__ . '/../../parts/bot-trap.php'; ?>
        <h2 class="contact-form-title">Forgot password</h2>
        <p class="contact-form-note">We'll email a link that works once and expires in an hour.</p>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required autocomplete="email"
            value="<?= e($_POST['email'] ?? '') ?>" />
        </div>

        <button type="submit" class="btn-submit">Send Reset Link</button>
        <p class="form-privacy">
          <a href="<?= $base_url ?>trade/login">Back to sign in</a>
        </p>
      </form>
    </div>
  </div>
</main>
