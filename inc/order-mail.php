<?php
/**
 * Emails the full order to inquiry_to() the moment a trader submits it, and sends
 * the trader a copy so both sides have the same record.
 *
 * Delivery failure is logged, never surfaced — the order is already saved and
 * visible in the portal, so a bounced email is not the trader's problem.
 */

require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/trade.php';

/** Build the HTML body for one order. */
function order_email_html(array $order, array $account, array $items): string
{
    $rows = '';
    foreach ($items as $i) {
        $rows .= '<tr>'
            . '<td style="padding:9px 12px;border-bottom:1px solid #eee;"><strong>' . e($i['slab_name']) . '</strong>'
            . ($i['slab_type'] !== '' ? '<br><span style="color:#888;font-size:12px;">' . e(ucfirst(str_replace('-', ' ', $i['slab_type']))) . '</span>' : '')
            . '</td>'
            . '<td style="padding:9px 12px;border-bottom:1px solid #eee;text-align:center;font-size:17px;"><strong>' . (int) $i['quantity'] . '</strong></td>'
            . '<td style="padding:9px 12px;border-bottom:1px solid #eee;">' . e($i['size_note'] ?: '—') . '</td>'
            . '<td style="padding:9px 12px;border-bottom:1px solid #eee;color:#555;">' . e($i['item_notes'] ?: '—') . '</td>'
            . '</tr>';
    }

    $totalSlabs = array_sum(array_column($items, 'quantity'));

    return '<div style="font-family:Arial,Helvetica,sans-serif;max-width:680px;">'
        . '<h2 style="font-family:Georgia,serif;margin:0 0 4px;">Reservation request ' . e($order['reference']) . '</h2>'
        . '<p style="color:#888;margin:0 0 22px;font-size:13px;">'
        . date('l j F Y \a\t g:i A', strtotime($order['created_at'])) . '</p>'

        . '<h3 style="font-size:13px;letter-spacing:.1em;text-transform:uppercase;color:#888;margin:0 0 8px;">Account</h3>'
        . '<table cellpadding="0" style="font-size:14px;margin-bottom:24px;border-collapse:collapse;">'
        . '<tr><td style="padding:3px 18px 3px 0;color:#888;">Company</td><td><strong>' . e($account['company']) . '</strong></td></tr>'
        . '<tr><td style="padding:3px 18px 3px 0;color:#888;">Contact</td><td>' . e($account['contact_name']) . '</td></tr>'
        . '<tr><td style="padding:3px 18px 3px 0;color:#888;">Email</td><td><a href="mailto:' . e($account['email']) . '">' . e($account['email']) . '</a></td></tr>'
        . '<tr><td style="padding:3px 18px 3px 0;color:#888;">Phone</td><td>' . e($account['phone']) . '</td></tr>'
        . ($account['city'] !== '' ? '<tr><td style="padding:3px 18px 3px 0;color:#888;">City</td><td>' . e($account['city']) . '</td></tr>' : '')
        . '</table>'

        . '<h3 style="font-size:13px;letter-spacing:.1em;text-transform:uppercase;color:#888;margin:0 0 8px;">'
        . count($items) . ' line(s) · ' . $totalSlabs . ' slab(s)</h3>'
        . '<table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:14px;border:1px solid #eee;">'
        . '<tr style="background:#fafafa;">'
        . '<th style="padding:9px 12px;text-align:left;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#888;">Material</th>'
        . '<th style="padding:9px 12px;text-align:center;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#888;">Qty</th>'
        . '<th style="padding:9px 12px;text-align:left;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#888;">Size</th>'
        . '<th style="padding:9px 12px;text-align:left;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#888;">Notes</th>'
        . '</tr>' . $rows . '</table>'

        . ($order['needed_by'] ? '<p style="margin-top:20px;font-size:14px;"><strong>Needed by:</strong> '
            . date('j F Y', strtotime($order['needed_by'])) . '</p>' : '')

        . ($order['notes'] !== '' && $order['notes'] !== null
            ? '<h3 style="font-size:13px;letter-spacing:.1em;text-transform:uppercase;color:#888;margin:22px 0 6px;">Message</h3>'
              . '<p style="font-size:14px;line-height:1.7;background:#fafafa;padding:14px 16px;border-left:3px solid #947733;">'
              . nl2br(e($order['notes'])) . '</p>'
            : '')

        . '<p style="margin-top:26px;font-size:12px;color:#999;">No pricing has been quoted. '
        . 'Open the portal to confirm availability and respond.</p>'
        . '</div>';
}

