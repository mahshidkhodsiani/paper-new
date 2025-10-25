<?php
// اطمینان از راه‌اندازی سشن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// فرض بر این است که $conn و $user در فایل اصلی (مثلاً profile.php) تعریف شده‌اند.
// بررسی وضعیت اتصال بین کاربر فعلی و کاربر پروفایل
$button_text = 'Connect';
$button_class = 'btn-success';
$button_disabled = '';
$show_connect_button = false;

// فقط در صورتی که کاربر لاگین کرده و پروفایل متعلق به خودش نیست، دکمه‌ها نمایش داده می‌شوند
if (isset($_SESSION['user_data']['id']) && isset($user['id']) && $_SESSION['user_data']['id'] != $user['id']) {
    $loggedInUserId = $_SESSION['user_data']['id'];
    $profileUserId = $user['id'];
    $show_connect_button = true;

    $stmt_check_connection = $conn->prepare("SELECT status, sender_id FROM connections WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
    if ($stmt_check_connection) {
        $stmt_check_connection->bind_param("iiii", $loggedInUserId, $profileUserId, $profileUserId, $loggedInUserId);
        $stmt_check_connection->execute();
        $result = $stmt_check_connection->get_result();

        if ($result->num_rows > 0) {
            $connection_row = $result->fetch_assoc();
            if ($connection_row['status'] == 'pending') {
                if ($connection_row['sender_id'] == $loggedInUserId) {
                    $button_text = 'Request Sent';
                    $button_class = 'btn-secondary';
                } else {
                    $button_text = 'Pending Request';
                    $button_class = 'btn-warning';
                }
                $button_disabled = 'disabled';
            } elseif ($connection_row['status'] == 'accepted') {
                $button_text = 'Connected';
                $button_class = 'btn-primary';
                $button_disabled = 'disabled';
            }
        }
        $stmt_check_connection->close();
    }
}
?>

<div class="col-md-3">
    <div class="sidebar-content shadow p-3 mb-5 bg-white rounded">
        <div class="text-center mb-4">
            <div class="profile-pic-container-sidebar">
                <img decoding="async" width="150" height="150"
                    src="../<?= !empty($user['profile_pic']) ? $user['profile_pic'] : '../images/2.png'; ?>"
                    class="img-fluid rounded-circle" alt="profile-pic">

                <?php if (!empty($user['intro_video_path'])): ?>
                    <div class="play-icon-overlay-sidebar" data-video-path="../<?= safe($user['intro_video_path']) ?>" data-bs-toggle="modal" data-bs-target="#videoModal">
                        <i class="fas fa-play-circle play-icon"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mb-4">
            <p class="">
                <i class="far fa-user-circle"></i>
                <?= safe($user['name'] . " " . $user['family']); ?>
            </p>
        </div>

        <div class="list-group">

            <button type="button" class="btn <?= safe($button_class) ?> w-100 mt-3 connect-btn" data-user-id="<?= safe($profileUserId) ?>" <?= safe($button_disabled) ?>>
                <i class="fas fa-user-plus me-2"></i> <?= safe($button_text) ?>
            </button>

            <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#messageModal">
                <i class="fas fa-paper-plane me-2"></i> Send Message
            </button>

        </div>

        <br>


        <?php
        $introVideoPath = htmlspecialchars($user['intro_video_path'] ?? '');
        $hasVideo = !empty($introVideoPath) && file_exists($introVideoPath);
        ?>
        <div class="mb-4">
            <h5 class="profile-section-title"><i class="fas fa-briefcase me-2"></i>Education and Work Information</h5>
            <?php if (!empty($user_universities_array)) : ?>
                <h6><i class="fas fa-university me-2 text-primary"></i>Universities:</h6>
                <ul>
                    <?php foreach ($user_universities_array as $uni) : ?>
                        <li><?= htmlspecialchars(trim($uni)) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if (!empty($user_educations_array)) : ?>
                <h6><i class="fas fa-graduation-cap me-2 text-primary"></i>Education:</h6>
                <ul>
                    <?php foreach ($user_educations_array as $edu) : ?>
                        <li><?= htmlspecialchars(trim($edu)) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if (!empty($user['workplace'])) : ?>
                <p><i class="fas fa-building me-2 text-primary"></i>Workplace: <?= htmlspecialchars($user['workplace']) ?></p>
            <?php endif; ?>
        </div>
        <?php if (!empty($user['biography'])) : ?>
            <div class="mb-4">
                <h5 class="profile-section-title"><i class="fas fa-info-circle me-2"></i>About Me</h5>
                <p class="text-justify" style="font-size: 10px;">
                    <?= nl2br(htmlspecialchars($user['biography'])) ?>
                </p>
            </div>
        <?php endif; ?>
        <?php if (!empty($user['linkedin_url']) || !empty($user['x_url']) || !empty($user['google_scholar_url']) || !empty($user['github_url']) || !empty($user['website_url'])) : ?>
            <div class="mb-4">
                <h5 class="profile-section-title"><i class="fas fa-link me-2"></i>Social and Web Links</h5>
                <div class="social-links">
                    <?php if (!empty($user['linkedin_url'])) : ?>
                        <a href="<?= htmlspecialchars($user['linkedin_url']) ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <?php endif; ?>
                   
                    
                    <?php if (!empty($user['google_scholar_url'])) : ?>
                        <a href="<?= htmlspecialchars($user['google_scholar_url']) ?>" target="_blank" title="Google Scholar"><i class="fas fa-graduation-cap"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($user['github_url'])) : ?>
                        <a href="<?= htmlspecialchars($user['github_url']) ?>" target="_blank" title="GitHub"><i class="fab fa-github"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($user['website_url'])) : ?>
                        <a href="<?= htmlspecialchars($user['website_url']) ?>" target="_blank" title="Website"><i class="fas fa-globe"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">New Message to <?= safe($user['name'] . ' ' . $user['family']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="messageForm">
                    <input type="hidden" name="receiver_id" value="<?= safe($user['id']) ?>">
                    <div class="mb-3">
                        <label for="messageSubject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="messageSubject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="messageContent" class="form-label">Message</label>
                        <textarea class="form-control" id="messageContent" name="content" rows="5" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendMessageBtn">Send</button>
            </div>
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
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .sidebar-content {
        /* استایل‌های موجود */
    }

    .profile-pic-container-sidebar {
        position: relative;
        display: inline-block;
    }

    .play-icon-overlay-sidebar {
        position: absolute;
        top: 5px;
        right: 5px;
        cursor: pointer;
        color: #fff;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .play-icon {
        font-size: 20px;
    }

    .play-icon-overlay-sidebar:hover {
        background-color: rgba(0, 0, 0, 0.7);
    }


    .sidebar-content p {
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: anywhere;
        text-align: center;
        display: inline-block;
        max-width: 100%;
    }
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // تابع نمایش پیام پاپ‌آپ که در فایل اصلی تعریف شده است
    function showMessage(message, type = 'info') {
        const msgContainer = document.getElementById('message-container');
        if (!msgContainer) {
            console.warn("Message container not found. Please add a div with id='message-container' to your page.");
            return;
        }

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        msgContainer.innerHTML = '';
        msgContainer.appendChild(alertDiv);
        msgContainer.style.display = 'block';

        setTimeout(() => {
            $(alertDiv).alert('close');
        }, 5000);
    }

    $(document).ready(function() {
        // جاوااسکریپت برای دکمه ارسال پیام
        $('#sendMessageBtn').click(function() {
            const formData = $('#messageForm').serialize();
            $.ajax({
                url: 'send_message.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#sendMessageBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
                },
                success: function(response) {
                    if (response.success) {
                        $('#messageModal').modal('hide');
                        showMessage(response.message, 'success');
                        $('#messageForm')[0].reset();
                    } else {
                        showMessage('Error: ' + response.message, 'danger');
                    }
                },
                error: function() {
                    showMessage('An error occurred while sending the message.', 'danger');
                },
                complete: function() {
                    $('#sendMessageBtn').prop('disabled', false).text('Send');
                }
            });
        });
    });

    // جاوااسکریپت برای دکمه ارسال درخواست اتصال
    document.addEventListener('DOMContentLoaded', function() {
        const connectButtons = document.querySelectorAll('.connect-btn');
        connectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const receiverId = this.dataset.userId;
                const clickedButton = this;

                if (clickedButton.disabled) {
                    return;
                }

                const originalText = clickedButton.innerHTML;
                const originalClass = clickedButton.className;

                clickedButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                clickedButton.disabled = true;
                clickedButton.classList.remove('btn-success', 'btn-secondary', 'btn-warning');
                clickedButton.classList.add('btn-info');

                fetch('../profile/handle_connection.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'receiver_id=' + receiverId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            clickedButton.innerHTML = '<i class="fas fa-check"></i> Request Sent';
                            clickedButton.classList.remove('btn-info');
                            clickedButton.classList.add('btn-secondary');
                            showMessage(data.message, 'success');
                        } else {
                            clickedButton.innerHTML = originalText;
                            clickedButton.className = originalClass;
                            clickedButton.disabled = false;
                            console.error('Error:', data.message);
                            showMessage('Failed to send connection request: ' + data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        clickedButton.innerHTML = originalText;
                        clickedButton.className = originalClass;
                        clickedButton.disabled = false;
                        showMessage('Network error or server issue. Please try again.', 'danger');
                    });
            });
        });

        // جاوااسکریپت برای مودال ویدیو
        const videoModal = document.getElementById('videoModal');
        const videoPlayer = document.getElementById('introVideoPlayer');

        document.querySelectorAll('.play-icon-overlay-sidebar').forEach(item => {
            item.addEventListener('click', function() {
                const videoPath = this.getAttribute('data-video-path');
                if (videoPath) {
                    videoPlayer.src = videoPath;
                    videoModal.addEventListener('shown.bs.modal', function() {
                        videoPlayer.play();
                    }, {
                        once: true
                    });
                }
            });
        });

        videoModal.addEventListener('hide.bs.modal', function() {
            videoPlayer.pause();
            videoPlayer.src = '';
        });
    });
</script>