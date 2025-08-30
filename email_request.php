<?php
// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Adjust this path based on your setup
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $to_email = 'info@paperet.com';

    // Collect and sanitize form data
    $requester_name = htmlspecialchars($_POST['requester_name'] ?? '');
    $requester_email = htmlspecialchars($_POST['requester_email'] ?? '');
    $presenter_name = htmlspecialchars($_POST['presenter_name'] ?? '');
    $presenter_email = htmlspecialchars($_POST['presenter_email'] ?? '');
    $paper_title = htmlspecialchars($_POST['paper_title'] ?? '');
    $paper_abstract = htmlspecialchars($_POST['paper_abstract'] ?? '');

    // Validate required fields
    if (empty($requester_name) || empty($requester_email) || empty($presenter_name) || empty($presenter_email) || empty($paper_title) || empty($paper_abstract) || !isset($_POST['consent'])) {
        header("Location: present_request_form.php?message=error");
        exit;
    }

    // Build the email body
    $email_body = "A new presentation request has been submitted with the following details:\n\n";
    $email_body .= "--- Requester Details ---\n";
    $email_body .= "Name: " . $requester_name . "\n";
    $email_body .= "Email: " . $requester_email . "\n";
    // ... (rest of your form data) ...

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = 'your.smtp.host';  // REPLACE with your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your.smtp.username'; // REPLACE with your SMTP username
        $mail->Password   = 'your.smtp.password'; // REPLACE with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('info@paperet.com', 'Paperet Website');
        $mail->addAddress($to_email);
        $mail->addReplyTo($requester_email, $requester_name);

        // Add Attachments
        if (isset($_FILES['paper_file']) && $_FILES['paper_file']['error'] == 0) {
            $mail->addAttachment($_FILES['paper_file']['tmp_name'], $_FILES['paper_file']['name']);
        }
        if (isset($_FILES['comp_file']) && $_FILES['comp_file']['error'] == 0 && isset($_POST['include_comp']) && $_POST['include_comp'] == '1') {
            $mail->addAttachment($_FILES['comp_file']['tmp_name'], $_FILES['comp_file']['name']);
        }

        // Content
        $mail->isHTML(false);
        $mail->Subject = "New Presentation Submission: " . $paper_title;
        $mail->Body    = $email_body;

        $mail->send();
        header("Location: present_request_form.php?message=success");
        exit;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        header("Location: present_request_form.php?message=error");
        exit;
    }
}
?>