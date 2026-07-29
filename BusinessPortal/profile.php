<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';

$me = auth_user();
$st = $pdo->prepare('SELECT * FROM admin_users WHERE id = ?');
$st->execute([$me['id']]);
$user = $st->fetch();

if (!$user) {
    auth_logout();
    header('Location: ' . portal_url('login.php'));
    exit;
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $action = (string) ($_POST['action'] ?? '');

    // ── Account details ──────────────────────────────────────────────────────
    if ($action === 'details') {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));

        if (!preg_match('/^[a-zA-Z0-9_.-]{3,60}$/', $username)) {
            $errors[] = 'Username must be 3–60 characters (letters, numbers, dot, dash, underscore).';
        }
        if (mb_strlen($fullName) > 120) {
            $errors[] = 'Display name is too long.';
        }

        if (!$errors && $username !== $user['username']) {
            $dupe = $pdo->prepare('SELECT COUNT(*) FROM admin_users WHERE username = ? AND id <> ?');
            $dupe->execute([$username, $user['id']]);
            if ((int) $dupe->fetchColumn() > 0) {
                $errors[] = 'That username is already taken.';
            }
        }

        if (!$errors) {
            $pdo->prepare('UPDATE admin_users SET full_name = ?, username = ? WHERE id = ?')
                ->execute([$fullName, $username, $user['id']]);

            // Refresh the session copy so the header updates immediately.
            $_SESSION['admin']['username'] = $username;
            $_SESSION['admin']['name'] = $fullName ?: $username;

            flash('ok', 'Profile updated.');
            header('Location: ' . portal_url('profile.php'));
            exit;
        }
    }

    // ── Business details (name, address, phone, email) ───────────────────────
    if ($action === 'business') {
        $fields = [
            'name'   => trim((string) ($_POST['biz_name'] ?? '')),
            'phone'  => trim((string) ($_POST['biz_phone'] ?? '')),
            'email'  => trim((string) ($_POST['biz_email'] ?? '')),
            'street' => trim((string) ($_POST['biz_street'] ?? '')),
            'city'   => trim((string) ($_POST['biz_city'] ?? '')),
            'state'  => trim((string) ($_POST['biz_state'] ?? '')),
            'zip'    => trim((string) ($_POST['biz_zip'] ?? '')),
            'map_url'    => trim((string) ($_POST['biz_map_url'] ?? '')),
            'map_embed'  => trim((string) ($_POST['biz_map_embed'] ?? '')),
            'inquiry_to' => trim((string) ($_POST['biz_inquiry_to'] ?? '')),
        ];

        // These three appear on every page and in the schema.org markup, so a
        // blank one would visibly break the site rather than fail quietly.
        if ($fields['name'] === '') {
            $errors[] = 'Business name cannot be empty.';
        }
        if (strlen(preg_replace('/\D/', '', $fields['phone'])) < 10) {
            $errors[] = 'Enter a full phone number including the area code.';
        }
        if (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'The public email address is not valid.';
        }
        if ($fields['inquiry_to'] !== '' && !filter_var($fields['inquiry_to'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'The address that receives enquiries is not valid.';
        }
        if ($fields['map_url'] !== '' && !filter_var($fields['map_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'The map link must be a full URL starting with https://.';
        }

        // Google hands you a whole <iframe> tag — take the src out of it rather
        // than making someone edit HTML by hand. Storing only the URL also means
        // no third-party markup is ever written into a page.
        if ($fields['map_embed'] !== '') {
            if (preg_match('~src=["\']([^"\']+)["\']~', $fields['map_embed'], $m)) {
                $fields['map_embed'] = $m[1];
            }
            if (!str_starts_with($fields['map_embed'], 'https://www.google.com/maps/embed')) {
                $errors[] = 'That is not a Google Maps embed. In Google Maps choose Share → '
                    . 'Embed a map, then paste the whole code here.';
            }
        }

        if (!$errors) {
            $save = $pdo->prepare(
                'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
            );
            foreach ($fields as $key => $value) {
                $save->execute(['biz_' . $key, $value]);
            }

            flash('ok', 'Business details saved. They now show in the header, footer, contact page, '
                . 'the side contact bar and your Google listing markup.');
            header('Location: ' . portal_url('profile.php'));
            exit;
        }
    }

    // ── Opening hours ────────────────────────────────────────────────────────
    if ($action === 'hours') {
        $save = $pdo->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );

        foreach (SITE_HOURS as $i => $row) {
            $days = trim((string) ($_POST['hours_days_' . $i] ?? ''));
            $time = trim((string) ($_POST['hours_' . $i] ?? ''));
            $save->execute(['biz_hours_days_' . $i, $days]);
            $save->execute(['biz_hours_' . $i, $time]);
        }

        flash('ok', 'Opening hours saved.');
        header('Location: ' . portal_url('profile.php'));
        exit;
    }

    // ── Brand: logo + favicon ────────────────────────────────────────────────
    if ($action === 'brand') {
        $uploadDir = rtrim($base_path, '/') . '/assets/uploads';
        $save = $pdo->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );

        foreach (['logo', 'favicon'] as $slot) {
            $current = setting($slot);

            // Explicit removal wins over any file that was also chosen.
            if (!empty($_POST['remove_' . $slot])) {
                delete_upload($current, $uploadDir);
                $save->execute([$slot, '']);
                continue;
            }

            try {
                $new = save_brand_upload($slot, $uploadDir);
            } catch (Throwable $ex) {
                $errors[] = ucfirst($slot) . ': ' . $ex->getMessage();
                continue;
            }

            if ($new !== null) {
                $save->execute([$slot, $new]);
                delete_upload($current, $uploadDir);   // replaced — drop the old file
            }
        }

        if (!$errors) {
            flash('ok', 'Brand assets updated. They now appear across the site, the portal and the browser tab.');
            header('Location: ' . portal_url('profile.php'));
            exit;
        }
    }

    // ── Social links ─────────────────────────────────────────────────────────
    if ($action === 'social') {
        $save = $pdo->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );

        foreach (SOCIAL_NETWORKS as $key => $meta) {
            $url = trim((string) ($_POST['social_' . $key] ?? ''));

            if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = $meta['label'] . ': that does not look like a full URL (include https://).';
                continue;
            }
            $save->execute(['social_' . $key, $url]);
        }

        if (!$errors) {
            flash('ok', 'Social links saved. They now appear in the site footer.');
            header('Location: ' . portal_url('profile.php'));
            exit;
        }
    }

    // ── Analytics / Search Console ───────────────────────────────────────────
    if ($action === 'integrations') {
        $ga4 = trim((string) ($_POST['ga4_id'] ?? ''));
        $gsc = trim((string) ($_POST['gsc_verification'] ?? ''));

        // Accept a pasted <meta> tag as well as the bare token — that is what
        // Search Console actually puts on your clipboard.
        if (preg_match('~content=["\']([^"\']+)["\']~', $gsc, $m)) {
            $gsc = $m[1];
        }

        if ($ga4 !== '' && !preg_match('/^G-[A-Z0-9]{6,}$/i', $ga4)) {
            $errors[] = 'Measurement ID should look like G-XXXXXXXXXX.';
        }
        if ($gsc !== '' && !preg_match('/^[A-Za-z0-9_\-]{20,100}$/', $gsc)) {
            $errors[] = 'That Search Console token does not look right — paste the whole meta tag or just the content value.';
        }

        if (!$errors) {
            $save = $pdo->prepare(
                'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
            );
            $save->execute(['ga4_id', strtoupper($ga4)]);
            $save->execute(['gsc_verification', $gsc]);

            flash('ok', 'Integrations saved.');
            header('Location: ' . portal_url('profile.php'));
            exit;
        }
    }

    // ── Password ─────────────────────────────────────────────────────────────
    if ($action === 'password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        // Requiring the current password stops anyone who walks up to an
        // unlocked screen from taking the account over.
        if (!password_verify($current, $user['password_hash'])) {
            $errors[] = 'Your current password is not correct.';
        }
        if (strlen($new) < 10) {
            $errors[] = 'New password must be at least 10 characters.';
        }
        if ($new !== $confirm) {
            $errors[] = 'The new passwords do not match.';
        }
        if ($new !== '' && $new === $current) {
            $errors[] = 'The new password must be different from the current one.';
        }

        if (!$errors) {
            $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);

            session_regenerate_id(true);
            flash('ok', 'Password changed. Keep it somewhere safe — there is no reset link.');
            header('Location: ' . portal_url('profile.php'));
            exit;
        }
    }
}

