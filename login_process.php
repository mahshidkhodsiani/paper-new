<?php

session_start();
include "config.php";

// این قسمت از کد که باعث ریدایرکت می‌شد، حذف شده است.
// if (isset($_SESSION['user_data'])) {
//     header("Location: profile");
//     exit();
// }

if (isset($_POST['enter'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prevent SQL injection (basic) - توصیه می‌شود از Prepared Statements استفاده کنید
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    // توجه: استفاده از رمز عبور هش نشده (یا هش شده با MD5) بسیار ناامن است.
    // به شدت توصیه می‌شود از password_hash() و password_verify() برای هش کردن و اعتبارسنجی رمز عبور استفاده کنید.
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_data'] = $row;
        header("Location: profile");
        exit();
    } else {
        $_SESSION['login_error'] = "Incorrect email or password.";
        header("Location: login.php");
        exit();
    }
} else {
    // اگر کاربر مستقیماً به login_process.php بدون ارسال فرم دسترسی پیدا کند.
    header("Location: login.php");
    exit();
}
