<?php
require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST["subject"];
    $message = "Name: " . $_POST['name'] . "<br>" . "Email: " . $_POST['email'] . "<br><br><br>" . $_POST["message"];
    $receiver = 'info@jpilawfirm.com';

    $sender = '';
    $passwd = '';

    $mail = new PHPMailer(true);
    $response = [];

    try {
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true); // Set email body as HTML
        $mail->isSMTP();
        $mail->ContentType = 'text/html; charset=utf-8';

        $mail->Host = 'smtp.hostinger.com'; // Your SMTP server
        $mail->Port = 587; // Port for SMTP over SSL (465 for Hostinger)
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls'; // Use SSL encryption
        $mail->Username = $sender;
        $mail->Password = $passwd;

        $mail->setFrom($sender, $sender);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->addAddress($receiver);

   if ($mail->send()) {
    $response['success'] = true;
    $response['message'] = 'Email process completed.';
    
    // Redirect to thank you page
    header('Location: index.php');
    exit;
} else {
    $response['success'] = false;
    $response['message'] = 'Mailer Error: ' . $mail->ErrorInfo;
    
    // Redirect to an error page
    header('Location: error.php');
    exit;
}

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Mailer Error: ' . $mail->ErrorInfo;
    }

    // Output response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

function save_mail($mail, $sender, $passwd)
{
    $username = $sender;
    $password = $passwd;
    $mailboxName = 'INBOX.Sent';
    $path = '{imap.hostinger.com:993/imap/ssl/novalidate-cert}INBOX.Sent';

    $mail_string = $mail->getSentMIMEMessage();
    $imapStream = imap_open($path, $username, $password);
    $result = imap_append($imapStream, $path, $mail_string);
    imap_close($imapStream);
    return $result;
}
?>
