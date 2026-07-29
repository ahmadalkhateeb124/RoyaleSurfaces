<main id="main">
  <div class="container">
    <?php
    $hero = [
        'crumbs'  => ['Trade Account'],
        'eyebrow' => 'Trade Accounts',
        'title'   => 'Sign In',
        'lead'    => 'Browse inventory and send reservation requests. Pricing is confirmed by our team on every request.',
    ];
    include __DIR__ . '/../../parts/page-header.php';
    ?>

    <div class="trade-form-wrap trade-form-narrow pb-section">
      <?php if ($notice): ?>
        <div class="form-status"><?= e($notice) ?></div>
      <?php endif; ?>
      <?php foreach ($errors as $err): ?>
        <div class="form-status is-error"><?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="post" class="contact-form-wrap" data-nonce="<?= e($_SESSION['form_nonce'] ?? '') ?>">
        <input type="hidden" name="action" value="login" />
        <?php include __DIR__ . '/../../parts/bot-trap.php'; ?>
        <h2 class="contact-form-title">Account sign in</h2>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>" />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required autocomplete="current-password" />
          <a href="<?= $base_url ?>trade/forgot" class="form-forgot">Forgot your password?</a>
        </div>

        <button type="submit" class="btn-submit">Sign In</button>
        <p class="form-privacy">
          No account yet? <a href="<?= $base_url ?>trade/register">Apply for one</a> — approval takes about a day.
        </p>
      </form>
    </div>
  </div>
</main>
