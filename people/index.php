<?php
session_start();
include(__DIR__ . '/../config.php');

if (!($conn instanceof mysqli) || $conn->connect_error) {
    die("<div class='alert alert-danger container mt-5'>System temporarily unavailable. Please try again later. (DB Connection Error)</div>");
}

$current_user_id = null;
if (isset($_SESSION['user_data']['id'])) {
    $current_user_id = $_SESSION['user_data']['id'];
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>People - User List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <?php include "../includes.php"; ?>

    <style>
        body {
            background-color: #f0f2f5;
        }

        .user-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            text-align: center;
            position: relative;
        }

        .cover-photo {
            width: 100%;
            height: 100px;
            background-color: #ccc;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .profile-pic-container {
            position: absolute;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            width: 100px;
            height: 100px;
        }

        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
        }

        .card-body-custom {
            padding: 15px;
            padding-top: 55px;
        }

        .card-title-custom {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .icon-text {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #555;
        }

        .icon-text svg {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            fill: #54595F;
        }

        .btn-custom {
            width: 80%;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .play-icon-overlay {
            position: absolute;
            top: 0;
            right: 0;
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

        .play-icon-overlay:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }
    </style>
</head>

<body>

    <?php include 'header.php'; ?>

    <div class="container mt-3" id="message-container" style="display: none;">
        <div class="alert" role="alert" id="message-alert"></div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <?php
            try {
                // اضافه کردن شرط WHERE status = 1 به کوئری
                $sql = "SELECT id, name, family, education, university, profile_pic, cover_photo, intro_video_path FROM users WHERE status = 1";
                $params = [];
                $types = "";

                // اگر کاربر وارد شده باشد، خودش را از لیست حذف می‌کنیم.
                if ($current_user_id !== null) {
                    $sql .= " AND id != ?";
                    $params[] = $current_user_id;
                    $types .= "i";
                }

                $stmt = $conn->prepare($sql);
                if ($current_user_id !== null) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $target_user_id = $row["id"];
                        $fullName = htmlspecialchars($row["name"] . " " . $row["family"]);
                        $education = htmlspecialchars($row["education"] ?? 'نامشخص');
                        $university = htmlspecialchars($row["university"] ?? 'نامشخص');
                        $profilePic = htmlspecialchars($row["profile_pic"] ?? 'https://via.placeholder.com/100');
                        $coverPhoto = htmlspecialchars($row["cover_photo"] ?? 'https://via.placeholder.com/400x150/f0f2f5?text=Cover+Photo');
                        $introVideoPath = htmlspecialchars($row["intro_video_path"] ?? '');
                        $profileLink = "profile.php?id=" . (int)$target_user_id;

                        $button_text = 'Connect';
                        $button_class = 'btn-outline-primary';
                        $button_disabled = '';
                        $show_connect_button = true;

                        if ($current_user_id !== null) {
                            $conn_stmt = $conn->prepare("SELECT status, sender_id FROM connections WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
                            $conn_stmt->bind_param("iiii", $current_user_id, $target_user_id, $target_user_id, $current_user_id);
                            $conn_stmt->execute();
                            $conn_result = $conn_stmt->get_result();

                            if ($conn_result->num_rows > 0) {
                                $conn_row = $conn_result->fetch_assoc();
                                $connection_status = $conn_row['status'];
                                if ($connection_status == 'pending') {
                                    if ($conn_row['sender_id'] == $current_user_id) {
                                        $button_text = 'Request Sent';
                                        $button_class = 'btn-secondary';
                                        $button_disabled = 'disabled';
                                    } else {
                                        $button_text = 'Pending Request';
                                        $button_class = 'btn-warning';
                                        $button_disabled = 'disabled';
                                    }
                                } elseif ($connection_status == 'accepted') {
                                    $button_text = 'Connected';
                                    $button_class = 'btn-success';
                                    $button_disabled = 'disabled';
                                }
                            }
                            $conn_stmt->close();
                        }
            ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="user-card">
                                <div class="cover-photo" style="background-image: url('../<?= $coverPhoto ?>');"></div>

                                <div class="profile-pic-container">
                                    <img src="../<?= $profilePic ?>" class="profile-picture" alt="Profile Picture">
                                    <?php if (!empty($introVideoPath)) : ?>
                                        <div class="play-icon-overlay" data-video-path="<?= $introVideoPath ?>" data-bs-toggle="modal" data-bs-target="#videoModal">
                                            <i class="fas fa-play-circle play-icon"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body-custom">
                                    <h5 class="card-title-custom"><?= $fullName ?></h5>
                                    <div class="icon-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#54595F" viewBox="0 0 50 50" overflow="inherit">
                                            <path d="M30 8v33h-24v-33h24m4-4h-32v42h32v-42zm-25 8h18v4h-18zm0 7h18v4h-18zm0 7h18v4h-18zm0 7h18v4h-18zm31-21h8v28h-8zm4.006-11c-2.194 0-4.006 1.765-4.006 3.937v4.063h8v-4.063c0-2.172-1.809-3.937-3.994-3.937zm-4.068 42l4.041 6.387 4.021-6.387z"></path>
                                        </svg>
                                        <span>Education: <?= $education ?></span>
                                    </div>
                                    <div class="icon-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#54595F" viewBox="0 0 50 50" overflow="inherit">
                                            <path d="M24.999 27.381c-5.406 0-9.999 1.572-12.999 4.036v4.583h26v-4.583c-3-2.464-7.594-4.036-13.001-4.036zm23.871-2.352l-23.934-11.029-23.924 11.029 3.988 1.825v2.807c-1 .207-1.003.731-1.003 1.354 0 .368.122.799.354 1.057l-1.368 2.928h4.88l-1.356-2.93c.228-.258.415-.638.415-1.006 0-.622-.922-1.197-.922-1.404v-2.337l5 2.246v-.199c3-2.609 8.271-4.265 13.998-4.265 5.729 0 11.002 1.656 14.002 4.265v.199l9.87-4.54z"></path>
                                        </svg>
                                        <span>University: <?= $university ?></span>
                                    </div>


                                    <button type="button" class="btn <?= $button_class ?> btn-sm btn-custom connect-btn" data-user-id="<?= $target_user_id ?>" <?= $button_disabled ?>>
                                        <i class="fas fa-user-plus"></i> <?= $button_text ?>
                                    </button>


                                    <a href="<?= $profileLink ?>" class="btn btn-primary btn-sm btn-custom"><i class="fas fa-user-graduate"></i> View Profile</a>
                                </div>
                            </div>
                        </div>
            <?php
                    }
                } else {
                    echo "<div class='col-12 text-center'><p>No users found.</p></div>";
                }
                $stmt->close();
            } catch (Exception $e) {
                error_log("Query error in people/index.php: " . $e->getMessage());
                echo "<div class='col-12 alert alert-danger'>Error loading user data. Please try again later.</div>";
            } finally {
                if ($conn instanceof mysqli) {
                    $conn->close();
                }
            }
            ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
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
                    clickedButton.classList.remove('btn-outline-primary', 'btn-secondary', 'btn-warning', 'btn-success');
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

                                // نمایش پیام موفقیت
                                const messageAlert = document.getElementById('message-alert');
                                if (messageAlert) {
                                    messageAlert.textContent = data.message;
                                    messageAlert.classList.add('alert-success');
                                    document.getElementById('message-container').style.display = 'block';
                                    setTimeout(() => {
                                        document.getElementById('message-container').style.display = 'none';
                                    }, 5000);
                                }
                            } else {
                                clickedButton.innerHTML = originalText;
                                clickedButton.className = originalClass;
                                clickedButton.disabled = false;
                                console.error('Error:', data.message);

                                // نمایش پیام خطا
                                const messageAlert = document.getElementById('message-alert');
                                if (messageAlert) {
                                    messageAlert.textContent = 'Failed to send connection request: ' + data.message;
                                    messageAlert.classList.add('alert-danger');
                                    document.getElementById('message-container').style.display = 'block';
                                    setTimeout(() => {
                                        document.getElementById('message-container').style.display = 'none';
                                    }, 5000);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            clickedButton.innerHTML = originalText;
                            clickedButton.className = originalClass;
                            clickedButton.disabled = false;

                            // نمایش پیام خطای شبکه
                            const messageAlert = document.getElementById('message-alert');
                            if (messageAlert) {
                                messageAlert.textContent = 'Network error or server issue. Please try again.';
                                messageAlert.classList.add('alert-danger');
                                document.getElementById('message-container').style.display = 'block';
                                setTimeout(() => {
                                    document.getElementById('message-container').style.display = 'none';
                                }, 5000);
                            }
                        });
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const videoModal = document.getElementById('videoModal');
            const videoPlayer = document.getElementById('introVideoPlayer');

            videoModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const videoPath = button.getAttribute('data-video-path');
                if (videoPath) {
                    videoPlayer.src = videoPath;
                    videoPlayer.load();
                    videoPlayer.play();
                }
            });

            videoModal.addEventListener('hide.bs.modal', function() {
                videoPlayer.pause();
                videoPlayer.src = '';
            });
        });
    </script>

    <?php include "footer.php"; ?>

</body>

</html>