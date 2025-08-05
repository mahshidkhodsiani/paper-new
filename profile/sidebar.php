<?php

// include "config.php";

// منطق مربوط به تغییر عکس پروفایل
if (isset($_POST['update_profile_pic'])) {

    // بررسی اینکه آیا کاربر لاگین کرده است یا خیر
    if (!isset($_SESSION['user_data']['id'])) {
        echo json_encode(['message' => 'Unauthorized access.']);
        exit();
    }



    $newProfilePic = $_POST['update_profile_pic'];
    $userId = $_SESSION['user_data']['id'];

    // آماده‌سازی و اجرای کوئری برای به‌روزرسانی
    $sql = "UPDATE users SET profile_pic = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newProfilePic, $userId);

    if ($stmt->execute()) {
        // به‌روزرسانی متغیر سشن برای نمایش فوری تغییر
        $_SESSION['user_data']['profile_pic'] = $newProfilePic;
        echo json_encode(['message' => 'Profile picture updated successfully!']);
    } else {
        echo json_encode(['message' => 'Error updating profile picture: ' . $conn->error]);
    }
    $stmt->close();
    $conn->close();
    exit(); // مهم: پس از ارسال پاسخ JSON، اجرای اسکریپت متوقف شود
}
?>

<div class="col-md-3">
    <div class="sidebar-content shadow p-3 mb-5 bg-white rounded">
        <div class="text-center mb-4">
            <img decoding="async" width="150" height="150" id="main-profile-pic"
                src="../<?= !empty($_SESSION['user_data']['profile_pic']) ? $_SESSION['user_data']['profile_pic'] : '../images/2.png'; ?>"
                class="img-fluid rounded-circle" alt="profile-pic">
        </div>

        <div class="text-center mb-4">
            <span class="avatar-option">
                <img decoding="async" width="50" height="50" src="../images/2.png"
                    class="img-fluid rounded-circle clickable-avatar" alt="female-avatar" data-path="images/9.png">
            </span>
            <span class="avatar-option">
                <img decoding="async" width="50" height="50" src="../images/10.png"
                    class="img-fluid rounded-circle clickable-avatar" alt="male-avatar" data-path="images/10.png">
            </span>
        </div>

        <div class="text-center mb-4">
            <p class="">
                <i class="far fa-user-circle"></i>
                <?= $_SESSION['user_data']['name'] . " " . $_SESSION['user_data']['family']; ?>
            </p>
        </div>

        <div class="list-group">
            <a class="list-group-item list-group-item-action" href="./">Main</a>
            <a class="list-group-item list-group-item-action" href="settings.php">Settings</a>
            <a class="list-group-item list-group-item-action" href="resume-media.php">Resume And Introduction Media</a>
            <a class="list-group-item list-group-item-action" href="my_presentations.php">My Presentations</a>
            <a class="list-group-item list-group-item-action" href="saved_presentations.php">Saved Presentations</a>
            <a class="list-group-item list-group-item-action" href="saved_peoples.php">Connections</a>
            <a class="list-group-item list-group-item-action" href="messages.php">Messages</a>
            <a class="list-group-item list-group-item-action" href="my_requests.php">My requests</a>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // کدهای قبلی شما برای فعال‌سازی لینک‌های سایدبار
        var currentPath = window.location.pathname;
        var fileName = currentPath.split('/').pop();

        $('.list-group-item-action').removeClass('active');
        $('.list-group-item-action').each(function() {
            var linkHref = $(this).attr('href');
            var linkFileName = linkHref.split('/').pop();

            if (linkFileName === fileName) {
                $(this).addClass('active');
            }
        });

        // کد جدید برای تغییر آواتار با AJAX
        $('.clickable-avatar').on('click', function() {
            var newProfilePicPath = $(this).data('path');
            var fullPath = '../' + newProfilePicPath;

            $('#main-profile-pic').attr('src', fullPath);

            $.ajax({
                // در اینجا، آدرس فایل فعلی (sidebar.php) را به عنوان URL مشخص می‌کنیم
                url: 'sidebar.php',
                type: 'POST',
                data: {
                    update_profile_pic: newProfilePicPath // نام پارامتر تغییر کرد
                },
                success: function(response) {
                    console.log('Profile picture updated successfully!');
                },
                error: function(xhr, status, error) {
                    console.error('An error occurred:', error);
                }
            });
        });
    });
</script>

<style>
    .clickable-avatar {
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s ease-in-out;
    }

    .clickable-avatar:hover {
        border-color: #007bff;
    }
</style>