<?php
/**
 * Trade account area — authentication, the request list, and order creation.
 *
 * There is no payment anywhere in this flow. A trader assembles a list of
 * materials and submits it as a reservation request; a person prices it and
 * confirms availability afterwards. That matches how slabs actually sell.
 */

require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/slabs.php';

const TRADE_MAX_ATTEMPTS    = 5;
const TRADE_LOCKOUT_MINUTES = 15;
const TRADE_SESSION_IDLE    = 3600 * 8;   // a working day

// ── Session ──────────────────────────────────────────────────────────────────

function trade_user(): ?array
{
    return $_SESSION['trade'] ?? null;
}

function trade_check(): bool
{
    if (empty($_SESSION['trade'])) {
        return false;
    }
    if (time() - ($_SESSION['trade_seen'] ?? 0) > TRADE_SESSION_IDLE) {
        trade_logout();
        return false;
    }
    $_SESSION['trade_seen'] = time();
    return true;
}

function trade_login(array $account): void
{
    session_regenerate_id(true);
    $_SESSION['trade'] = [
        'id'      => (int) $account['id'],
        'company' => $account['company'],
        'name'    => $account['contact_name'],
        'email'   => $account['email'],
    ];
    $_SESSION['trade_seen'] = time();
}

function trade_logout(): void
{
    unset($_SESSION['trade'], $_SESSION['trade_seen'], $_SESSION['trade_cart']);
}

/** Send the visitor to the login page, remembering where they were headed. */
function trade_require_login(string $return = ''): void
{
    global $base_url;
    if (!trade_check()) {
        if ($return !== '') {
            $_SESSION['trade_return'] = $return;
        }
        header('Location: ' . $base_url . 'trade/login');
        exit;
    }
}

// ── Throttling (IP based — a bot sends no cookies) ───────────────────────────

