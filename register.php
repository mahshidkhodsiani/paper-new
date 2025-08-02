<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <?php
    include "config.php";
    include "includes.php";
    ?>

    <script src="https://accounts.google.com/gsi/client" async defer></script>

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

        .login-anim-img {
            max-width: 90%;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.09);
            margin-bottom: 16px;
            animation: float 2.5s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        .form-label {
            font-weight: 500;
        }

        .login-title {
            font-weight: 700;
            color: #3730a3;
        }

        .register-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <?php include "header.php"; ?>

    <div class="container login-container">
        <div class="row w-100 align-items-center justify-content-center">



            <div class="col-lg-5">
                <div class="login-form-box">
                    <h3 class="mb-4 login-title">Register</h3>
                    <?php
                    session_start();
                    if (isset($_SESSION['registration_error'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['registration_error'] . '</div>';
                        unset($_SESSION['registration_error']);
                    }
                    if (isset($_SESSION['registration_success'])) {
                        echo '<div class="alert alert-success">' . $_SESSION['registration_success'] . '</div>';
                        unset($_SESSION['registration_success']);
                    }
                    ?>

                    <div class="mt-4 mb-4 text-center">
                        <div id="g_id_onload"
                            data-client_id="<?php echo GOOGLE_CLIENT_ID; ?>"
                            data-context="signin"
                            data-ux_mode="popup"
                            data-login_uri="<?php echo GOOGLE_REDIRECT_URI; ?>"
                            data-auto_prompt="false">
                        </div>
                        <div class="g_id_signin"
                            data-type="standard"
                            data-shape="pill"
                            data-theme="outline"
                            data-text="signin_with"
                            data-size="large"
                            data-logo_alignment="left">
                        </div>
                        <hr class="my-4">
                    </div>

                    <form method="post" action="register_process.php">
                        <div class="mb-3 text-start">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required autocomplete="given-name">
                        </div>
                        <div class="mb-3 text-start">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required autocomplete="family-name">
                        </div>
                        <div class="mb-3 text-start">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                        </div>
                        <div class="mb-3 text-start">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                        </div>
                        <div class="mb-3 text-start">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100" style="font-weight:600;">Register</button>
                        <div class="mt-3 text-center">
                            <span>Already have an account?</span>
                            <a href="login.php" class="register-link">Login now</a>
                        </div>
                    </form>
                </div>
            </div>


            <div class="col-lg-6 mb-4 mb-lg-0 text-center">
                <img src="images/Thesis-pana.png" alt="Animated Register" class="login-anim-img">
                <h2 class="mt-3" style="color:#6366f1;font-weight:600;">Join Us!</h2>
                <p style="color:#555;">Create your account and start your journey.</p>
            </div>

        </div>
    </div>

    <?php include "footer.php"; ?>

</body>

</html>