<?php

session_start();
include "config.php";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['enter'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ۱. از Prepared Statement برای جلوگیری از SQL Injection استفاده کنید.
    $sql = "SELECT id, name, family, email, password, status, profile_pic FROM users WHERE email = ?";
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

            // --- بخش جدید: بررسی و فعال‌سازی مجدد اکانت در صورت نیاز ---
            if ($row['status'] == 0) {
                $sql_reactivate = "UPDATE users SET status = 1 WHERE id = ?";
                $stmt_reactivate = $conn->prepare($sql_reactivate);
                $stmt_reactivate->bind_param("i", $row['id']);
                $stmt_reactivate->execute();
                $stmt_reactivate->close();

                // پیام موفقیت برای کاربر تنظیم شود.
                $_SESSION['login_message'] = "Your account has been reactivated successfully!";
                $_SESSION['login_message_type'] = "success";
            }

            // اطلاعات کاربری را بدون رمز عبور در سشن ذخیره کنید.
            unset($row['password']);
            $_SESSION['user_data'] = $row;

            // کاربر را به صفحه پروفایل هدایت کنید.
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