function trade_lockout(PDO $pdo, string $ip): int
{
    try {
        $st = $pdo->prepare(
            'SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), locked_until))
             FROM trade_login_attempts WHERE ip = ? AND locked_until IS NOT NULL'
        );
        $st->execute([$ip]);
        return (int) ($st->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        return 0;
    }
}

function trade_login_fail(PDO $pdo, string $ip): void
{
    $pdo->prepare(
        'INSERT INTO trade_login_attempts (ip, attempts) VALUES (?, 1)
         ON DUPLICATE KEY UPDATE attempts = attempts + 1'
    )->execute([$ip]);

    $st = $pdo->prepare('SELECT attempts FROM trade_login_attempts WHERE ip = ?');
    $st->execute([$ip]);

    if ((int) $st->fetchColumn() >= TRADE_MAX_ATTEMPTS) {
        $pdo->prepare(
            'UPDATE trade_login_attempts SET attempts = 0,
             locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE ip = ?'
        )->execute([TRADE_LOCKOUT_MINUTES, $ip]);
    }
}

function trade_login_reset(PDO $pdo, string $ip): void
{
    $pdo->prepare('DELETE FROM trade_login_attempts WHERE ip = ?')->execute([$ip]);
}

// ── Bot trap ─────────────────────────────────────────────────────────────────
// Same three signals the contact form uses (inc/spam.php / pages/contact.php):
// a decoy field only a script would fill, a submission faster than a human can
// type, and a token JavaScript has to copy in. Shared here so login, register
// and the password-reset request all get it instead of three copies of it.
//
// A trip counts against the same IP counter login failures use — a bot that
// keeps trying gets locked out of the whole /trade area, not just re-served
// the same trap again.

function trade_looks_like_bot(array $post): bool
{
    if (trim((string) ($post['website'] ?? '')) !== '' || trim((string) ($post['company_url'] ?? '')) !== '') {
        return true;
    }

    $startedAt = (int) ($post['started_at'] ?? 0);
    if ($startedAt > 0 && time() - $startedAt < 3) {
        return true;
    }

    if (($post['js_token'] ?? '') !== ($_SESSION['form_nonce'] ?? '__none__')) {
        return true;
    }

    return false;
}

// ── Password reset ───────────────────────────────────────────────────────────
// The email is never confirmed to exist — every request gets the same "check
// your inbox" message, whether or not an account is behind it. Only the token
// (32 random bytes, hashed at rest, one-hour window, single use) proves you
// actually control the mailbox.

const TRADE_RESET_TTL_MINUTES = 60;

/**
 * Issue a reset token for an email, if the account exists and hasn't just been
 * sent one. Returns the raw token to email out, or null — a null covers both
 * "no such account" and "already sent one moments ago", indistinguishably on
 * purpose, so the caller's response can't be used to probe for real accounts.
 */
function trade_password_reset_request(PDO $pdo, string $email): ?array
{
    // "Issued less than two minutes ago" is computed with TIMESTAMPDIFF, not
    // PHP's strtotime()/time() — comparing a MySQL timestamp against PHP's
    // clock that way silently breaks the moment the two run in different
    // timezones (this bit trade_lockout() before; see inc/trade.php history).
    $threshold = TRADE_RESET_TTL_MINUTES * 60 - 120;
    $st = $pdo->prepare(
        "SELECT id, company, contact_name, email,
                (reset_expires IS NOT NULL AND TIMESTAMPDIFF(SECOND, NOW(), reset_expires) > ?) AS recently_issued
         FROM trade_accounts WHERE email = ? LIMIT 1"
    );
    $st->execute([$threshold, $email]);
    $account = $st->fetch();
    if (!$account) {
        return null;
    }
    if ((bool) $account['recently_issued']) {
        return null;
    }

    $token = bin2hex(random_bytes(32));
    $pdo->prepare(
        'UPDATE trade_accounts SET reset_token_hash = ?,
         reset_expires = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?'
    )->execute([hash('sha256', $token), TRADE_RESET_TTL_MINUTES, $account['id']]);

    return ['account' => $account, 'token' => $token];
}

/** The account a reset token belongs to, or null if it's wrong, used, or expired. */
function trade_password_reset_lookup(PDO $pdo, string $token): ?array
{
    if ($token === '') {
        return null;
    }
    $st = $pdo->prepare(
        'SELECT * FROM trade_accounts WHERE reset_token_hash = ? AND reset_expires > NOW() LIMIT 1'
    );
    $st->execute([hash('sha256', $token)]);
    return $st->fetch() ?: null;
}

/** Set a new password and burn the token — resets are single-use. */
function trade_password_reset_complete(PDO $pdo, int $accountId, string $newPassword): void
{
    $pdo->prepare(
        'UPDATE trade_accounts SET password_hash = ?, reset_token_hash = NULL, reset_expires = NULL WHERE id = ?'
    )->execute([password_hash($newPassword, PASSWORD_DEFAULT), $accountId]);
}

// ── Request list (the "cart") ────────────────────────────────────────────────
// Kept in the session: it is a working list, not an order, and it should not
// outlive the browser session or clutter the database.

function cart_items(): array
{
    return $_SESSION['trade_cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(array_column(cart_items(), 'quantity'));
}

function cart_lines(): int
{
    return count(cart_items());
}

/**
 * Add or top up a line. Keyed by slab slug so repeats merge.
 *
 * Quantities are capped at what is physically on the floor — a trader can never
 * request more slabs than exist. Returns false when the slab is unknown or sold
 * out, and 'capped' when the request was trimmed to the remaining stock.
 *
 * @return bool|string true | false | 'capped'
 */
function cart_add(string $slug, int $qty = 1, string $size = '', string $notes = '')
{
    $slab = slabs_find($slug);
    if (!$slab) {
        return false;
    }

    $stock = slab_stock($slab);
    if ($stock < 1) {
        return false;
    }

    $qty = max(1, min(999, $qty));
    $cart = cart_items();
    $wanted = isset($cart[$slug]) ? $cart[$slug]['quantity'] + $qty : $qty;
    $final  = min($stock, $wanted);
    $capped = $final < $wanted;

    if (isset($cart[$slug])) {
        $cart[$slug]['quantity'] = $final;
        if ($size !== '')  $cart[$slug]['size']  = $size;
        if ($notes !== '') $cart[$slug]['notes'] = $notes;
    } else {
        $cart[$slug] = [
            'slug'     => $slug,
            'name'     => $slab['name'],
            'type'     => $slab['type'],
            'image'    => $slab['image'],
            'thickness'=> $slab['thickness'] ?? '',
            'quantity' => $final,
            'size'     => $size !== '' ? $size : ($slab['size'] ?? ''),
            'notes'    => $notes,
        ];
    }

    $_SESSION['trade_cart'] = $cart;
    return $capped ? 'capped' : true;
}

/** @return bool|string true | false | 'capped' */
function cart_update(string $slug, int $qty, string $size = '', string $notes = '')
{
    $cart = cart_items();
    if (!isset($cart[$slug])) {
        return false;
    }
    if ($qty < 1) {
        unset($cart[$slug]);
        $_SESSION['trade_cart'] = $cart;
        return true;
    }

    $stock = slab_stock_for($slug);
    if ($stock < 1) {
        // Sold out while the list was open — drop it rather than carry a line
        // that can never be fulfilled.
        unset($cart[$slug]);
        $_SESSION['trade_cart'] = $cart;
        return false;
    }

    $final = min($stock, 999, $qty);
    $cart[$slug]['quantity'] = $final;
    $cart[$slug]['size']     = $size;
    $cart[$slug]['notes']    = $notes;

    $_SESSION['trade_cart'] = $cart;
    return $final < $qty ? 'capped' : true;
}

/**
 * Re-check every line against current stock. Called before the list is shown or
 * submitted, because stock can move while a trader is still deciding.
 *
 * @return array<string> human-readable notes about what changed
 */
function cart_reconcile(): array
{
    $changes = [];
    $cart = cart_items();

    foreach ($cart as $slug => $line) {
        $stock = slab_stock_for($slug);

        if ($stock < 1) {
            unset($cart[$slug]);
            $changes[] = $line['name'] . ' is now sold out and has been removed from your list.';
        } elseif ($line['quantity'] > $stock) {
            $cart[$slug]['quantity'] = $stock;
            $changes[] = $line['name'] . ' is down to ' . $stock . ' slab' . ($stock === 1 ? '' : 's')
                . ' — your quantity was reduced.';
        }
    }

    if ($changes) {
        $_SESSION['trade_cart'] = $cart;
    }
    return $changes;
}

function cart_remove(string $slug): void
{
    $cart = cart_items();
    unset($cart[$slug]);
    $_SESSION['trade_cart'] = $cart;
}

function cart_clear(): void
{
    unset($_SESSION['trade_cart']);
}

// ── Orders ───────────────────────────────────────────────────────────────────

/** Human-friendly reference: RS-260727-4F2A. */
function order_reference(): string
{
    return 'RS-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
}

/**
 * Turn the current request list into an order.
 * Returns the new order's id and reference, or null if the list was empty.
 */
function order_create(PDO $pdo, int $accountId, string $notes, string $neededBy): ?array
{
    cart_reconcile();          // last guard against submitting more than exists
    $items = cart_items();
    if (!$items) {
        return null;
    }

    $reference = order_reference();

    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'INSERT INTO orders (reference, account_id, notes, needed_by) VALUES (?,?,?,?)'
        )->execute([
            $reference,
            $accountId,
            $notes,
            preg_match('/^\d{4}-\d{2}-\d{2}$/', $neededBy) ? $neededBy : null,
        ]);
        $orderId = (int) $pdo->lastInsertId();

        // Slab details are copied, not referenced — an order must still read
        // correctly a year later even if that slab has been sold and deleted.
        $st = $pdo->prepare(
            'INSERT INTO order_items (order_id, slab_name, slab_type, slab_slug, quantity, size_note, item_notes)
             VALUES (?,?,?,?,?,?,?)'
        );
        foreach ($items as $it) {
            $st->execute([
                $orderId, $it['name'], $it['type'], $it['slug'],
                $it['quantity'], $it['size'], $it['notes'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Order creation failed: ' . $e->getMessage());
        return null;
    }

    cart_clear();
    return ['id' => $orderId, 'reference' => $reference];
}

/** Orders for one account, newest first, with their line counts. */
function orders_for_account(PDO $pdo, int $accountId): array
{
    $st = $pdo->prepare(
        'SELECT o.*, COUNT(i.id) AS line_count, COALESCE(SUM(i.quantity), 0) AS slab_count
         FROM orders o LEFT JOIN order_items i ON i.order_id = o.id
         WHERE o.account_id = ? GROUP BY o.id ORDER BY o.created_at DESC'
    );
    $st->execute([$accountId]);
    return $st->fetchAll();
}

function order_items(PDO $pdo, int $orderId): array
{
    $st = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
    $st->execute([$orderId]);
    return $st->fetchAll();
}

// ── Stock ledger ─────────────────────────────────────────────────────────────
// Stock comes off the floor when the admin confirms a request, and goes back if
// that request is later cancelled. Nothing is held while a request is merely
// sitting in the queue — an unanswered request must not make a slab look sold.

/** Statuses that mean the slabs are spoken for. */
const ORDER_HOLDS_STOCK = ['confirmed', 'ready', 'completed'];

/**
 * Lines in an order that ask for more than the floor currently holds.
 * Empty means the order can be confirmed as it stands.
 */
function order_stock_shortfall(PDO $pdo, int $orderId): array
{
    $short = [];
    foreach (order_items($pdo, $orderId) as $i) {
        if ($i['slab_slug'] === '') {
            continue;      // slab deleted since — nothing left to decrement
        }
        $have = slab_stock_for($i['slab_slug']);
        if ((int) $i['quantity'] > $have) {
            $short[] = ['name' => $i['slab_name'], 'want' => (int) $i['quantity'], 'have' => $have];
        }
    }
    return $short;
}

/**
 * Bring the stock ledger in line with an order's status.
 *
 * Idempotent: `orders.stock_applied` records whether this order's slabs are
 * already off the floor, so repeated saves never double-count.
 *
 * @return bool false if confirming would oversell (nothing was changed)
 */
function order_sync_stock(PDO $pdo, int $orderId, string $status): bool
{
    $st = $pdo->prepare('SELECT stock_applied FROM orders WHERE id = ?');
    $st->execute([$orderId]);
    $applied = (bool) $st->fetchColumn();
    $shouldHold = in_array($status, ORDER_HOLDS_STOCK, true);

    if ($applied === $shouldHold) {
        return true;
    }

    if ($shouldHold && order_stock_shortfall($pdo, $orderId)) {
        return false;
    }

    $sign = $shouldHold ? '-' : '+';
    $pdo->beginTransaction();
    try {
        $items = order_items($pdo, $orderId);
        $move = $pdo->prepare(
            "UPDATE slabs SET stock = GREATEST(0, stock $sign ?) WHERE slug = ?"
        );
        foreach ($items as $i) {
            if ($i['slab_slug'] !== '') {
                $move->execute([(int) $i['quantity'], $i['slab_slug']]);
            }
        }
        $pdo->prepare('UPDATE orders SET stock_applied = ? WHERE id = ?')
            ->execute([$shouldHold ? 1 : 0, $orderId]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Stock sync failed for order ' . $orderId . ': ' . $e->getMessage());
        return false;
    }

    slabs_reset_cache();
    return true;
}

/** Human label + tone for an order status. */
function order_status_label(string $status): array
{
    return [
        'new'       => ['New request',  'live'],
        'quoted'    => ['Quoted',       'draft'],
        'confirmed' => ['Confirmed',    'live'],
        'ready'     => ['Ready',        'live'],
        'completed' => ['Completed',    ''],
        'cancelled' => ['Cancelled',    'draft'],
    ][$status] ?? [ucfirst($status), ''];
}
