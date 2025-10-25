<?php
session_start();
include "config.php";
include "includes.php";

if (isset($_POST['verify_code']) && isset($_POST['code'])) {
    
    $input_code = $_POST['code'];

    // 1. اطمینان از اینکه ایمیل برای ریست پسورد در دیتابیس پیدا شود.
    // نکته: ما در forgot_password_process.php، ایمیل را در سشن ذخیره نکردیم.
    // بهتر است یک فیلد مخفی (hidden field) در فرم verify_code.php، ایمیل را همراه با کد ارسال کند.
    // در اینجا، ما باید یک راهی برای پیدا کردن کاربر بر اساس کد داشته باشیم، 
    // یا ایمیل را از کاربر بگیریم. چون ایمیل برای فرآیند فراموشی رمز حیاتی است، 
    // فرض می‌کنیم که شما در verify_code.php، ایمیل را نیز از کاربر می‌گیرید 
    // یا آن را در یک فیلد پنهان از forgot_password.php منتقل کرده‌اید. 
    
    // اگر در سشن ذخیره شده است:
    if (!isset($_SESSION['email_for_reset'])) {
        // این اتفاق نباید بیفتد مگر اینکه سشن پاک شده باشد
        $_SESSION['reset_error'] = "Authentication error. Please restart the password reset process.";
        header("Location: forgot_password.php");
        exit();
    }
    
    $email = $_SESSION['email_for_reset'];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // 2. بازیابی اطلاعات ریست کد از دیتابیس
    $stmt = $conn->prepare("SELECT reset_code, reset_code_expires_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $conn->close();

    if ($user) {
        $hashed_code = $user['reset_code'];
        $expires_at = $user['reset_code_expires_at'];
        $current_time = date('Y-m-d H:i:s');

        // 3. بررسی انقضای کد
        if ($current_time > $expires_at) {
            $_SESSION['reset_error'] = "The reset code has expired. Please request a new one.";
            header("Location: forgot_password.php"); // بازگشت برای درخواست کد جدید
            exit();
        }

        // 4. مقایسه کد: از password_verify برای مقایسه با هش استفاده می‌کنیم
        if (password_verify($input_code, $hashed_code)) {
            
            // کد معتبر است! حالا کاربر را به صفحه تغییر رمز هدایت می‌کنیم
            // یک پرچم (Flag) در سشن می‌گذاریم تا reset_password.php بفهمد کد تأیید شده است
            $_SESSION['code_verified'] = true; 
            
            // نیازی به پیام موفقیت نیست، مستقیماً به صفحه بعدی می‌رود
            header("Location: reset_password.php");
            exit();
            
        } else {
            // کد اشتباه است
            $_SESSION['reset_error'] = "The code is invalid. Please try again.";
            // در اینجا می‌توانیم ایمیل را دوباره در سشن برای نمایش نگه داریم
            $_SESSION['reset_email'] = $email; 
            header("Location: verify_code.php"); 
            exit();
        }
    } else {
        // ایمیل در دیتابیس پیدا نشد (نباید اتفاق بیفتد)
        $_SESSION['reset_error'] = "System error: User not found.";
        header("Location: forgot_password.php");
        exit();
    }

} else {
    // دسترسی مستقیم
    header("Location: forgot_password.php");
    exit();
}
?>