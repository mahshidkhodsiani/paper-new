<?php

session_start();
include "config.php";

$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی خطای اتصال به دیتابیس
if ($conn->connect_error) {
    die("خطا در اتصال به دیتابیس: " . $conn->connect_error);
}

// بررسی اینکه آیا اطلاعات فرم با متد POST ارسال شده است یا خیر
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // استفاده از Prepared Statement برای جلوگیری از SQL Injection
        $sql = "SELECT id, name, family, email, password, status, profile_pic FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stored_hashed_password = $row['password'];

            // استفاده از password_verify() برای تأیید رمز عبور
            if (password_verify($password, $stored_hashed_password)) {
                // رمز عبور صحیح است.
                if ($row['status'] == 0) {
                    $sql_reactivate = "UPDATE users SET status = 1 WHERE id = ?";
                    $stmt_reactivate = $conn->prepare($sql_reactivate);
                    $stmt_reactivate->bind_param("i", $row['id']);
                    $stmt_reactivate->execute();
                    $stmt_reactivate->close();
                    $_SESSION['login_message'] = "Your account has been reactivated successfully!";
                    $_SESSION['login_message_type'] = "success";
                }

                unset($row['password']);
                $_SESSION['user_data'] = $row;
                $_SESSION['logged_in'] = true;

                // هدایت کاربر به صفحه پروفایل
                header("Location: profile");
                exit();
            } else {
                // رمز عبور اشتباه است.
                $_SESSION['login_error'] = "ایمیل یا رمز عبور اشتباه است.";
            }
        } else {
            // ایمیل در پایگاه داده یافت نشد.
            $_SESSION['login_error'] = "ایمیل یا رمز عبور اشتباه است.";
        }
    } else {
        // اطلاعات کامل نیست.
        $_SESSION['login_error'] = "لطفاً تمامی فیلدها را پر کنید.";
    }
} else {
    // اگر درخواست با متد POST ارسال نشده باشد
    $_SESSION['login_error'] = "درخواست نامعتبر.";
}

// در صورت بروز هر گونه خطا، به صفحه ورود بازگردان
header("Location: login.php");
exit();
