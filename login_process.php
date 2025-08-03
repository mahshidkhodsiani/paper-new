<?php

session_start();
include "config.php";

// ایجاد یک اتصال جدید به پایگاه داده
// فرض بر این است که متغیرهای اتصال ($servername, $username, $password, $dbname) در config.php تعریف شده‌اند.
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['enter'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ۱. از Prepared Statement برای جلوگیری از SQL Injection استفاده کنید.
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_hashed_password = $row['password'];

        // ۲. از password_verify() برای تأیید رمز عبور هش شده استفاده کنید.
        if (password_verify($password, $stored_hashed_password)) {
            // رمز عبور صحیح است.
            unset($row['password']); // برای امنیت، رمز عبور را از سشن حذف کنید.
            $_SESSION['user_data'] = $row;
            header("Location: profile");
            exit();
        } else {
            // رمز عبور اشتباه است.
            $_SESSION['login_error'] = "Incorrect email or password.";
            header("Location: login.php");
            exit();
        }
    } else {
        // ایمیل در پایگاه داده یافت نشد.
        $_SESSION['login_error'] = "Incorrect email or password.";
        header("Location: login.php");
        exit();
    }
} else {
    // اگر کاربر مستقیماً به login_process.php بدون ارسال فرم دسترسی پیدا کند.
    header("Location: login.php");
    exit();
}
