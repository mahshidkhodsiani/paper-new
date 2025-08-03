<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If you have Composer installed, this line is sufficient:
require 'vendor/autoload.php';

// If you don't have Composer, use these three lines and comment out the one above:
// require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
// require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
// require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

include "config.php";
// include "db.php";
include "includes.php";

if (isset($_POST['send_code'])) {
    $email = $_POST['email'];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reset_code = rand(100000, 999999);
        $hashed_code = password_hash($reset_code, PASSWORD_DEFAULT);
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $update_stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_code_expires_at = ? WHERE email = ?");
        $update_stmt->bind_param("sss", $hashed_code, $expires_at, $email);
        $update_stmt->execute();

        $mail = new PHPMailer(true);
        try {
            // --- Hostinger SMTP settings (based on your code) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'noreply@paperet.com';
            $mail->Password   = 'Mypaperet@5805';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            // Sender and recipient
            $mail->setFrom('noreply@paperet.com', 'Paperet Team');
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Code';
            $mail->Body    = '
                <p>Hello,</p>
                <p>Your password reset code is: <b>' . $reset_code . '</b></p>
                <p>Please enter this code on the password reset page. This code is valid for 30 minutes.</p>
                <p>Best regards,<br>Paperet Team</p>
            ';
            $mail->AltBody = 'Your password reset code is: ' . $reset_code;

            $mail->send();

            $_SESSION['reset_success'] = "The password reset code has been sent to your email.";
            header("Location: verify_code.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['reset_error'] = "Error sending email. Mailer Error: {$mail->ErrorInfo}";
            header("Location: forgot_password.php");
            exit();
        }
    } else {
        $_SESSION['reset_error'] = "The email you entered is not registered in the system.";
        header("Location: forgot_password.php");
        exit();
    }
} else {
    header("Location: forgot_password.php");
    exit();
}