$pageTitle = 'Profile';
$navActive = 'profile';
require __DIR__ . '/inc/layout-top.php';
?>

<?php foreach ($errors as $err): ?>
    <div class="alert is-error"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="edit-grid">
    <div class="edit-main">

        <!-- DETAILS -->
        <form method="post" class="side-box">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="details" />
            <h3>Account Details</h3>

            <div class="field">
                <label for="full_name">Display name</label>
                <input type="text" id="full_name" name="full_name" maxlength="120"
                    value="<?= e($_POST['full_name'] ?? $user['full_name']) ?>"
                    placeholder="Administrator" />
            </div>

            <div class="field">
                <label for="username">Username <span class="hint">what you sign in with</span></label>
                <input type="text" id="username" name="username" required minlength="3" maxlength="60"
                    autocapitalize="none" spellcheck="false"
                    value="<?= e($_POST['username'] ?? $user['username']) ?>" />
            </div>

            <button type="submit" class="btn-admin is-primary">Save Details</button>
        </form>

        <!-- BUSINESS DETAILS -->
        <form method="post" class="side-box" style="margin-top:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="business" />
            <h3>Business Details</h3>
            <p class="hint" style="margin:-4px 0 18px;">
                Everything here is used across the public site — the header, the footer, the contact page, the
                side contact bar, and the business markup Google reads for your map listing. Change it once here.
            </p>

            <div class="field">
                <label for="biz_name">Business name</label>
                <input type="text" id="biz_name" name="biz_name" required maxlength="120"
                    value="<?= e($_POST['biz_name'] ?? site_name()) ?>" />
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="biz_phone">Phone <span class="hint">shown and dialled</span></label>
                    <input type="text" id="biz_phone" name="biz_phone" required maxlength="40"
                        value="<?= e($_POST['biz_phone'] ?? site_phone()) ?>" placeholder="(972) 555-0180" />
                </div>
                <div class="field">
                    <label for="biz_email">Public email</label>
                    <input type="email" id="biz_email" name="biz_email" required maxlength="120"
                        value="<?= e($_POST['biz_email'] ?? site_email()) ?>" />
                </div>
            </div>

            <div class="field">
                <label for="biz_street">Street address</label>
                <input type="text" id="biz_street" name="biz_street" maxlength="160"
                    value="<?= e($_POST['biz_street'] ?? site_street()) ?>" placeholder="4820 Commerce Park Dr" />
            </div>

            <div class="field-row is-three">
                <div class="field">
                    <label for="biz_city">City</label>
                    <input type="text" id="biz_city" name="biz_city" maxlength="80"
                        value="<?= e($_POST['biz_city'] ?? site_city()) ?>" />
                </div>
                <div class="field">
                    <label for="biz_state">State</label>
                    <input type="text" id="biz_state" name="biz_state" maxlength="40"
                        value="<?= e($_POST['biz_state'] ?? site_state()) ?>" />
                </div>
                <div class="field">
                    <label for="biz_zip">ZIP</label>
                    <input type="text" id="biz_zip" name="biz_zip" maxlength="20"
                        value="<?= e($_POST['biz_zip'] ?? site_zip()) ?>" />
                </div>
            </div>

            <div class="field">
                <label for="biz_map_url">Google Maps link
                    <span class="hint">optional — overrides the address above</span></label>
                <input type="url" id="biz_map_url" name="biz_map_url" maxlength="500"
                    value="<?= e($_POST['biz_map_url'] ?? setting('biz_map_url')) ?>"
                    placeholder="https://maps.app.goo.gl/…" />
                <p class="hint" style="margin-top:8px;">
                    Paste the share link from your Google Business Profile and the map pin lands on your exact
                    door. Leave it empty and we build directions from the address instead.
                </p>
            </div>

            <div class="field">
                <label for="biz_map_embed">Google Maps embed
                    <span class="hint">optional — the map shown on the contact page</span></label>
                <textarea id="biz_map_embed" name="biz_map_embed" rows="3"
                    placeholder="&lt;iframe src=&quot;https://www.google.com/maps/embed?pb=…&quot;&gt;&lt;/iframe&gt;"><?= e($_POST['biz_map_embed'] ?? setting('biz_map_embed')) ?></textarea>
                <p class="hint" style="margin-top:8px;">
                    In Google Maps open your business → <strong>Share</strong> → <strong>Embed a map</strong> →
                    copy, and paste the whole thing here. We pull the address out of it for you. Without one the
                    map just searches for your street address, which puts the pin wherever Google guesses.
                </p>
            </div>

            <div class="field">
                <label for="biz_inquiry_to">Send enquiries to
                    <span class="hint">leave empty to use the public email</span></label>
                <input type="email" id="biz_inquiry_to" name="biz_inquiry_to" maxlength="120"
                    value="<?= e($_POST['biz_inquiry_to'] ?? setting('biz_inquiry_to')) ?>" />
                <p class="hint" style="margin-top:8px;">
                    Where contact-form messages and trade requests are delivered. Useful when the address you
                    publish is not the inbox you actually watch.
                </p>
            </div>

            <button type="submit" class="btn-admin is-primary">Save Business Details</button>
        </form>

        <!-- OPENING HOURS -->
        <form method="post" class="side-box" style="margin-top:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="hours" />
            <h3>Opening Hours</h3>
            <p class="hint" style="margin:-4px 0 18px;">
                Shown on the contact page and in your Google listing. Write times as
                <code>7:00 AM – 5:00 PM</code>, or the word <code>Closed</code>.
            </p>

            <?php foreach (site_hours() as $i => $row): ?>
                <div class="field-row">
                    <div class="field">
                        <label for="hd<?= $i ?>">Days</label>
                        <input type="text" id="hd<?= $i ?>" name="hours_days_<?= $i ?>" maxlength="60"
                            value="<?= e($row['days']) ?>" />
                    </div>
                    <div class="field">
                        <label for="ht<?= $i ?>">Hours</label>
                        <input type="text" id="ht<?= $i ?>" name="hours_<?= $i ?>" maxlength="60"
                            value="<?= e($row['time']) ?>" />
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-admin is-primary">Save Hours</button>
        </form>

        <!-- BRAND -->
        <form method="post" enctype="multipart/form-data" class="side-box" style="margin-top:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="brand" />
            <h3>Logo &amp; Favicon</h3>
            <p class="hint" style="margin:-8px 0 20px;">
                The logo replaces the “R” mark in the site header, the footer, this portal and the sign-in screen. It is
                also published as the business logo in structured data, which is what Google shows beside your listing.
            </p>

            <div class="field-row">
                <div class="field">
                    <label for="logo">Logo <span class="hint">wide mark · SVG or transparent PNG</span></label>
                    <div class="brand-preview">
                        <?php if (has_logo()): ?>
                            <img src="<?= e(logo_url()) ?>" alt="Current logo" />
                        <?php else: ?>
                            <span class="empty-mark">R</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" id="logo" name="logo" accept=".svg,image/svg+xml,image/png,image/webp,image/avif,image/jpeg" />
                    <?php if (has_logo()): ?>
                        <label class="check" style="margin-top:10px;">
                            <input type="checkbox" name="remove_logo" value="1" />
                            <span>Remove logo and go back to the “R” mark</span>
                        </label>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="favicon">Favicon <span class="hint">square · 512×512 recommended</span></label>
                    <div class="brand-preview is-square">
                        <img src="<?= e(favicon_url()) ?>" alt="Current favicon" />
                    </div>
                    <input type="file" id="favicon" name="favicon" accept=".svg,image/svg+xml,image/png,image/webp,image/avif,image/x-icon" />
                    <?php if (setting('favicon') !== ''): ?>
                        <label class="check" style="margin-top:10px;">
                            <input type="checkbox" name="remove_favicon" value="1" />
                            <span>Remove favicon</span>
                        </label>
                    <?php endif; ?>
                </div>
            </div>

            <p class="hint">
                Leave the favicon empty and the logo is used instead — but a wide logo shrinks to an unreadable smudge
                at 16px, so a square version is worth uploading. SVG uploads are cleaned of scripts before they are
                stored.
            </p>

            <button type="submit" class="btn-admin is-primary" style="margin-top:14px;">Save Brand Assets</button>
        </form>

        <!-- SOCIAL LINKS -->
        <form method="post" class="side-box" style="margin-top:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="social" />
            <h3>Social Links</h3>
            <p class="hint" style="margin:-8px 0 20px;">
                Shown in the site footer and published as <code>sameAs</code> in the business schema, which is how
                Google connects your profiles to the company. Leave a field blank to hide that icon.
            </p>

            <div class="social-fields">
                <?php foreach (SOCIAL_NETWORKS as $key => $net): ?>
                    <?php $val = $_POST['social_' . $key] ?? setting('social_' . $key); ?>
                    <div class="social-field<?= $val !== '' ? ' is-set' : '' ?>">
                        <label for="social_<?= e($key) ?>" class="social-label">
                            <span class="social-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><?= $net['icon'] ?></svg>
                            </span>
                            <?= e($net['label']) ?>
                        </label>
                        <input type="url" id="social_<?= e($key) ?>" name="social_<?= e($key) ?>"
                            placeholder="<?= e($net['placeholder']) ?>" value="<?= e($val) ?>" />
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-admin is-primary">Save Social Links</button>
        </form>

        <!-- INTEGRATIONS -->
        <form method="post" class="side-box" style="margin-top:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="integrations" />
            <h3>Google Integrations</h3>

            <div class="field">
                <label for="ga4_id">
                    Google Analytics measurement ID
                    <span class="hint">Analytics → Admin → Data streams → your stream</span>
                </label>
                <input type="text" id="ga4_id" name="ga4_id" placeholder="G-XXXXXXXXXX"
                    value="<?= e($_POST['ga4_id'] ?? setting('ga4_id')) ?>" />
                <span class="counter">
                    <?php if (analytics_id() === ''): ?>
                        Not connected — no tracking script is loaded.
                    <?php elseif (analytics_active()): ?>
                        Active — tracking on <?= e(SITE_DOMAIN) ?>.
                    <?php else: ?>
                        Saved, but paused: tracking is skipped on localhost so your own visits stay out of the reports.
                    <?php endif; ?>
                </span>
            </div>

            <div class="field">
                <label for="gsc_verification">
                    Search Console verification
                    <span class="hint">choose the "HTML tag" method and paste the whole tag here</span>
                </label>
                <input type="text" id="gsc_verification" name="gsc_verification"
                    placeholder='<meta name="google-site-verification" content="..." />'
                    value="<?= e($_POST['gsc_verification'] ?? setting('gsc_verification')) ?>" />
                <span class="counter">
                    <?= gsc_token() !== '' ? 'Verification tag is live in the site head.' : 'Not verified yet.' ?>
                </span>
            </div>

            <button type="submit" class="btn-admin is-primary">Save Integrations</button>

            <div class="ext-links">
                <a href="https://search.google.com/search-console" target="_blank" rel="noopener"
                    class="btn-admin is-small">Open Search Console ↗</a>
                <a href="https://analytics.google.com/" target="_blank" rel="noopener"
                    class="btn-admin is-small">Open Analytics ↗</a>
                <a href="https://business.google.com/" target="_blank" rel="noopener"
                    class="btn-admin is-small">Business Profile ↗</a>
            </div>
        </form>

        <!-- PASSWORD -->
        <form method="post" class="side-box" autocomplete="off" style="margin-top:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="password" />
            <h3>Change Password</h3>

            <div class="field">
                <label for="current_password">Current password</label>
                <div class="field-with-toggle">
                    <input type="password" id="current_password" name="current_password"
                        autocomplete="current-password" required />
                    <button type="button" class="reveal" data-reveal-for="current_password"
                        aria-label="Show password">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="new_password">New password <span class="hint">min 10 characters</span></label>
                    <input type="password" id="new_password" name="new_password" minlength="10"
                        autocomplete="new-password" required />
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm new password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="10"
                        autocomplete="new-password" required />
                </div>
            </div>

            <button type="submit" class="btn-admin is-primary">Change Password</button>
        </form>
    </div>

    <!-- SIDEBAR -->
    <aside class="edit-side">
        <div class="side-box">
            <h3>Signed In</h3>
            <div class="profile-id">
                <span class="profile-avatar"><?= e(strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1))) ?></span>
                <div>
                    <strong><?= e($user['full_name'] ?: $user['username']) ?></strong>
                    <small><?= e($user['username']) ?></small>
                </div>
            </div>

            <dl class="kv-stack">
                <div>
                    <dt>Last sign-in</dt>
                    <dd><?= $user['last_login_at'] ? date('M j, Y \a\t g:i A', strtotime($user['last_login_at'])) : 'This session' ?></dd>
                </div>
                <div>
                    <dt>Account created</dt>
                    <dd><?= date('M j, Y', strtotime($user['created_at'])) ?></dd>
                </div>
            </dl>

            <a href="<?= portal_url('logout.php') ?>" class="btn-admin is-block">Sign Out</a>
        </div>

        <div class="side-box">
            <h3>Recovery</h3>
            <p class="hint" style="margin-top:0;">
                This is the only administrator account and there is no password-reset email. If you lose access,
                delete the row from the <code>admin_users</code> table in phpMyAdmin and run
                <code>install.php</code> again.
            </p>
        </div>
    </aside>
</div>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
