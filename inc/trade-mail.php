<?php
/**
 * Password-reset email for trade accounts. Same SMTP bootstrap as
 * inc/order-mail.php — kept in its own file rather than folded in there
 * because the two have nothing else in common besides how they send mail.
 */

require_once __DIR__ . '/conn.php';

/** Send the reset link. Returns false on failure — the caller still shows the
 *  generic "check your inbox" message either way, so a delivery failure isn't
 *  surfaced to whoever asked for the reset (which could just as easily be
 *  someone probing the form, not the account owner). */
function trade_reset_notify(array $account, string $token): bool
{
    global $base_url;

    try {
        $link = $base_url . 'trade/reset?token=' . $token;

        $html = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:520px;">'
            . '<h2 style="font-family:Georgia,serif;margin:0 0 18px;">Reset your password</h2>'
            . '<p style="font-size:14px;line-height:1.7;color:#333;">Hi ' . e($account['contact_name']) . ',</p>'
            . '<p style="font-size:14px;line-height:1.7;color:#333;">We received a request to reset the password '
            . 'for the ' . e($account['company']) . ' trade account. Click below to choose a new one — this link '
            . 'works once and expires in ' . TRADE_RESET_TTL_MINUTES . ' minutes.</p>'
            . '<p style="margin:26px 0;"><a href="' . e($link) . '" '
            . 'style="display:inline-block;background:#14161a;color:#fff;text-decoration:none;'
            . 'padding:13px 26px;font-size:13px;font-weight:700;letter-spacing:.04em;">RESET PASSWORD</a></p>'
            . '<p style="font-size:12.5px;line-height:1.6;color:#888;">If you did not request this, no action is '
            . 'needed — your password stays what it was and this link will simply expire on its own.</p>'
            . '<p style="font-size:12px;color:#aaa;margin-top:24px;">Or paste this into your browser:<br>'
            . e($link) . '</p>'
            . '</div>';

        $text = "Reset your password\n\n"
            . "We received a request to reset the password for the {$account['company']} trade account.\n\n"
            . "Open this link within " . TRADE_RESET_TTL_MINUTES . " minutes to choose a new one:\n{$link}\n\n"
            . "If you did not request this, no action is needed.";

        require_once __DIR__ . '/../PHPMail/src/PHPMailer.php';
        require_once __DIR__ . '/../PHPMail/src/SMTP.php';
        require_once __DIR__ . '/../PHPMail/src/Exception.php';

        $smtp = ['host' => 'smtp.hostinger.com', 'port' => 587, 'secure' => 'tls', 'user' => '', 'pass' => ''];
        $credFile = __DIR__ . '/mail.credentials.php';
        if (is_file($credFile)) {
            $smtp = array_merge($smtp, require $credFile);
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        if ($smtp['user'] !== '') {
            $mail->isSMTP();
            $mail->Host = $smtp['host'];
            $mail->Port = (int) $smtp['port'];
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = $smtp['secure'];
            $mail->Username = $smtp['user'];
            $mail->Password = $smtp['pass'];
            $mail->setFrom($smtp['user'], site_name());
        } else {
            $mail->setFrom('no-reply@' . SITE_DOMAIN, site_name());
        }

        $mail->addAddress($account['email']);
        $mail->Subject = 'Reset your password — ' . site_name();
        $mail->Body = $html;
        $mail->AltBody = $text;

        return $mail->send();
    } catch (Throwable $e) {
        error_log('Trade password reset email failed for account ' . $account['id'] . ': ' . $e->getMessage());
        return false;
    }
}
