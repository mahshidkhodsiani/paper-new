<?php
session_start();

if (!isset($_SESSION['user_data'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_data']['id'];
include "../config.php";

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // مسیر را بر اساس ساختار پروژه تنظیم کنید

// Process form submission
if (isset($_POST['send_present_request'])) {
    $recipient_email = $_POST['email_address'];
    $message = $_POST['custom_message'] ?? '';

    try {
        $mail = new PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@paperet.com';
        $mail->Password = 'Paperet@2251518';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        // Sender and recipient
        $mail->setFrom('noreply@paperet.com', 'تیم Paperet');
        $mail->addAddress($recipient_email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'درخواست ارائه پرزنتیشن برای شما';

        $emailBody = '
            <div style="font-family: Tahoma, sans-serif; direction: rtl; text-align: right;">
                <h2 style="color: #007bff;">درخواست ارائه پرزنتیشن</h2>
                <p>سلام،</p>
                <p>شما یک درخواست برای ارائه پرزنتیشن مقاله خود دریافت کرده‌اید.</p>
                ';

        if (!empty($message)) {
            $emailBody .= '
                <div style="background: #f8f9fa; padding: 15px; border-right: 4px solid #007bff; margin: 15px 0;">
                    <p><strong>پیام شخصی از درخواست‌دهنده:</strong></p>
                    <p>' . nl2br(htmlspecialchars($message)) . '</p>
                </div>
            ';
        }

        $emailBody .= '
                <p>لطفاً برای پاسخ به این درخواست وارد حساب کاربری خود در Paperet شوید.</p>
                <p>با تشکر،<br>تیم Paperet</p>
            </div>
        ';

        $mail->Body = $emailBody;
        $mail->AltBody = 'درخواست ارائه پرزنتیشن برای شما ارسال شده است. لطفاً به حساب کاربری خود مراجعه کنید.';

        if ($mail->send()) {
            $success_msg = "درخواست با موفقیت ارسال شد!";
        } else {
            $error_msg = "خطا در ارسال درخواست. لطفاً مجدداً تلاش کنید.";
        }
    } catch (Exception $e) {
        $error_msg = "خطا در ارسال ایمیل: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request To Present</title>
    <?php include "../includes.php"; ?>
    <style>
        .presentation-request-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .request-form label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .request-form input,
        .request-form textarea {
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
        }

        .request-form textarea {
            min-height: 120px;
        }

        .submit-btn {
            background-color: #007bff;
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background-color: #0069d9;
            transform: translateY(-2px);
        }

        .page-title {
            color: #343a40;
            margin-bottom: 25px;
            font-weight: 700;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row">
            <?php include "sidebar.php"; ?>

            <div class="col-md-6">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">
                    <h2 class="page-title">درخواست ارائه پرزنتیشن</h2>

                    <?php if (isset($success_msg)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_msg; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_msg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_msg; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="presentation-request-card">
                        <form method="POST" action="" class="request-form">
                            <div class="form-group">
                                <label for="email_address">آدرس ایمیل نویسنده مقاله</label>
                                <input type="email" class="form-control" id="email_address" name="email_address" required placeholder="example@example.com">
                            </div>

                            <div class="form-group">
                                <label for="custom_message">پیام شخصی (اختیاری)</label>
                                <textarea class="form-control" id="custom_message" name="custom_message" placeholder="می‌توانید پیام شخصی خود را برای نویسنده مقاله بنویسید..."></textarea>
                            </div>

                            <button type="submit" name="send_present_request" class="btn btn-primary submit-btn">
                                ارسال درخواست
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <!-- Sidebar content if needed -->
            </div>
        </div>
    </div>

    <script>
        // Auto-dismiss alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>

</html>