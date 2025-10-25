<?php
session_start();
include "config.php";
include "includes.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/x-icon" href="images/logo.png">


    <title>Forgot Password</title>

    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            min-height: 100vh;
        }

        .login-container {
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-form-box {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            padding: 40px 32px;
            transition: box-shadow 0.3s;
        }

        .login-form-box:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
        }

        .form-label {
            font-weight: 500;
        }

        .login-title {
            font-weight: 700;
            color: #3730a3;
        }

        .back-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

           .btn-primary,
        .btn-info {
            background-color: #4242f0;
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container login-container">
        <div class="row w-100 align-items-center justify-content-center">
            <div class="col-lg-5">
                <div class="login-form-box">
                    <h3 class="mb-4 login-title">Forgot Password</h3>
                    <p class="mb-4">Enter your email address and we'll send you a password reset code.</p>

                    <?php
                    // نمایش پیام موفقیت یا خطا
                    if (isset($_SESSION['reset_error'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['reset_error'] . '</div>';
                        unset($_SESSION['reset_error']);
                    }
                    if (isset($_SESSION['reset_success'])) {
                        echo '<div class="alert alert-success">' . $_SESSION['reset_success'] . '</div>';
                        unset($_SESSION['reset_success']);
                    }
                    ?>

                    <form method="post" action="forgot_password_process.php">
                        <div class="mb-3 text-start">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                        </div>
                        <button type="submit" name="send_code" class="btn btn-primary w-100" style="font-weight:600;">Send Reset Code</button>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="login.php" class="back-link">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
</body>

</html>