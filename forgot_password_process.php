<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// --- تنظیمات بارگذاری PHPMailer ---
// اگر از Composer استفاده می‌کنید، خط اول را از کامنت خارج کنید و خطوط بعدی را کامنت کنید:
// require 'vendor/autoload.php';

// اگر PHPMailer را دستی اضافه کرده‌اید (مانند کدی که درست کار می‌کرد):
require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

include "config.php";
// include "db.php";
include "includes.php";


if (isset($_POST['send_code'])) {
    $email = $_POST['email'];

    // اتصال به پایگاه داده
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // بررسی وجود ایمیل در دیتابیس
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ایجاد کد ریست و هش کردن
        $reset_code = rand(100000, 999999);
        $hashed_code = password_hash($reset_code, PASSWORD_DEFAULT);
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        // ذخیره کد ریست و زمان انقضا در دیتابیس
        $update_stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_code_expires_at = ? WHERE email = ?");
        $update_stmt->bind_param("sss", $hashed_code, $expires_at, $email);
        $update_stmt->execute();

        // ارسال ایمیل
        $mail = new PHPMailer(true);
        try {
            // --- تنظیمات SMTP اصلاح شده (برگرفته از کد موفق) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@paperet.com';         // ✅ آدرس ایمیل درست
            $mail->Password   = '123456789M@hshid';          // ✅ رمز عبور درست
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            // فرستنده و گیرنده
            $mail->setFrom('info@paperet.com', 'Paperet Team'); // ✅ تنظیم آدرس فرستنده
            $mail->addAddress($email);

            // محتوای ایمیل
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

            $_SESSION['email_for_reset'] = $email; // ذخیره ایمیل کاربر در سشن

            $_SESSION['reset_success'] = "The password reset code has been sent to your email.";
            header("Location: verify_code.php");
            exit();
        } catch (Exception $e) {
            // ثبت خطای کامل در لاگ سرور
            error_log("Mailer Error: {$mail->ErrorInfo}");
            $_SESSION['reset_error'] = "Error sending email. Mailer Error: {$mail->ErrorInfo}";
            header("Location: forgot_password.php");
            exit();
        }
    } else {
        // ایمیل در سیستم ثبت نشده است
        $_SESSION['reset_error'] = "The email you entered is not registered in the system.";
        header("Location: forgot_password.php");
        exit();
    }
} else {
    // اگر به صورت مستقیم فراخوانی شده باشد، به صفحه فرم برگردانده می‌شود
    header("Location: forgot_password.php");
    exit();
}