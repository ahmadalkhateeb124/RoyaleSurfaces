<?php
// Validated here purely to decide which state to render — the POST handler in
// pages/trade.php re-validates independently before it ever touches the
// password, so a forged/expired token can't slip through even if this check
// were somehow bypassed.
$resetToken   = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$resetAccount = trade_password_reset_lookup($pdo, $resetToken);
?>
<main id="main">
  <div class="container">
    <?php
    $hero = [
        'crumbs'  => ['Trade' => 'trade/login', 'Reset Password'],
        'eyebrow' => 'Trade Accounts',
        'title'   => 'Choose a New Password',
        'lead'    => $resetAccount
            ? 'Setting a new password for the ' . e($resetAccount['company']) . ' account.'
            : 'This link needs to be valid to continue.',
    ];
    include __DIR__ . '/../../parts/page-header.php';
    ?>

    <div class="trade-form-wrap trade-form-narrow pb-section">
      <?php foreach ($errors as $err): ?>
        <div class="form-status is-error"><?= e($err) ?></div>
      <?php endforeach; ?>

      <?php if (!$resetAccount): ?>
        <div class="contact-form-wrap">
          <h2 class="contact-form-title">Link expired</h2>
          <p class="contact-form-note">
            That reset link is invalid or more than <?= TRADE_RESET_TTL_MINUTES ?> minutes old — links only work
            once. Request a fresh one and it'll be waiting in your inbox within a minute.
          </p>
          <a href="<?= $base_url ?>trade/forgot" class="btn-submit" style="display:block;text-align:center;">
            Request a New Link
          </a>
        </div>
      <?php else: ?>
        <form method="post" class="contact-form-wrap">
          <input type="hidden" name="action" value="reset_password" />
          <input type="hidden" name="token" value="<?= e($resetToken) ?>" />
          <h2 class="contact-form-title">New password</h2>

          <div class="form-group">
            <label for="password">New Password <span style="text-transform:none;letter-spacing:0;">min 8 characters</span></label>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" />
          </div>

          <div class="form-group">
            <label for="confirm">Confirm Password</label>
            <input type="password" id="confirm" name="confirm" required minlength="8" autocomplete="new-password" />
          </div>

          <button type="submit" class="btn-submit">Update Password</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</main>
