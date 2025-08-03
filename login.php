<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <?php
    include "config.php";
    include "includes.php";
    ?>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->

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

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }

        .forgot-password {
            font-size: 0.875rem;
            text-align: right;
            margin-top: -8px;
            margin-bottom: 16px;
        }

        .forgot-password-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password-link:hover {
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
                    <h3 class="mb-4 login-title">Sign in</h3>
                    <?php
                    if (isset($_SESSION['login_error'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['login_error'] . '</div>';
                        unset($_SESSION['login_error']);
                    }
                    ?>

                    <div class="text-center mb-3">
                        <div id="g_id_onload" data-client_id="<?php echo GOOGLE_CLIENT_ID; ?>"
                            data-context="signin" data-ux_mode="popup"
                            data-login_uri="<?php echo GOOGLE_REDIRECT_URI; ?>"
                            data-auto_prompt="false">
                        </div>
                        <div class="g_id_signin" data-type="standard" data-shape="pill"
                            data-theme="outline" data-text="signin_with" data-size="large"
                            data-logo_alignment="left">
                        </div>
                    </div>
                    <hr class="my-4">
                    <form method="post" action="login_process.php">
                        <div class="mb-3 text-start">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                autocomplete="username">
                        </div>

                        <div class="mb-0 text-start">
                            <label for="password" class="form-label">Password</label>
                            <div class="password-container">
                                <input type="password" class="form-control" id="password" name="password" required
                                    autocomplete="current-password">
                                <span class="password-toggle" onclick="togglePasswordVisibility()">
                                    <i class="fa-solid fa-eye-slash" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                        </div>

                        <div class="forgot-password mt-2">
                            <a href="forgot_password.php" class="forgot-password-link">Forgot Password?</a>
                        </div>

                        <button type="submit" name="enter" class="btn btn-primary w-100" style="font-weight:600;">Sign
                            in</button>
                        <div class="mt-3 text-center">
                            <span>Don't have an account?</span>
                            <a href="register.php" class="register-link">Register now</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-6 mb-4 mb-lg-0 text-center">
                <h2 class="mt-3" style="color:#6366f1;font-weight:600;">Welcome Back!</h2>
                <p style="color:#555;">Sign in to access your account and explore new features.</p>
                <img src="images/Thesis-amico (1).png" alt="Animated Login" class="login-anim-img">
            </div>

        </div>
    </div>

    <?php include "footer.php"; ?>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>

</html>