/** Plain-text fallback. */
function order_email_text(array $order, array $account, array $items): string
{
    $out = "Reservation request {$order['reference']}\n"
        . date('j F Y g:i A', strtotime($order['created_at'])) . "\n\n"
        . "Company: {$account['company']}\nContact: {$account['contact_name']}\n"
        . "Email:   {$account['email']}\nPhone:   {$account['phone']}\n\n"
        . "ITEMS\n";

    foreach ($items as $i) {
        $out .= "  {$i['quantity']} × {$i['slab_name']}"
            . ($i['size_note'] !== '' ? "  [{$i['size_note']}]" : '')
            . ($i['item_notes'] !== '' ? "  — {$i['item_notes']}" : '') . "\n";
    }
    if ($order['needed_by']) {
        $out .= "\nNeeded by: " . date('j F Y', strtotime($order['needed_by'])) . "\n";
    }
    if (!empty($order['notes'])) {
        $out .= "\nMessage:\n{$order['notes']}\n";
    }
    return $out;
}

/**
 * Send the order to the business and a copy to the trader.
 * Returns true only if the business copy went out.
 */
function order_notify(PDO $pdo, int $orderId): bool
{
    try {
        $st = $pdo->prepare(
            'SELECT o.*, a.company, a.contact_name, a.email, a.phone, a.city
             FROM orders o JOIN trade_accounts a ON a.id = o.account_id WHERE o.id = ?'
        );
        $st->execute([$orderId]);
        $row = $st->fetch();
        if (!$row) {
            return false;
        }

        $items = order_items($pdo, $orderId);
        $account = [
            'company' => $row['company'], 'contact_name' => $row['contact_name'],
            'email' => $row['email'], 'phone' => $row['phone'], 'city' => $row['city'],
        ];

        $html  = order_email_html($row, $account, $items);
        $plain = order_email_text($row, $account, $items);

        require_once __DIR__ . '/../PHPMail/src/PHPMailer.php';
        require_once __DIR__ . '/../PHPMail/src/SMTP.php';
        require_once __DIR__ . '/../PHPMail/src/Exception.php';

        $smtp = ['host' => 'smtp.hostinger.com', 'port' => 587, 'secure' => 'tls', 'user' => '', 'pass' => ''];
        $credFile = __DIR__ . '/mail.credentials.php';
        if (is_file($credFile)) {
            $smtp = array_merge($smtp, require $credFile);
        }

        $send = function (string $to, string $subject, string $body, string $text, ?string $replyTo = null) use ($smtp): bool {
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

            $mail->addAddress($to);
            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $text;
            return $mail->send();
        };

        $ok = $send(
            inquiry_to(),
            'Reservation request ' . $row['reference'] . ' — ' . $row['company'],
            $html,
            $plain,
            $row['email']          // replying answers the trader directly
        );

        // Trader's copy. A failure here must not affect the business copy.
        try {
            $send(
                $row['email'],
                'We received your request ' . $row['reference'] . ' — ' . site_name(),
                '<p style="font-family:Arial,sans-serif;">Thank you — we have your request and will confirm '
                . 'availability and pricing within one business day.</p>' . $html,
                "Thank you — we have your request and will confirm availability and pricing within one business day.\n\n" . $plain
            );
        } catch (Throwable $e) {
            error_log('Trader copy failed for order ' . $orderId . ': ' . $e->getMessage());
        }

        return $ok;
    } catch (Throwable $e) {
        error_log('Order notification failed for ' . $orderId . ': ' . $e->getMessage());
        return false;
    }
}
