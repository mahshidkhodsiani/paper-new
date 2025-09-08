<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

function sendInvitationEmail($recipientEmail, $recipientName, $competitionTitle, $competitionLink, $role)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP Server Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@paperet.com';
        $mail->Password   = '123456789M@hshid'; // **REPLACE THIS WITH YOUR ACTUAL PASSWORD**
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom('info@paperet.com', 'Paperet Competition');
        $mail->addAddress($recipientEmail, $recipientName);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "Invitation to Competition: {$competitionTitle}";

        $email_body_html = '
        <html>
        <head>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: left; direction: ltr; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                h1 { color: #1a1a1a; text-align: center; }
                p { color: #555555; line-height: 1.6; }
                .button {
                    display: inline-block;
                    background-color: #007bff;
                    color: #ffffff !important;
                    padding: 10px 20px;
                    margin-top: 20px;
                    border-radius: 5px;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Hello ' . htmlspecialchars($recipientName) . ',</h1>
                <p>You have been invited to participate in the competition: <b>' . htmlspecialchars($competitionTitle) . '</b>.</p>
                <p>Your role: <b>' . htmlspecialchars($role) . '</b></p>
                
                <p style="text-align:center;">
                    <a href="' . htmlspecialchars($competitionLink) . '" class="button">View Details and Register</a>
                </p>
                
                <p>Thank you,<br>The Paperet Team</p>
            </div>
        </body>
        </html>
        ';

        $mail->Body    = $email_body_html;
        $mail->AltBody = "You have been invited to participate in the competition {$competitionTitle}. Your role: {$role}. Competition link: {$competitionLink}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send invitation email to {$recipientEmail}. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}