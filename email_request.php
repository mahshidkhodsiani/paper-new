<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Adjust this path based on your setup
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // The recipient email is taken from the form
    $to_email = htmlspecialchars($_POST['presenter_email'] ?? '');

    // Collect and sanitize form data
    $requester_name = htmlspecialchars($_POST['requester_name'] ?? '');
    $requester_email = htmlspecialchars($_POST['requester_email'] ?? '');
    $presenter_name = htmlspecialchars($_POST['presenter_name'] ?? '');
    $presenter_email = htmlspecialchars($_POST['presenter_email'] ?? '');
    $paper_title = htmlspecialchars($_POST['paper_title'] ?? '');
    $paper_abstract = htmlspecialchars($_POST['paper_abstract'] ?? '');

    // Validate required fields
    if (empty($requester_name) || empty($requester_email) || empty($presenter_name) || empty($presenter_email) || empty($paper_title) || empty($paper_abstract) || !isset($_POST['consent'])) {
        echo "Error: Required fields are missing.";
        exit;
    }

    // Build the email body
    $email_body = "A new presentation request has been submitted with the following details:\n\n";
    $email_body .= "--- Requester Details ---\n";
    $email_body .= "Name: " . $requester_name . "\n";
    $email_body .= "Email: " . $requester_email . "\n";
    $email_body .= "Affiliation: " . htmlspecialchars($_POST['requester_affiliation'] ?? '') . "\n";
    $email_body .= "Phone: " . htmlspecialchars($_POST['requester_phone'] ?? '') . "\n\n";

    $email_body .= "--- Presenter Details ---\n";
    $email_body .= "Name: " . $presenter_name . "\n";
    $email_body .= "Email: " . $presenter_email . "\n";
    $email_body .= "Affiliation: " . htmlspecialchars($_POST['presenter_affiliation'] ?? '') . "\n\n";

    $email_body .= "--- Paper Details ---\n";
    $email_body .= "Title: " . $paper_title . "\n";
    $email_body .= "Link: " . htmlspecialchars($_POST['paper_link'] ?? '') . "\n";
    $email_body .= "Abstract: " . $paper_abstract . "\n";
    $email_body .= "Tags: " . htmlspecialchars($_POST['tags'] ?? '') . "\n";
    $email_body .= "Online Presentation: " . (isset($_POST['is_online']) ? 'Yes' : 'No') . "\n\n";

    $email_body .= "--- Competition Details ---\n";
    $email_body .= "For Competition: " . (isset($_POST['include_comp']) && $_POST['include_comp'] == '1' ? 'Yes' : 'No') . "\n";
    if (isset($_POST['include_comp']) && $_POST['include_comp'] == '1') {
        $email_body .= "Competition Name: " . htmlspecialchars($_POST['comp_name'] ?? '') . "\n";
        $email_body .= "Competition Link: " . htmlspecialchars($_POST['comp_link'] ?? '') . "\n";
        $email_body .= "Competition Message: " . htmlspecialchars($_POST['comp_message'] ?? '') . "\n\n";
    }

    $email_body .= "--- Other Details ---\n";
    $email_body .= "Custom Message: " . htmlspecialchars($_POST['custom_message'] ?? '') . "\n";
    $email_body .= "CC Emails: " . htmlspecialchars($_POST['cc_emails'] ?? '') . "\n";

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@paperet.com';
        $mail->Password   = '123456789M@hshid'; // REPLACE WITH YOUR REAL PASSWORD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom('info@paperet.com', 'Paperet Website');
        $mail->addAddress($to_email); // Recipient email from form: presenter_email
        $mail->addReplyTo($requester_email, $requester_name); // Reply-to email from form: requester_email

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
        echo "Email sent successfully!"; // On success, display a success message
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        // On error, display the specific error message
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}
?>