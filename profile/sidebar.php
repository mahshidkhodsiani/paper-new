<?php

// این بخش رو قبلاً داشتید
// include "config.php"; // مطمئن بشید که این خط uncomment شده و فایل config.php وجود داره و اطلاعات اتصال به دیتابیس رو شامل میشه.

// منطق مربوط به تغییر عکس پروفایل
if (isset($_POST['update_profile_pic'])) {

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


<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->


<div class="col-md-3">
    <div class="sidebar-content shadow p-3 mb-5 bg-white rounded position-relative">
        <?php if (!empty($_SESSION['user_data']['intro_video_path'])): ?>
            <div class="play-icon-overlay" data-video-path="<?= htmlspecialchars($_SESSION['user_data']['intro_video_path']); ?>">
                <i class="fas fa-play play-icon"></i>
            </div>
        <?php endif; ?>

        <div class="text-center mb-4 profile-container">
            <img decoding="async" width="150" height="150" id="main-profile-pic"
                src="../<?= !empty($_SESSION['user_data']['profile_pic']) ? $_SESSION['user_data']['profile_pic'] : '../images/2.png'; ?>"
                class="img-fluid rounded-circle" alt="profile-pic">
        </div>



        <div class="text-center mb-4">
            <p class="">
                <i class="far fa-user-circle"></i>
                <?= htmlspecialchars($_SESSION['user_data']['name'] . " " . $_SESSION['user_data']['family']); ?>
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
            <a class="list-group-item list-group-item-action" href="#">My Labs</a>
        </div>
    </div>
</div>

<div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalLabel">Introduction Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <video id="introVideoPlayer" width="100%" controls>

                    <source src="" type="video/mp4">
                    مرورگر شما از تگ ویدیو پشتیبانی نمی‌کند.

                </video>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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



        // کد جدید برای پخش ویدیو با کلیک روی آیکون پلی
        $('.play-icon-overlay').on('click', function() {
            var videoPath = $(this).data('video-path');

            if (videoPath) {
                var fullPath =  videoPath;
                var videoPlayer = $('#introVideoPlayer');

                // مسیر ویدیو را مستقیماً به تگ <video> اضافه می‌کنیم
                videoPlayer.attr('src', fullPath);

                // بارگذاری مجدد ویدیو
                videoPlayer[0].load();

                // نمایش مودال
                $('#videoModal').modal('show');

                // بلافاصله بعد از نمایش مودال، پخش را شروع می‌کنیم
                videoPlayer[0].play();
            } else {
                console.log('مسیر ویدیو یافت نشد!');
            }
        });

        // وقتی مودال بسته شد، ویدیو رو متوقف می‌کنیم تا صدا ادامه پیدا نکنه
        $('#videoModal').on('hidden.bs.modal', function() {
            var videoPlayer = $('#introVideoPlayer');
            videoPlayer[0].pause();
            videoPlayer.attr('src', ''); // پاک کردن مسیر ویدیو
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

    .sidebar-content {
        position: relative;
    }

    .profile-container {
        display: inline-block;
    }

    .play-icon-overlay {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        color: #fff;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .play-icon {
        font-size: 20px;
    }

    .play-icon-overlay:hover {
        background-color: rgba(0, 0, 0, 0.7);
    }
</style>