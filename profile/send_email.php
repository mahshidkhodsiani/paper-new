<?php

// شروع سشن برای دسترسی به متغیرهای سشن، مثل اطلاعات کاربر لاگین شده
session_start();

// خطوط زیر برای نمایش خطاها در محیط توسعه مفید هستند.
// در محیط پروداکشن (هاست واقعی) بهتر است غیرفعال باشند یا خطاها به فایل لاگ شوند.
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// مطمئن شوید که مسیر به autoload.php صحیح است.
// اگر فایل send_email.php در profile/ قرار دارد و vendor/ در روت پروژه،
// ممکن است نیاز باشد مسیر را به ../vendor/autoload.php تغییر دهید.
require __DIR__ . '/../vendor/autoload.php'; // فرض می‌کنیم vendor در یک سطح بالاتر از profile/ قرار دارد

// **مرحله اول: بررسی وضعیت لاگین کاربر**
if (!isset($_SESSION['user_data'])) {
    // اگر کاربر لاگین نکرده بود، به صفحه لاگین هدایت شود.
    // مسیر ../login.php به این معنی است که login.php یک سطح بالاتر از پوشه profile/ قرار دارد.
    header("Location: ../login.php");
    exit(); // مهم: بعد از هدایت، اجرای اسکریپت متوقف شود.
}

// اگر کاربر لاگین بود، ادامه کد اجرا می‌شود.

if (isset($_POST['send_present_request'])) {
    // ایمیل گیرنده را از فرم دریافت می‌کنیم.
    // اگر ایمیلی وارد نشده بود، از یک ایمیل پیش‌فرض استفاده می‌کنیم.
    $recipient_email = $_POST['email_address'] ?? 'mahshidkhodsiani2@gmail.com';
    // نام مقاله را از فرم دریافت می‌کنیم.
    $article_name = $_POST['article_name'] ?? 'Untitled Article'; // نام مقاله پیش‌فرض

    $mail = new PHPMailer(true);
    try {
        // تنظیمات سرور SMTP هاستینگر (همان تنظیمات قبلی شما)
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com'; // برای Hostinger
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@paperet.com'; // ایمیل شما روی هاست
        $mail->Password = 'Mypaperet@5805'; // رمز عبور ایمیل هاست
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // یا PHPMailer::ENCRYPTION_STARTTLS برای پورت 587
        $mail->Port = 465; // یا 587 اگر از TLS استفاده می‌کنی
        $mail->CharSet = 'UTF-8'; // برای پشتیبانی از کاراکترهای فارسی در صورت نیاز (هرچند ایمیل انگلیسی است)

        // فرستنده و گیرنده
        $mail->setFrom('noreply@paperet.com', 'Paperet Team'); // نام فرستنده به انگلیسی
        $mail->addAddress($recipient_email);

        // محتوای ایمیل به زبان انگلیسی
        $mail->isHTML(true);
        $mail->Subject = 'Presentation Request for Your Article: ' . htmlspecialchars($article_name);
        $mail->Body    = '
            <p>Dear recipient,</p>
            <p>A request has been sent to you for a presentation regarding the article: <strong>' . htmlspecialchars($article_name) . '</strong>.</p>
            <p>Best regards,<br>The Paperet Team</p>
        ';
        $mail->AltBody = 'Dear recipient, A request has been sent to you for a presentation regarding the article: ' . htmlspecialchars($article_name) . '. Best regards, The Paperet Team';

        $mail->send();
        echo 'Email sent successfully.'; // پیغام موفقیت به انگلیسی
    } catch (Exception $e) {
        echo "Error sending email: {$mail->ErrorInfo}"; // پیغام خطا به انگلیسی
    }
} else {
    // فرم HTML برای ارسال درخواست پرزنت
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Request to Present</title>
        <!-- می‌توانید لینک‌های CSS و JS مورد نیاز (مثل Bootstrap) را اینجا اضافه کنید -->
        <!-- مثلاً اگر از Bootstrap استفاده می‌کنید: -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
                /* استفاده از فونت Inter */
                background-color: #f8f9fa;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
            }

            .form-container {
                background-color: #ffffff;
                padding: 30px;
                border-radius: 15px;
                /* گوشه‌های گرد */
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 500px;
            }

            .form-container h2 {
                color: #007bff;
                margin-bottom: 25px;
                font-weight: bold;
            }

            .btn-primary {
                background-color: #007bff;
                border-color: #007bff;
                border-radius: 10px;
                /* گوشه‌های گرد برای دکمه */
                padding: 10px 20px;
                font-size: 1.1rem;
                transition: background-color 0.3s ease;
            }

            .btn-primary:hover {
                background-color: #0056b3;
                border-color: #0056b3;
            }

            .form-control {
                border-radius: 10px;
                /* گوشه‌های گرد برای اینپوت‌ها */
                padding: 10px;
            }
        </style>
    </head>

    <body>
        <div class="form-container">
            <h2 class="text-center">Request to Present</h2>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email_address" class="form-label">Recipient Email:</label>
                    <input type="email" id="email_address" name="email_address" class="form-control" value="mahshidkhodsiani2@gmail.com" required>
                </div>
                <div class="mb-3">
                    <label for="article_name" class="form-label">Article Name:</label>
                    <input type="text" id="article_name" name="article_name" class="form-control" placeholder="Enter article name" required>
                </div>
                <div class="d-grid">
                    <button type="submit" name="send_present_request" class="btn btn-primary">Send Presentation Request</button>
                </div>
            </form>
        </div>
        <!-- می‌توانید لینک‌های JS مورد نیاز (مثل Bootstrap JS) را اینجا اضافه کنید -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>
<?php } ?>