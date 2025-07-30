<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>paperet</title>

    <?php
    // این خط رو اضافه کن تا مطمئن بشی config.php لود میشه
    include "config.php";
    include "includes.php";
    ?>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>

<body>

    <?php include "header.php"; ?>

    <div class="container">
        <div class="row">

            <div class="col-md-6">
                <img src="images/2.jpg" class="img-fluid">
            </div>

            <div class="col-md-6 text-center">
                <h2>Your Journey Starts Right Here!</h2>
                <p>Showcase Yourself To The World</p>

                <a href="profile" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-user-circle"></i> Your Profile
                </a>
                <br>

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


                <br>

                <a href="profile/send_email" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-microphone"></i> Request To Present
                </a>

                <br>


                <!-- <button class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-user-plus"></i> Invite
                </button>
                <br> -->
                <button class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-trophy"></i> Host a Competition
                </button>
            </div>

        </div>

        <br />

        <div class="row">

            <div class="col-md-6 text-center">
                <h6>Paperet</h6>
                <h2>Take a peek into who we are and what we do</h2>
                <p>We believe in sharing our journey with you. Learn more about our mission, values, and the passion behind our work.</p>
            </div>

            <div class="col-md-6">
                <img src="images/3.png" class="img-fluid">
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>

</body>

</html>