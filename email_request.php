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

    // Collect and sanitize form data
    $to_email = htmlspecialchars($_POST['presenter_email'] ?? '');
    $requester_name = htmlspecialchars($_POST['requester_name'] ?? '');
    $requester_email = htmlspecialchars($_POST['requester_email'] ?? '');
    $presenter_name = htmlspecialchars($_POST['presenter_name'] ?? '');
    $presenter_email = htmlspecialchars($_POST['presenter_email'] ?? '');
    $paper_title = htmlspecialchars($_POST['paper_title'] ?? '');


    // Validate required fields
    if (empty($requester_name) || empty($requester_email) || empty($presenter_name) || empty($presenter_email) || empty($paper_title) || !isset($_POST['consent'])) {
        // Redirect on error
        header("Location: present_request_form.php?status=error");
        exit;
    }

    // Build the email body in HTML format
    $email_body_html = '
    <html>
    <head>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            h1 { color: #1a1a1a; text-align: center; }
            h2 { color: #333333; border-bottom: 2px solid #eeeeee; padding-bottom: 10px; margin-top: 30px; }
            p { color: #555555; line-height: 1.6; }
            .details-section { margin-bottom: 20px; }
            .details-item { margin-bottom: 10px; }
            .label { font-weight: bold; color: #777777; display: block; margin-bottom: 4px; }
            .value { background-color: #f9f9f9; padding: 8px; border-radius: 4px; border: 1px solid #e0e0e0; word-wrap: break-word; }
            .link-value { color: #007bff; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>New Presentation Request</h1>
            <p>A new presentation request has been submitted with the following details:</p>

            <div class="details-section">
                <h2>Requester Details</h2>
                <div class="details-item">
                    <span class="label">Name:</span>
                    <span class="value">' . $requester_name . '</span>
                </div>
                <div class="details-item">
                    <span class="label">Email:</span>
                    <span class="value">' . $requester_email . '</span>
                </div>
                <div class="details-item">
                    <span class="label">Affiliation:</span>
                    <span class="value">' . htmlspecialchars($_POST['requester_affiliation'] ?? '') . '</span>
                </div>
                <div class="details-item">
                    <span class="label">Phone:</span>
                    <span class="value">' . htmlspecialchars($_POST['requester_phone'] ?? '') . '</span>
                </div>
            </div>

            <div class="details-section">
                <h2>Presenter Details</h2>
                <div class="details-item">
                    <span class="label">Name:</span>
                    <span class="value">' . $presenter_name . '</span>
                </div>
                <div class="details-item">
                    <span class="label">Email:</span>
                    <span class="value">' . $presenter_email . '</span>
                </div>
                <div class="details-item">
                    <span class="label">Affiliation:</span>
                    <span class="value">' . htmlspecialchars($_POST['presenter_affiliation'] ?? '') . '</span>
                </div>
            </div>

            <div class="details-section">
                <h2>Paper Details</h2>
                <div class="details-item">
                    <span class="label">Title:</span>
                    <span class="value">' . $paper_title . '</span>
                </div>
                <div class="details-item">
                    <span class="label">Link:</span>
                    <span class="value"><a href="' . htmlspecialchars($_POST['paper_link'] ?? '') . '" class="link-value">' . htmlspecialchars($_POST['paper_link'] ?? '') . '</a></span>
                </div>
                
            </div>
    ';

    if (isset($_POST['include_comp']) && $_POST['include_comp'] == '1') 
                {
        $email_body_html .= '
            <div class="details-section">
                    <h2>Competition Details</h2>
                <div class="details-item">
                    <span class="label">For Competition:</span>
                    <span class="value">Yes</span>
                </div>
                <div class="details-item">
                    <span class="label">Competition Name:</span>
                    <span class="value">' . htmlspecialchars($_POST['comp_name'] ?? '') . '</span>
                </div>
                <div class="details-item">
                    <span class="label">Competition Link:</span>
                    <span class="value"><a href="' . htmlspecialchars($_POST['comp_link'] ?? '') . '" class="link-value">' . htmlspecialchars($_POST['comp_link'] ?? '') . '</a></span>
                </div>
                <div class="details-item">
                    <span class="label">Competition Message:</span>
                    <p class="value">' . nl2br(htmlspecialchars($_POST['comp_message'] ?? '')) . '</p>
                </div>
            </div>
        ';
    }

    $email_body_html .= '
            <div class="details-section">
                <h2>Other Details</h2>
                <div class="details-item">
                    <span class="label">Custom Message:</span>
                    <p class="value">' . nl2br(htmlspecialchars($_POST['custom_message'] ?? '')) . '</p>
                </div>
                <div class="details-item">
                    <span class="label">CC Emails:</span>
                    <span class="value">' . htmlspecialchars($_POST['cc_emails'] ?? '') . '</span>
                </div>
            </div>
        </div>
    </body>
    </html>
    ';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host      = 'smtp.hostinger.com';
        $mail->SMTPAuth  = true;
        $mail->Username  = 'info@paperet.com';
        $mail->Password  = '123456789M@hshid'; // REPLACE WITH YOUR REAL PASSWORD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port      = 465;
        $mail->CharSet   = 'UTF-8';

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
        $mail->isHTML(true);
        $mail->Subject = "New Presentation Submission: " . $paper_title;
        $mail->Body    = $email_body_html;
        $mail->AltBody = "A new presentation request has been submitted..."; // Fallback plain-text body

        $mail->send();

        // Redirect on success
        header("Location: present_request_form.php?status=success");
        exit;
    } catch (Exception $e) {
        // Redirect on error
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        header("Location: present_request_form.php?status=error");
        exit;
    }
}
