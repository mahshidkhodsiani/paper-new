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

    // Collect and sanitize form data, including new title fields
    $requester_title = htmlspecialchars($_POST['requester_title'] ?? ''); // NEW
    $presenter_title = htmlspecialchars($_POST['presenter_title'] ?? ''); // NEW

    $to_email = htmlspecialchars($_POST['presenter_email'] ?? '');
    $requester_name = htmlspecialchars($_POST['requester_name'] ?? '');
    $requester_email = htmlspecialchars($_POST['requester_email'] ?? '');
    $presenter_name = htmlspecialchars($_POST['presenter_name'] ?? '');
    $presenter_email = htmlspecialchars($_POST['presenter_email'] ?? '');
    $paper_title = htmlspecialchars($_POST['paper_title'] ?? '');

    // Collect all optional fields
    $requester_affiliation = htmlspecialchars($_POST['requester_affiliation'] ?? '');
    $requester_phone = htmlspecialchars($_POST['requester_phone'] ?? '');
    $presenter_affiliation = htmlspecialchars($_POST['presenter_affiliation'] ?? '');
    $paper_link = htmlspecialchars($_POST['paper_link'] ?? '');
    $tags = htmlspecialchars($_POST['tags'] ?? '');
    $custom_message = htmlspecialchars($_POST['custom_message'] ?? '');
    $cc_emails = htmlspecialchars($_POST['cc_emails'] ?? '');
    
    // Competition fields
    $include_comp = $_POST['include_comp'] ?? '0';
    $comp_name = htmlspecialchars($_POST['comp_name'] ?? '');
    $comp_link = htmlspecialchars($_POST['comp_link'] ?? '');
    $comp_message = htmlspecialchars($_POST['comp_message'] ?? '');


    // Combine Title and Name for display in email
    $full_requester_name = (!empty($requester_title) ? $requester_title . ' ' : '') . $requester_name;
    $full_presenter_name = (!empty($presenter_title) ? $presenter_title . ' ' : '') . $presenter_name;


    // Validate required fields
    if (empty($requester_name) || empty($requester_email) || empty($presenter_name) || empty($presenter_email) || empty($paper_title) || !isset($_POST['consent'])) {
        // Redirect on error
        header("Location: present_request_form.php?status=error");
        exit;
    }

    // Build the email body in HTML format with improved structure
    $email_body_html = '
    <html>
    <head>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f4; padding: 20px; line-height: 1.5; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-top: 5px solid #4e73df; }
            h1 { color: #1a1a1a; text-align: center; font-size: 24px; margin-bottom: 20px; }
            h2 { color: #4e73df; font-size: 18px; border-bottom: 1px solid #e9ecef; padding-bottom: 8px; margin-top: 25px; margin-bottom: 15px; }
            p { color: #555555; margin-bottom: 10px; }
            .details-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
            .details-table td { padding: 10px; border: 1px solid #f0f0f0; }
            .label { font-weight: 600; color: #777777; width: 30%; background-color: #fafafa; }
            .value { color: #333333; width: 70%; word-wrap: break-word; }
            .link-value { color: #007bff; text-decoration: none; }
            .message-box { padding: 15px; background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; border-radius: 6px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>📝 New Presentation Request Submitted</h1>
            <p>A new presentation request has been received on ' . $paper_title . ' with the following details:</p>

            <h2>Requester Details</h2>
            <table class="details-table">
                <tr>
                    <td class="label">Name:</td>
                    <td class="value">' . $full_requester_name . '</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td class="value"><a href="mailto:' . $requester_email . '" class="link-value">' . $requester_email . '</a></td>
                </tr>
                <tr>
                    <td class="label">Affiliation:</td>
                    <td class="value">' . ($requester_affiliation ?: 'N/A') . '</td>
                </tr>
                <tr>
                    <td class="label">Phone:</td>
                    <td class="value">' . ($requester_phone ?: 'N/A') . '</td>
                </tr>
            </table>

            <h2>Presenter Details</h2>
            <table class="details-table">
                <tr>
                    <td class="label">Name:</td>
                    <td class="value">' . $full_presenter_name . '</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td class="value"><a href="mailto:' . $presenter_email . '" class="link-value">' . $presenter_email . '</a></td>
                </tr>
                <tr>
                    <td class="label">Affiliation:</td>
                    <td class="value">' . ($presenter_affiliation ?: 'N/A') . '</td>
                </tr>
            </table>

            <h2>Paper Details</h2>
            <table class="details-table">
                <tr>
                    <td class="label">Title:</td>
                    <td class="value">' . $paper_title . '</td>
                </tr>
                <tr>
                    <td class="label">Link:</td>
                    <td class="value">' . (!empty($paper_link) ? '<a href="' . $paper_link . '" class="link-value">' . $paper_link . '</a>' : 'N/A') . '</td>
                </tr>
                <tr>
                    <td class="label">Keywords:</td>
                    <td class="value">' . ($tags ?: 'N/A') . '</td>
                </tr>
            </table>
            
            ';

    if ($include_comp == '1') {
        $email_body_html .= '
            <h2>Competition Details</h2>
            <table class="details-table">
                <tr>
                    <td class="label">Included:</td>
                    <td class="value">Yes</td>
                </tr>
                <tr>
                    <td class="label">Competition Name:</td>
                    <td class="value">' . ($comp_name ?: 'N/A') . '</td>
                </tr>
                <tr>
                    <td class="label">Link:</td>
                    <td class="value">' . (!empty($comp_link) ? '<a href="' . $comp_link . '" class="link-value">' . $comp_link . '</a>' : 'N/A') . '</td>
                </tr>
            </table>
            <div class="message-box">
                <strong>Competition Message:</strong><br>' . nl2br($comp_message) . '
            </div>
        ';
    }

    $email_body_html .= '
            <h2>Other Details</h2>
            <div class="message-box">
                <strong>Custom Message:</strong><br>' . nl2br($custom_message) . '
            </div>
            <table class="details-table" style="margin-top: 15px;">
                <tr>
                    <td class="label">CC Emails:</td>
                    <td class="value">' . ($cc_emails ?: 'N/A') . '</td>
                </tr>
            </table>
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

        // Handle CC Emails
        if (!empty($cc_emails)) {
            $cc_array = explode(',', $cc_emails);
            foreach ($cc_array as $cc) {
                $cc = trim($cc);
                if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                    $mail->addCC($cc);
                }
            }
        }
        
        // Add Attachments
        if (isset($_FILES['paper_file']) && $_FILES['paper_file']['error'] == 0) {
            $mail->addAttachment($_FILES['paper_file']['tmp_name'], $_FILES['paper_file']['name']);
        }
        if (isset($_FILES['comp_file']) && $_FILES['comp_file']['error'] == 0 && $include_comp == '1') {
            $mail->addAttachment($_FILES['comp_file']['tmp_name'], $_FILES['comp_file']['name']);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Presentation Submission: " . $paper_title;
        $mail->Body    = $email_body_html;
        $mail->AltBody = "A new presentation request has been submitted for: " . $paper_title . ". Requester: " . $full_requester_name . " (" . $requester_email . ")"; // Fallback plain-text body

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