<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>paperet</title>

    <?php
    // مطمئن شوید این فایل‌ها در مسیر صحیح قرار دارند
    include "config.php";
    include "includes.php";
    ?>

    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .btn-outline-info {
            color: #17a2b8;
            border-color: #17a2b8;
            width: 300px;
        }

        .btn-outline-info:hover {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container">
        <div class="row mt-5 align-items-center">
            <div class="col-md-6">
                <img src="images/Metrics-pana.png" class="img-fluid">
            </div>



            <div class="col-md-6 text-center">
                <h2>Your Journey Starts Right Here!</h2>
                <p>Showcase Yourself To The World</p>

                <a href="profile" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-id-card-alt"></i> Your Profile
                </a>
                <br>

                <button class="btn btn-outline-info rounded mb-3" id="google-signin-btn">
                    <i class="fab fa-google"></i> Sign in/up with Google
                </button>
                <br>

                <a href="" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-file-contract"></i> Term and Conditions
                </a>
                <br>

                <a href="profile/send_email" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-microphone"></i> Request To Present
                </a>
                <br>

                <a href="" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-trophy"></i> Host a competition
                </a>
                <br>

                <a href="" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-flask"></i> Build your Lab Page
                </a>
            </div>


        </div>

        <br />

        <div class="row align-items-center">
            <div class="col-md-6 text-center ">
                <h6>Paperet</h6>
                <h2>Take a peek into who we are and what we do</h2>
                <p>We believe in sharing our journey with you. Learn more about our mission, values, and the passion behind our work.</p>
            </div>
            <div class="col-md-6">
                <img src="images/Webinar-pana.png" class="img-fluid">
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <script>
        function handleCredentialResponse(response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo GOOGLE_REDIRECT_URI; ?>';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'credential';
            input.value = response.credential;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }

        window.onload = function() {
            google.accounts.id.initialize({
                client_id: "<?php echo GOOGLE_CLIENT_ID; ?>",
                callback: handleCredentialResponse
            });

            const signinButton = document.getElementById('google-signin-btn');
            signinButton.onclick = () => {
                google.accounts.id.prompt();
            };
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>