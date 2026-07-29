<?php
/**
 * Contact form endpoint.
 *
 * Every genuine submission is stored in `inquiries` so nothing is lost if mail
 * delivery fails, then emailed to inquiry_to(). Suspected spam is stored and
 * flagged but never emailed — you review it in the portal instead of your inbox.
 *
 * ⚠ TODO: fill in SMTP_* below, or create inc/mail.credentials.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/spam.php';

header('Content-Type: application/json; charset=utf-8');

// ── SMTP settings ────────────────────────────────────────────────────────────
$smtp = [
    'host'   => 'smtp.hostinger.com',
    'port'   => 587,
    'secure' => 'tls',
    'user'   => '',   // e.g. trade@royalesurfaces.com
    'pass'   => '',
];
$credFile = __DIR__ . '/mail.credentials.php';
if (is_file($credFile)) {
    $smtp = array_merge($smtp, require $credFile);
}

/** Send a JSON response and stop. (`void`, not `never` — must run on PHP 8.0.) */
function respond(bool $ok, string $message = '', int $status = 200): void
{
    http_response_code($status);
    echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => $message]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(false, 'Method not allowed.', 405);
}

$ip = inquiry_ip();

// ── Silent bot traps ─────────────────────────────────────────────────────────
// These return success so a bot never learns which signal caught it — an error
// tells the author exactly what to change on the next run.
if (trim((string) ($_POST['website'] ?? '')) !== '' || trim((string) ($_POST['company_url'] ?? '')) !== '') {
    error_log("Inquiry honeypot tripped from $ip");
    respond(true);
}

// The token is written into the form by JavaScript. Bots that only parse and
// POST the HTML leave it empty.
if (($_POST['js_token'] ?? '') !== ($_SESSION['form_nonce'] ?? '__none__')) {
    error_log("Inquiry JS token mismatch from $ip");
    respond(true);
}

// ── Hard throttle, keyed on IP ───────────────────────────────────────────────
if (isset($pdo) && $pdo instanceof PDO && inquiry_ip_count($pdo, $ip) >= SPAM_IP_HOURLY_LIMIT) {
    respond(false, 'You have sent several messages recently. Please call us instead — we would rather talk anyway.', 429);
}

// ── Validation (mirrors the client-side rules) ───────────────────────────────
$name    = trim((string) ($_POST['name'] ?? ''));
$company = trim((string) ($_POST['company'] ?? ''));
$phone   = trim((string) ($_POST['phone'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$subject = trim((string) ($_POST['inquiry'] ?? ''));

$errors = [];
if (mb_strlen($name) < 2)                                    $errors[] = 'name';
// Company is optional — private clients buying a single slab have none.
if ($company !== '' && mb_strlen($company) < 2)              $errors[] = 'company';
if (strlen(preg_replace('/\D/', '', $phone)) < 10)           $errors[] = 'phone';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))              $errors[] = 'email';
if (mb_strlen($message) < 10)                                $errors[] = 'message';

if ($errors) {
    respond(false, 'Please check the highlighted fields and try again.', 422);
}

// Header injection guard — newlines have no business in these fields.
foreach ([$name, $company, $email, $subject] as $field) {
    if (preg_match('/[\r\n]/', $field)) {
        respond(false, 'Invalid input.', 400);
    }
}

// ── Score ────────────────────────────────────────────────────────────────────
$startedAt = (int) ($_POST['started_at'] ?? 0);
$verdict = spam_score([
    'name'    => $name,
    'company' => $company,
    'email'   => $email,
    'message' => $message,
    'elapsed' => $startedAt > 0 ? time() - $startedAt : 999,
], $pdo ?? null);

// ── Store ────────────────────────────────────────────────────────────────────
$inquiryId = null;
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $st = $pdo->prepare(
            'INSERT INTO inquiries
                (name, company, phone, email, subject, message, ip, user_agent, referer,
                 spam_score, spam_reason, is_spam)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $name, $company, $phone, $email, $subject, $message, $ip,
            substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 255),
            min(255, $verdict['score']),
            substr(implode('; ', $verdict['reasons']), 0, 255),
            $verdict['is_spam'] ? 1 : 0,
        ]);
        $inquiryId = (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log('Inquiry could not be stored: ' . $e->getMessage());
    }
}

// Spam is filed, not delivered. Reply "ok" so the sender gets no feedback loop.
if ($verdict['is_spam']) {
    error_log("Inquiry flagged as spam (score {$verdict['score']}) from $ip: " . implode('; ', $verdict['reasons']));
    respond(true);
}

// ── Compose ──────────────────────────────────────────────────────────────────
$heading = $subject !== '' ? 'Slab inquiry: ' . $subject : 'New inquiry';

$rows = ['Name' => $name];
if ($company !== '') {
    $rows['Company'] = $company;
}
$rows['Phone'] = $phone;
$rows['Email'] = $email;
if ($subject !== '') {
    $rows['Material'] = $subject;
}

$body = '<h2 style="font-family:Georgia,serif;">' . e($heading) . '</h2><table cellpadding="6" style="font-family:Arial,sans-serif;font-size:14px;border-collapse:collapse;">';
foreach ($rows as $label => $value) {
    $body .= '<tr><td style="color:#666;">' . e($label) . '</td><td><strong>' . e($value) . '</strong></td></tr>';
}
$body .= '</table><p style="font-family:Arial,sans-serif;font-size:14px;"><strong>Message</strong><br>'
    . nl2br(e($message)) . '</p>'
    . '<p style="color:#999;font-size:12px;font-family:Arial,sans-serif;">Sent from ' . e(SITE_DOMAIN)
    . ' on ' . date('M j, Y \a\t g:i A T') . ' · IP ' . e($ip) . '</p>';

$plain = $heading . "\n\n";
foreach ($rows as $label => $value) {
    $plain .= $label . ': ' . $value . "\n";
}
$plain .= "\nMessage:\n" . $message . "\n";

// ── Send ─────────────────────────────────────────────────────────────────────
require_once __DIR__ . '/../PHPMail/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMail/src/SMTP.php';
require_once __DIR__ . '/../PHPMail/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$mail = new PHPMailer(true);

try {
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);

    if ($smtp['user'] !== '') {
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->Port       = (int) $smtp['port'];
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = $smtp['secure'];
        $mail->Username   = $smtp['user'];
        $mail->Password   = $smtp['pass'];
        $mail->setFrom($smtp['user'], site_name() . ' Website');
    } else {
        // No SMTP configured yet — fall back to PHP mail() so the form still works.
        $mail->setFrom('no-reply@' . SITE_DOMAIN, site_name() . ' Website');
    }

    $mail->addAddress(inquiry_to());
    $mail->addReplyTo($email, $name);       // hitting Reply answers the customer
    $mail->Subject = $heading . ($company !== '' ? ' — ' . $company : '');
    $mail->Body    = $body;
    $mail->AltBody = $plain;

    $mail->send();

    if ($inquiryId && isset($pdo)) {
        $pdo->prepare('UPDATE inquiries SET emailed = 1 WHERE id = ?')->execute([$inquiryId]);
    }
    respond(true);

} catch (MailException | Throwable $e) {
    error_log('Royale Surfaces inquiry email failed: ' . $e->getMessage());

    // It is saved in the portal, so this is a delivery problem, not data loss.
    if ($inquiryId) {
        respond(true);
    }
    respond(false, 'We could not send your message right now. Please call us instead.', 500);
}
