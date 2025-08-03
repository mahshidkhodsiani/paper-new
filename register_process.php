<?php
session_start();
include "config.php";

// ایجاد یک اتصال جدید به پایگاه داده
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = $_POST['first_name'];
    $family = $_POST['last_name'];
    $profile_img = "images/2.png";

    if ($password !== $confirm_password) {
        $_SESSION['registration_error'] = 'Your passwords do not match!';
        header('Location: register.php');
        exit();
    }

    // بررسی وجود ایمیل با استفاده از Prepared Statement
    $check_email_sql = "SELECT email FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['registration_error'] = 'This email is already registered.';
        header('Location: register.php');
        exit();
    }

    // هش کردن رمز عبور برای امنیت بیشتر
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // درج کاربر جدید با استفاده از Prepared Statement
    $insert_sql = "INSERT INTO users (name, family, email, password, profile_pic) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sssss", $name, $family, $email, $hashed_password, $profile_img);

    if ($stmt->execute()) {
        // --- کد ریدایرکت تأخیری اصلاح شده ---
        echo '<!DOCTYPE html>
              <html lang="en">
              <head>
                  <meta charset="UTF-8">
                  <title>Registration Successful</title>
                  <meta http-equiv="refresh" content="2;url=login.php">
                  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
                  <style>
                      body { background: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; }
                      .success-box { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.09); padding: 40px 32px; text-align: center; }
                  </style>
              </head>
              <body>
                  <div class="success-box">
                      <h2 class="text-success mb-3">Registration Successful!</h2>
                      <p>Your registration was successful. You can now log in.</p>
                      <a href="login.php" class="btn btn-primary mt-3">Go to Login</a>
                  </div>
              </body>
              </html>';
        exit();
    } else {
        $_SESSION['registration_error'] = "Registration error: " . $stmt->error;
        header('Location: register.php');
        exit();
    }
} else {
    header('Location: register.php');
    exit();
}
