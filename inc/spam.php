<?php
/**
 * Contact-form spam scoring.
 *
 * Signals are additive and each one records why it fired, so a submission that
 * gets blocked can be explained in the portal rather than vanishing silently.
 * Nothing here is a security control — it filters noise, it does not stop a
 * determined attacker. Validation and escaping do that.
 */

/** At or above this score a submission is filed as spam and not emailed. */
const SPAM_THRESHOLD = 6;

/** Max submissions allowed from one IP address per hour. */
const SPAM_IP_HOURLY_LIMIT = 5;

/** Phrases that never appear in a genuine stone enquiry. */
const SPAM_PHRASES = [
    'seo service', 'seo servic', 'backlink', 'guest post', 'link building',
    'rank your', 'first page of google', 'increase traffic', 'web design service',
    'crypto', 'bitcoin', 'forex', 'casino', 'betting', 'viagra', 'cialis',
    'payday loan', 'work from home', 'make money', 'earn $', 'earn usd',
    'telegram.me', 'wa.me/', 'bit.ly', 'tinyurl',
];

/**
 * Score one submission.
 * Returns ['score' => int, 'reasons' => string[], 'is_spam' => bool].
 */
function spam_score(array $data, ?PDO $pdo = null): array
{
    $score = 0;
    $reasons = [];

    $name    = (string) ($data['name'] ?? '');
    $email   = (string) ($data['email'] ?? '');
    $message = (string) ($data['message'] ?? '');
    $company = (string) ($data['company'] ?? '');
    $elapsed = (int) ($data['elapsed'] ?? 999);
    $blob    = strtolower($name . ' ' . $company . ' ' . $message);

    $add = function (int $points, string $why) use (&$score, &$reasons): void {
        $score += $points;
        $reasons[] = $why;
    };

    // ── Links ────────────────────────────────────────────────────────────────
    // A customer asking about slabs almost never pastes a URL; bots almost always do.
    $links = preg_match_all('~https?://|www\.|\[url~i', $message);
    if ($links >= 3) {
        $add(5, "$links links in the message");
    } elseif ($links > 0) {
        $add(2, "$links link(s) in the message");
    }

    // Markup in a plain-text box means an automated poster.
    if (preg_match('~<a\s|\[url|</?[a-z]+>~i', $message)) {
        $add(4, 'HTML or BBCode markup');
    }

    // ── Content ──────────────────────────────────────────────────────────────
    foreach (SPAM_PHRASES as $phrase) {
        if (str_contains($blob, $phrase)) {
            $add(4, 'Spam phrase: "' . $phrase . '"');
            break;   // one hit is enough; do not stack to absurd totals
        }
    }

    // A URL in the name field is never a person — enough to condemn on its own.
    if (preg_match('~https?://|www\.~i', $name)) {
        $add(SPAM_THRESHOLD, 'Link in the name field');
    } elseif (str_contains($name, '@')) {
        // Weaker: a real visitor sometimes pastes their email into the wrong box.
        $add(2, 'Email address in the name field');
    }

    // ── Shape ────────────────────────────────────────────────────────────────
    $letters = preg_replace('/[^a-z]/i', '', $message) ?? '';
    if (strlen($letters) > 25) {
        $upper = strlen(preg_replace('/[^A-Z]/', '', $message) ?? '');
        if ($upper / max(1, strlen($letters)) > 0.7) {
            $add(2, 'Mostly capital letters');
        }
    }

    // Cyrillic or CJK on an English-only Texas site is a strong signal.
    if (preg_match('~[\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}]~u', $message)) {
        $add(3, 'Non-Latin script');
    }

    // A "message" with no spaces is machine-generated.
    if (mb_strlen($message) > 40 && !str_contains(trim($message), ' ')) {
        $add(4, 'No spaces in the message');
    }

    // ── Timing ───────────────────────────────────────────────────────────────
    // Nobody reads five fields and writes a project brief in three seconds.
    if ($elapsed < 3) {
        $add(5, 'Submitted in under 3 seconds');
    } elseif ($elapsed < 7) {
        $add(2, 'Submitted very quickly');
    }

    // ── Repetition ───────────────────────────────────────────────────────────
    if ($pdo instanceof PDO) {
        try {
            $st = $pdo->prepare(
                'SELECT COUNT(*) FROM inquiries
                 WHERE message = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)'
            );
            $st->execute([$message]);
            if ((int) $st->fetchColumn() > 0) {
                $add(4, 'Identical message already received this week');
            }
        } catch (Throwable $e) {
            // Table missing — scoring still works without this signal.
        }
    }

    return [
        'score'   => $score,
        'reasons' => $reasons,
        'is_spam' => $score >= SPAM_THRESHOLD,
    ];
}

/**
 * How many submissions this IP has made in the past hour.
 *
 * Throttling has to be keyed on IP, not the session: a bot sends no cookies,
 * so a session-based limit gives it a clean slate on every single request.
 */
function inquiry_ip_count(PDO $pdo, string $ip): int
{
    try {
        $st = $pdo->prepare(
            'SELECT COUNT(*) FROM inquiries WHERE ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        $st->execute([$ip]);
        return (int) $st->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/** Caller's IP, trimmed to fit the column. */
function inquiry_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}
