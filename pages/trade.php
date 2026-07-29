<?php
/**
 * Trade account area. `$tradePage` comes from index.php:
 *   register · login · logout · (dashboard) · list · orders
 *
 * No payment anywhere — a trader builds a request list and submits it. Pricing
 * and availability are confirmed by a person afterwards.
 */
require_once __DIR__ . '/../inc/trade.php';

$tradePage = $tradePage ?? '';
$ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
$errors = [];
$notice = $_SESSION['trade_notice'] ?? null;
unset($_SESSION['trade_notice']);

// Bot trap for login/register/forgot — see trade_looks_like_bot(). One nonce
// covers all three forms; each renders it via data-nonce on its <form>.
if (empty($_SESSION['form_nonce'])) {
    $_SESSION['form_nonce'] = bin2hex(random_bytes(16));
}

// ── Actions ──────────────────────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    // ── Register ────────────────────────────────────────────────────────────
    if ($action === 'register') {
        // Same trick the contact form uses: a trapped bot is told it worked,
        // so it has no signal to tell it which field gave it away. A real
        // applicant never sees this branch.
        if (trade_looks_like_bot($_POST) || trade_lockout($pdo, $ip) > 0) {
            trade_login_fail($pdo, $ip);
            $_SESSION['trade_notice'] = 'Thank you. Your application is with our team — we verify new accounts '
                . 'within one business day and will email you as soon as it is approved.';
            header('Location: ' . $base_url . 'trade/login');
            exit;
        }

        $company = trim((string) ($_POST['company'] ?? ''));
        $name    = trim((string) ($_POST['contact_name'] ?? ''));
        $email   = trim((string) ($_POST['email'] ?? ''));
        $phone   = trim((string) ($_POST['phone'] ?? ''));
        $city    = trim((string) ($_POST['city'] ?? ''));
        $taxId   = trim((string) ($_POST['tax_id'] ?? ''));
        $pass    = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');

        if (mb_strlen($company) < 2)                       $errors[] = 'Company name is required.';
        if (mb_strlen($name) < 2)                          $errors[] = 'Contact name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = 'Enter a valid email address.';
        if (strlen(preg_replace('/\D/', '', $phone)) < 10) $errors[] = 'Enter a valid phone number.';
        if (strlen($pass) < 8)                             $errors[] = 'Password must be at least 8 characters.';
        if ($pass !== $confirm)                            $errors[] = 'Passwords do not match.';

        if (!$errors) {
            $dupe = $pdo->prepare('SELECT COUNT(*) FROM trade_accounts WHERE email = ?');
            $dupe->execute([$email]);
            if ((int) $dupe->fetchColumn() > 0) {
                $errors[] = 'An account already exists for that email. Try signing in instead.';
            }
        }

        if (!$errors) {
            $pdo->prepare(
                'INSERT INTO trade_accounts (company, contact_name, email, phone, city, tax_id, password_hash)
                 VALUES (?,?,?,?,?,?,?)'
            )->execute([$company, $name, $email, $phone, $city, $taxId, password_hash($pass, PASSWORD_DEFAULT)]);

            $_SESSION['trade_notice'] = 'Thank you. Your application is with our team — we verify new accounts '
                . 'within one business day and will email you as soon as it is approved.';
            header('Location: ' . $base_url . 'trade/login');
            exit;
        }
    }

    // ── Sign in ─────────────────────────────────────────────────────────────
    if ($action === 'login') {
        $email = trim((string) ($_POST['email'] ?? ''));
        $pass  = (string) ($_POST['password'] ?? '');
        $locked = trade_lockout($pdo, $ip);

        if ($locked > 0) {
            $errors[] = 'Too many attempts. Try again in ' . ceil($locked / 60) . ' minute(s).';
        } elseif (trade_looks_like_bot($_POST)) {
            trade_login_fail($pdo, $ip);
            // Same message a genuinely wrong password gets — nothing here
            // tells a script which check actually caught it.
            $errors[] = 'Those details did not match. Please try again.';
        } else {
            $st = $pdo->prepare('SELECT * FROM trade_accounts WHERE email = ? LIMIT 1');
            $st->execute([$email]);
            $acc = $st->fetch();

            if ($acc && password_verify($pass, $acc['password_hash'])) {
                if ($acc['status'] === 'pending') {
                    $errors[] = 'Your account is still awaiting approval. We will email you once it is active.';
                } elseif ($acc['status'] === 'suspended') {
                    $errors[] = 'This account is on hold. Please call us on ' . site_phone() . '.';
                } elseif ($acc['status'] === 'rejected') {
                    $errors[] = 'We were not able to approve this account. Please contact us.';
                } else {
                    $pdo->prepare('UPDATE trade_accounts SET last_login_at = NOW() WHERE id = ?')
                        ->execute([$acc['id']]);
                    trade_login_reset($pdo, $ip);
                    trade_login($acc);

                    $to = $_SESSION['trade_return'] ?? ($base_url . 'trade');
                    unset($_SESSION['trade_return']);
                    header('Location: ' . $to);
                    exit;
                }
            } else {
                trade_login_fail($pdo, $ip);
                // Vague on purpose — do not confirm whether the email exists.
                $errors[] = 'Those details did not match. Please try again.';
            }
        }
    }

    // ── Forgot password ──────────────────────────────────────────────────────
    if ($action === 'forgot_password') {
        $email = trim((string) ($_POST['email'] ?? ''));
        $isBot = trade_looks_like_bot($_POST) || trade_lockout($pdo, $ip) > 0;

        if (!$isBot && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        } else {
            if ($isBot) {
                trade_login_fail($pdo, $ip);
            } else {
                $issued = trade_password_reset_request($pdo, $email);
                if ($issued) {
                    require_once __DIR__ . '/../inc/trade-mail.php';
                    trade_reset_notify($issued['account'], $issued['token']);
                }
            }
            // Same message every time — whether the address matched an
            // account, and whether the request was even genuine. Any of
            // those three outcomes being distinguishable is a free probe.
            $_SESSION['trade_notice'] = 'If that email has an account with us, a reset link is on its way. '
                . 'It works for ' . TRADE_RESET_TTL_MINUTES . ' minutes.';
            header('Location: ' . $base_url . 'trade/login');
            exit;
        }
    }

    // ── Reset password ───────────────────────────────────────────────────────
    if ($action === 'reset_password') {
        $token   = (string) ($_POST['token'] ?? '');
        $pass    = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');

        $account = trade_password_reset_lookup($pdo, $token);

        if (!$account) {
            $errors[] = 'That reset link is invalid or has expired. Request a new one below.';
        } elseif (strlen($pass) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif ($pass !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            trade_password_reset_complete($pdo, (int) $account['id'], $pass);
            $_SESSION['trade_notice'] = 'Password updated. Sign in with your new password.';
            header('Location: ' . $base_url . 'trade/login');
            exit;
        }
    }

    // ── Request list ────────────────────────────────────────────────────────
    if ($action === 'cart_add') {
        $slug   = (string) ($_POST['slug'] ?? '');
        $result = cart_add(
            $slug,
            (int) ($_POST['quantity'] ?? 1),
            trim((string) ($_POST['size'] ?? '')),
            trim((string) ($_POST['notes'] ?? ''))
        );

        // Say plainly when the number was trimmed — silently changing what
        // someone asked for is worse than telling them the slab is short.
        if ($result === false) {
            $_SESSION['trade_notice'] = 'That slab is sold out. Ask us to source it and we will call you '
                . 'when the next container lands.';
        } elseif ($result === 'capped') {
            $left = slab_stock_for($slug);
            $_SESSION['trade_notice'] = 'Only ' . $left . ' slab' . ($left === 1 ? '' : 's')
                . ' left — your list was set to that.';
        } else {
            $_SESSION['trade_notice'] = 'Added to your request list.';
        }

        header('Location: ' . ($_POST['back'] ?? ($base_url . 'trade/list')));
        exit;
    }

    if ($action === 'cart_update') {
        $slug   = (string) ($_POST['slug'] ?? '');
        $result = cart_update(
            $slug,
            (int) ($_POST['quantity'] ?? 1),
            trim((string) ($_POST['size'] ?? '')),
            trim((string) ($_POST['notes'] ?? ''))
        );

        if ($result === 'capped') {
            $left = slab_stock_for($slug);
            $_SESSION['trade_notice'] = 'We only have ' . $left . ' of that one — your quantity was reduced.';
        }

        header('Location: ' . $base_url . 'trade/list');
        exit;
    }

    if ($action === 'cart_remove') {
        cart_remove((string) ($_POST['slug'] ?? ''));
        header('Location: ' . $base_url . 'trade/list');
        exit;
    }

    // ── Submit ──────────────────────────────────────────────────────────────
    if ($action === 'submit_order') {
        trade_require_login($base_url . 'trade/list');
        $me = trade_user();

        $order = order_create(
            $pdo,
            $me['id'],
            trim((string) ($_POST['notes'] ?? '')),
            (string) ($_POST['needed_by'] ?? '')
        );

        if (!$order) {
            $errors[] = 'Your request list is empty.';
        } else {
            require_once __DIR__ . '/../inc/order-mail.php';
            order_notify($pdo, $order['id']);

            $_SESSION['trade_notice'] = 'Request ' . $order['reference'] . ' received. '
                . 'We will confirm availability and pricing within one business day.';
            header('Location: ' . $base_url . 'trade/orders');
            exit;
        }
    }
}

if ($tradePage === 'logout') {
    trade_logout();
    $_SESSION['trade_notice'] = 'You have been signed out.';
    header('Location: ' . $base_url . 'trade/login');
    exit;
}

// /trade with no account is a public landing page — a trader has to be able to
// find out the programme exists before being asked to sign in.
$showLanding = $tradePage === '' && !trade_check();

// A reset link arrives by email — it has to work whether or not the visitor
// happens to have an old session open, and signing in is not the point of it.
$publicPages = ['register', 'login', 'forgot', 'reset'];

// Everything else past this point needs an account.
if (!$showLanding && !in_array($tradePage, $publicPages, true)) {
    trade_require_login($base_url . 'trade/' . $tradePage);
}

// Already signed in? Skip straight past the sign-in/apply forms — but not the
// reset flow, which someone might still want after opening an emailed link.
if (in_array($tradePage, ['register', 'login'], true) && trade_check()) {
    header('Location: ' . $base_url . 'trade');
    exit;
}

$me = trade_user();

$view = $showLanding
    ? 'landing'
    : (in_array($tradePage, [...$publicPages, 'list', 'orders'], true) ? $tradePage : 'dashboard');

include __DIR__ . '/trade/' . $view . '.php';
