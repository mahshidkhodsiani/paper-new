<?php
session_start();
include "config.php";

if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = $_POST['first_name'];
    $family = $_POST['last_name'];
    $profile_img = "images/2.png";

    if ($password == $confirm_password) {
        // Check if email is already registered
        $check_email_sql = "SELECT email FROM users WHERE email = '$email'";
        $result = $conn->query($check_email_sql);
        if ($result->num_rows > 0) {
            $_SESSION['registration_error'] = 'This email is already registered.';
            header('Location: register.php');
            exit();
        }

        $sql = "INSERT INTO users (name, family, email, password, profile_pic) 
                VALUES ('$name', '$family', '$email', '$password', '$profile_img')";

        if ($conn->query($sql) === TRUE) {
            // Successful registration
            $_SESSION['registration_success'] = 'Your registration was successful. You can now log in.';
            header('refresh:2;url=login.php'); // Redirect to login after 2 seconds
            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Registration Successful</title>
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
            $_SESSION['registration_error'] = "Registration error: " . $conn->error;
            header('Location: register.php');
            exit();
        }
    } else {
        $_SESSION['registration_error'] = 'Your passwords do not match!';
        header('Location: register.php');
        exit();
    }
} else {
    header('Location: register.php');
    exit();
}