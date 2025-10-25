<?php
session_start();
include "config.php";
include "includes.php";

// بررسی دسترسی: مطمئن می‌شویم کاربر از طریق مراحل قبلی به اینجا رسیده است
if (!isset($_POST['reset_password'])) {
    header("Location: forgot_password.php");
    exit();
}

// 1. بررسی سشن‌های لازم
if (!isset($_SESSION['code_verified']) || $_SESSION['code_verified'] !== true || !isset($_SESSION['email_for_reset'])) {
    $_SESSION['reset_error'] = "Access denied. Please restart the password reset process.";
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['email_for_reset'];
$new_password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// 2. بررسی تطابق رمز عبور
if ($new_password !== $confirm_password) {
    $_SESSION['reset_error'] = "The new password and confirmation password do not match.";
    header("Location: reset_password.php");
    exit();
}

// 3. بررسی حداقل طول رمز عبور (اختیاری اما توصیه شده)
if (strlen($new_password) < 8) {
    $_SESSION['reset_error'] = "Password must be at least 8 characters long.";
    header("Location: reset_password.php");
    exit();
}

// 4. هش کردن رمز عبور جدید
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// 5. اتصال به پایگاه داده و به‌روزرسانی
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // ثبت خطا در لاگ سرور
    error_log("Database Connection Failed: " . $conn->connect_error);
    $_SESSION['reset_error'] = "A system error occurred. Please try again later.";
    header("Location: reset_password.php");
    exit();
}
$conn->set_charset("utf8mb4");

// 6. به‌روزرسانی رمز عبور و پاکسازی کد بازنشانی
// پاکسازی reset_code و reset_code_expires_at برای جلوگیری از استفاده مجدد
$update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_code_expires_at = NULL, updated_at = NOW() WHERE email = ?");
$update_stmt->bind_param("ss", $hashed_password, $email);

if ($update_stmt->execute()) {
    // 7. موفقیت: پاکسازی سشن و هدایت به صفحه ورود
    session_unset(); // پاک کردن همه متغیرهای سشن
    session_destroy(); // از بین بردن سشن
    
    // می‌توانیم پیام موفقیت را در یک سشن جدید برای صفحه ورود ذخیره کنیم
    session_start();
    $_SESSION['login_success'] = "Your password has been successfully reset. Please log in with your new password.";
    header("Location: login.php");
    exit();
} else {
    // 8. خطا در به‌روزرسانی دیتابیس
    error_log("Database Update Failed: " . $conn->error);
    $_SESSION['reset_error'] = "Error updating password in the database. Please try again.";
    header("Location: reset_password.php");
    exit();
}

$conn->close();

?>