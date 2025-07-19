<?php
session_start();
if(!isset($_SESSION['user_data'])) {
    header("Location: ../login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحه با سایدبار و محتوا</title>

    <?php include "../includes.php"; ?>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <?php include "header.php"; ?>

    <div class="container">
        <div class="row">

            <?php include "sidebar.php"; ?>

            <div class="col-md-6">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">
                    <h4>محتوای اصلی</h4>
                    <p>اینجا محتوای اصلی صفحه قرار می‌گیرد. این ستون برای نمایش اطلاعات اصلی و بزرگ‌تر است.</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="optional-sidebar shadow-sm p-3 mb-5 bg-white rounded">
                    <h4>ستون اختیاری</h4>
                    <p>این ستون همیشه نمایش داده می‌شود.</p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>