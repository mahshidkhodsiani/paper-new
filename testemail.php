<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (isset($_POST['send_present_request'])) {
    $recipient_email = $_POST['email_address'] ?? 'mahshidkhodsiani2@gmail.com';

    $mail = new PHPMailer(true);
    try {
        // سرور SMTP هاست
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com'; // برای Hostinger
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@paperet.com'; // ایمیل شما روی هاست
        $mail->Password = 'Paperet@2251518'; // رمز عبور ایمیل هاست
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // یا PHPMailer::ENCRYPTION_STARTTLS برای پورت 587
        $mail->Port = 465; // یا 587 اگر از TLS استفاده می‌کنی
        $mail->CharSet = 'UTF-8';

        // فرستنده و گیرنده
        $mail->setFrom('noreply@paperet.com', 'تیم Paperet');
        $mail->addAddress($recipient_email);

        // محتوای ایمیل
        $mail->isHTML(true);
        $mail->Subject = 'درخواست ارائه پرزنتیشن برای شما';
        $mail->Body    = '
            <p>با سلام،</p>
            <p>یک درخواست برای ارائه پرزنتیشن برای شما ارسال شده است.</p>
            <p>با احترام،<br>تیم Paperet</p>
        ';
        $mail->AltBody = 'درخواست ارائه پرزنتیشن برای شما ارسال شده است.';

        $mail->send();
        echo 'ایمیل با موفقیت ارسال شد.';
    } catch (Exception $e) {
        echo "خطا در ارسال ایمیل: {$mail->ErrorInfo}";
    }
} else {
?>
    <form method="POST" action="">
        <label for="email_address">ایمیل گیرنده:</label><br>
        <input type="email" id="email_address" name="email_address" required><br><br>
        <button type="submit" name="send_present_request">ارسال درخواست پرزنت</button>
    </form>
<?php } ?>