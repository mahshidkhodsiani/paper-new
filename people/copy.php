<?php
session_start();
include "../config.php"; // Database connection file

$user = null;
$profileId = null;
$presentations = [];
$savedPresentationIds = [];

// بررسی اتصال به دیتابیس
if (!($conn instanceof mysqli) || $conn->connect_error) {
    error_log("Database connection failed in profile.php: " . $conn->connect_error);
    header("Location: ../error.php?code=db_conn_failed");
    exit();
}

$loggedInUserId = $_SESSION['user_id'] ?? ($_SESSION['user_data']['id'] ?? null);

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profileId = intval($_GET['id']);

    // کوئری اطلاعات کاربر
    $sql = "SELECT id, name, family, email, profile_pic, university, birthdate, education, workplace, meeting_info, linkedin_url, x_url, google_scholar_url, github_url, website_url, biography, custom_profile_link, availability_status, meeting_link, google_calendar, last_resume_update, intro_video_path , resume_pdf_path, hide_resume, cover_photo FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $profileId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }
        $stmt->close();
    }

    if ($user) {
        // کوئری اصلاح شده: شامل ستون‌های جدید برای دانلودها، آمار و مدت زمان ویدیو
        $presentations_sql = "SELECT id, title, description, pdf_path, video_path, role, created_at, keywords, slides_path, supplements_path, likes_count, views_count, video_duration FROM presentations WHERE user_id = ? ORDER BY created_at DESC";
        $presentations_stmt = $conn->prepare($presentations_sql);
        if ($presentations_stmt) {
            $presentations_stmt->bind_param("i", $profileId);
            $presentations_stmt->execute();
            $presentations_result = $presentations_stmt->get_result();

            while ($row = $presentations_result->fetch_assoc()) {
                $presentationId = $row['id'];

                // --- محاسبه میانگین امتیاز و تعداد رای (واقعی) ---
                $sql_avg_rating = "SELECT AVG(rating_value) AS avg_rating, COUNT(id) AS rating_count FROM ratings WHERE presentation_id = ?";
                $stmt_avg_rating = $conn->prepare($sql_avg_rating);
                if ($stmt_avg_rating) {
                    $stmt_avg_rating->bind_param("i", $presentationId);
                    $stmt_avg_rating->execute();
                    $result_avg = $stmt_avg_rating->get_result()->fetch_assoc();
                    $row['avg_rating'] = round($result_avg['avg_rating'] ?? 0, 1);
                    $row['rating_count'] = $result_avg['rating_count'] ?? 0;
                    $stmt_avg_rating->close();
                }

                // --- بررسی ریتینگ و کامنت کاربر لاگین شده (واقعی) ---
                $row['has_user_rated'] = false;
                $row['user_rating'] = 0;
                $row['user_comment'] = '';
                $row['is_liked'] = false; // وضعیت لایک

                if ($loggedInUserId) {
                    // وضعیت امتیاز و کامنت کاربر
                    $sql_check_rating = "SELECT rating_value, comment FROM ratings WHERE rater_user_id = ? AND presentation_id = ?";
                    $stmt_check_rating = $conn->prepare($sql_check_rating);
                    if ($stmt_check_rating) {
                        $stmt_check_rating->bind_param("ii", $loggedInUserId, $presentationId);
                        $stmt_check_rating->execute();
                        $result_check = $stmt_check_rating->get_result();
                        if ($result_check->num_rows > 0) {
                            $ratedData = $result_check->fetch_assoc();
                            $row['has_user_rated'] = true;
                            $row['user_rating'] = $ratedData['rating_value'];
                            $row['user_comment'] = $ratedData['comment'];
                        }
                        $stmt_check_rating->close();
                    }

                    // وضعیت لایک کاربر (نیاز به جدول likes)
                    $sql_check_like = "SELECT 1 FROM likes WHERE user_id = ? AND presentation_id = ?";
                    $stmt_check_like = $conn->prepare($sql_check_like);
                    if ($stmt_check_like) {
                        $stmt_check_like->bind_param("ii", $loggedInUserId, $presentationId);
                        $stmt_check_like->execute();
                        $result_check_like = $stmt_check_like->get_result();
                        if ($result_check_like->num_rows > 0) {
                            $row['is_liked'] = true;
                        }
                        $stmt_check_like->close();
                    }
                }

                // --- استخراج کامنت‌ها از جدول ratings (واقعی) ---
                $comments_sql = "SELECT u.name, u.family, r.comment, r.created_at FROM ratings r JOIN users u ON r.rater_user_id = u.id WHERE r.presentation_id = ? AND r.comment IS NOT NULL AND r.comment != '' ORDER BY r.created_at DESC";
                $comments_stmt = $conn->prepare($comments_sql);
                $comments_stmt->bind_param("i", $presentationId);
                $comments_stmt->execute();
                $comments_result = $comments_stmt->get_result();
                $row['comments'] = [];
                while ($comment_row = $comments_result->fetch_assoc()) {
                    $row['comments'][] = $comment_row;
                }
                $comments_stmt->close();

                $presentations[] = $row;
            }
            $presentations_stmt->close();
        }

        // کوئری برای لیست ذخیره‌شده‌ها (Saved Presentations)
        if ($loggedInUserId) {
            $saved_sql = "SELECT presentation_id FROM saved_presentations WHERE user_id = ?";
            $saved_stmt = $conn->prepare($saved_sql);
            if ($saved_stmt) {
                $saved_stmt->bind_param("i", $loggedInUserId);
                $saved_stmt->execute();
                $saved_result = $saved_stmt->get_result();
                while ($row = $saved_result->fetch_assoc()) {
                    $savedPresentationIds[] = $row['presentation_id'];
                }
                $saved_stmt->close();
            }
        }
    }
} else {
    // هدایت کاربر به صفحه خودش یا صفحه اصلی اگر لاگین نیست
    if ($loggedInUserId) {
        header("Location: /paper-new/people/profile.php?id=" . $loggedInUserId);
    } else {
        header("Location: /paper-new/index.php");
    }
    exit();
}

if (!$user) {
    header("Location: ../index.php");
    exit();
}

$message = '';
$messageType = '';
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $messageType = htmlspecialchars($_GET['status']);
    $message = htmlspecialchars(urldecode($_GET['msg']));
}

$user_universities_array = !empty($user['university']) ? explode(';', $user['university']) : [];
$user_educations_array = !empty($user['education']) ? explode(';', $user['education']) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile of <?php echo htmlspecialchars($user['name'] . ' ' . $user['family']); ?></title>
    <?php include "../includes.php"; ?>
    <link rel="stylesheet" href="styles.css">
    <style>
        .comment-section {
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
            padding: 10px;
            margin-top: 15px;
        }

        .comment-item {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .comment-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        /* Helper class for clickability */
        .cursor-pointer {
            cursor: pointer;
        }

        .rating-stars .fa-star {
            color: #ccc;
            /* Default color */
            transition: color 0.1s;
        }

        .rating-stars .fa-star.fas {
            color: #ffc107;
            /* Gold for selected stars */
        }

        .rating-stars .fa-star:hover {
            color: #ffc107 !important;
            /* Gold on hover */
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>
    <div class="container mt-3" id="message-container"></div>
    <div class="container mt-4">
        <div class="row">
            <?php include "sidebar.php"; ?>


            <div class="col-md-6">

                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">

                    <div class="border-bottom pb-2 mb-4">
                        <h2 class="mb-0">
                            <?php
                            if ($profileId == $loggedInUserId) {
                                echo 'My Presentations';
                            } else {
                                echo htmlspecialchars($user['name'] . ' ' . $user['family']) . "'s Presentations";
                            }
                            ?>
                        </h2>
                    </div>

                    <?php if (!empty($presentations)): ?>
                        <?php foreach ($presentations as $presentation): ?>

                            <div class="presentation-block shadow-lg p-0 mb-5 bg-white rounded border">

                                <div class="p-3 border-bottom text-white" style="background-color: #343a40;">
                                    <h4 class="mb-0 fw-bold"><?= htmlspecialchars($presentation['title']) ?></h4>
                                    <div class="small mt-1 d-flex align-items-center">
                                        <i class="fas fa-calendar-alt me-1"></i> <?= date('M d, Y', strtotime($presentation['created_at'])) ?>
                                        <span class="ms-3 me-3"><i class="fas fa-eye me-1"></i> <?= $presentation['views_count'] ?? 0 ?> views</span>
                                        <span><i class="fas fa-comments me-1"></i> <?= count($presentation['comments']) ?> comments</span>
                                    </div>
                                </div>

                                <div class="p-4 d-flex flex-wrap align-items-center justify-content-center ">

                                    <div class="me-4 mb-3 mb-md-0" style=" max-width: 100%;">
                                        <?php if (!empty($presentation['video_path'])): ?>
                                            <div class="video-container position-relative" style="height: 180px; border-radius: 5px; overflow: hidden; width: 300px;">
                                                <video class="w-100 h-100" controls poster="<?= $presentation['video_thumbnail_path'] ?? '' ?>">
                                                    <source src="<?= htmlspecialchars($presentation['video_path']) ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <?php if (!empty($presentation['video_duration'])): ?>
                                                    <span class="badge bg-dark position-absolute bottom-0 end-0 m-1"><?= htmlspecialchars($presentation['video_duration']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="video-placeholder position-relative bg-light d-flex justify-content-center align-items-center" style="height: 180px; border-radius: 5px; overflow: hidden; border: 1px dashed #ccc; width: 300px;">
                                                <p class="text-muted mb-0">Video not available</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($presentation['pdf_path'])): ?>
                                        <div class="d-flex flex-column p-3 rounded text-center" style="width: 160px; min-width: 150px; border: 1px solid #dee2e6;">
                                            <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i>
                                            <p class="mb-1 fw-bold">Research Paper</p>
                                            <p class="small text-muted mb-3">PDF File</p>
                                            <a href="<?= htmlspecialchars($presentation['pdf_path']) ?>" target="_blank" class="btn btn-outline-info btn-sm mb-2"><i class="fas fa-eye me-1"></i> View</a>
                                            <a href="<?= htmlspecialchars($presentation['pdf_path']) ?>" class="btn btn-success btn-sm" download><i class="fas fa-download me-1"></i> Download</a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="px-4 py-3 border-top">
                                    <p class="card-text mb-0"><?= htmlspecialchars($presentation['description']) ?></p>
                                </div>

                                <div class="p-4 border-top">
                                    <?php if (!empty($presentation['slides_path'])): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><i class="far fa-file-powerpoint me-2 text-primary"></i> Presentation Slides</span>
                                            <a href="<?= htmlspecialchars($presentation['slides_path']) ?>" download class="text-secondary"><i class="fas fa-download"></i></a>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small mb-0">Presentation slides not available.</p>
                                    <?php endif; ?>

                                    <?php if (!empty($presentation['supplements_path'])): ?>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span><i class="fas fa-file-archive me-2 text-warning"></i> Supplementary Materials</span>
                                            <a href="<?= htmlspecialchars($presentation['supplements_path']) ?>" download class="text-secondary"><i class="fas fa-download"></i></a>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small mb-0">Supplementary materials not available.</p>
                                    <?php endif; ?>
                                </div>

                                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center small">
                                        <?php
                                        // وضعیت لایک: is_liked از دیتابیس می‌آید
                                        $likeClass = $presentation['is_liked'] ? 'fas text-danger' : 'far text-muted';
                                        $likeAction = $presentation['is_liked'] ? 'unlike' : 'like';
                                        ?>
                                        <a href="javascript:void(0)" class="me-3 text-decoration-none like-link" data-presentation-id="<?= $presentation['id'] ?>" data-action="<?= $likeAction ?>">
                                            <i class="fa-heart me-1 <?= $likeClass ?>" id="like-icon-<?= $presentation['id'] ?>"></i>
                                            <span id="likes-count-<?= $presentation['id'] ?>"><?= $presentation['likes_count'] ?? 0 ?></span>
                                        </a>

                                        <a href="#comments-<?= $presentation['id'] ?>" class="text-muted me-3 text-decoration-none"><i class="far fa-comment me-1"></i> Comment</a>

                                        <?php
                                        $isSaved = in_array($presentation['id'], $savedPresentationIds);
                                        $saveClass = $isSaved ? 'fas' : 'far';
                                        $saveText = $isSaved ? 'Saved' : 'Save';
                                        $saveAction = $isSaved ? 'unsave' : 'save';
                                        ?>
                                        <a href="javascript:void(0)" class="text-muted me-3 text-decoration-none save-link" data-presentation-id="<?= $presentation['id'] ?>" data-action="<?= $saveAction ?>">
                                            <i class="<?= $saveClass ?> fa-bookmark me-1" id="save-icon-<?= $presentation['id'] ?>"></i> <span id="save-text-<?= $presentation['id'] ?>"><?= $saveText ?></span>
                                        </a>
                                    </div>

                                    <button class="btn btn-success btn-sm" onclick="shareItem('<?= htmlspecialchars($presentation['title']) ?>', '<?= htmlspecialchars(substr($presentation['description'], 0, 50)) ?>...', window.location.href + '?presentation=<?= $presentation['id'] ?>')">
                                        <i class="fas fa-share-alt me-2"></i> Share
                                    </button>
                                </div>

                                <div class="p-3 border-top d-flex align-items-center">
                                    <span class="text-warning me-2">
                                        <?php $avg_rating = $presentation['avg_rating'] ?? 0; ?>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa<?= ($avg_rating >= $i) ? 's' : 'r' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="small text-muted">(<?= $avg_rating ?>/5) based on <?= $presentation['rating_count'] ?? 0 ?> ratings)</span>
                                </div>


                                <div class="p-3 border-top" id="comments-<?= $presentation['id'] ?>">
                                    <h5 class="mb-3">Ratings & Comments</h5>

                                    <?php if ($loggedInUserId): ?>

                                        <div class="mb-4 p-3 border rounded">
                                            <h6><?php echo $presentation['has_user_rated'] ? 'Edit Your Rating' : 'Rate This Presentation'; ?></h6>
                                            <form class="rate-form" data-presentation-id="<?= $presentation['id'] ?>">
                                                <div class="mb-2">
                                                    <label class="form-label">Your Rating:</label>
                                                    <div class="rating-stars" data-current-rating="<?= $presentation['user_rating'] ?>">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fa<?= ($presentation['user_rating'] >= $i) ? 's' : 'r' ?> fa-star cursor-pointer star-input" data-rating="<?= $i ?>"></i>
                                                        <?php endfor; ?>
                                                        <input type="hidden" name="rating_value" value="<?= $presentation['user_rating'] ?>" required>
                                                        <input type="hidden" name="presentation_id" value="<?= $presentation['id'] ?>">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="comment-<?= $presentation['id'] ?>" class="form-label">Your Comment (Optional):</label>
                                                    <textarea class="form-control" id="comment-<?= $presentation['id'] ?>" name="comment" rows="3"><?= htmlspecialchars($presentation['user_comment']) ?></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-primary btn-sm">Submit Rating</button>
                                                <div class="feedback-msg mt-2 small"></div>
                                            </form>
                                        </div>

                                    <?php else: ?>
                                        <div class="alert alert-warning small">
                                            Please <a href="../login.php">log in</a> to rate or comment on this presentation.
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($presentation['comments'])): ?>
                                        <h6 class="mt-4 mb-2">User Comments (<?= count($presentation['comments']) ?>)</h6>
                                        <div class="comment-section">
                                            <?php foreach ($presentation['comments'] as $comment): ?>
                                                <div class="comment-item">
                                                    <p class="mb-0 fw-bold small"><?= htmlspecialchars($comment['name'] . ' ' . $comment['family']) ?></p>
                                                    <p class="mb-0 small text-muted"><?= date('M d, Y', strtotime($comment['created_at'])) ?></p>
                                                    <p class="mt-1"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($presentation['rating_count'] > 0): ?>
                                        <div class="alert alert-info small mt-4">
                                            There are ratings for this presentation, but no user has left a comment yet.
                                        </div>
                                    <?php endif; ?>
                                </div>


                            </div> <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            This user has not uploaded any presentations yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>



            <div class="col-md-3">
                <div class="optional-sidebar shadow-sm p-3 mb-5 bg-white rounded">


                    <div class="mb-4">
                        <h5 class="profile-section-title"><i class="fas fa-share-alt me-2"></i>Share This Profile</h5>
                        <button class="btn btn-primary btn-lg w-100" onclick="shareItem(
                            'Profile of <?= htmlspecialchars($user['name'] . ' ' . $user['family']) ?> on Paperet',
                            'Check out <?= htmlspecialchars($user['name'] . ' ' . $user['family']) ?>\'s profile on our website and get in touch.',
                            window.location.href
                        )">
                            <i class="fas fa-share-alt me-2"></i> Share
                        </button>
                    </div>

                    <h5 class="profile-section-title mt-4"><i class="fas fa-clock me-2"></i>Current Availability Status</h5>
                    <div class="availability-status-box
                        <?php
                        switch ($user['availability_status'] ?? '') {
                            case 'available':
                                echo 'status-available';
                                break;
                            case 'busy':
                                echo 'status-busy';
                                break;
                            case 'meeting_link':
                                echo 'status-meeting-link';
                                break;
                            case 'google_calendar_embed':
                                echo 'status-google-calendar';
                                break;
                            default:
                                echo 'status-available';
                                break;
                        }
                        ?>">
                        <?php if (($user['availability_status'] ?? '') == 'available') : ?>
                            <i class="fas fa-circle-check status-icon"></i>
                            <p class="status-text">Currently Available</p>
                            <?php if (!empty($user['meeting_info'])) : ?><p class="small"><?= htmlspecialchars($user['meeting_info']) ?></p><?php endif; ?>
                        <?php elseif (($user['availability_status'] ?? '') == 'busy') : ?>
                            <i class="fas fa-circle-xmark status-icon"></i>
                            <p class="status-text">Currently Busy</p>
                            <?php if (!empty($user['meeting_info'])) : ?><p class="small"><?= htmlspecialchars($user['meeting_info']) ?></p><?php endif; ?>
                        <?php elseif (($user['availability_status'] ?? '') == 'meeting_link' && !empty($user['meeting_link'])) : ?>
                            <i class="fas fa-handshake status-icon"></i>
                            <p class="status-text">Available for Meetings</p>
                            <a href="<?= htmlspecialchars($user['meeting_link']) ?>" target="_blank" class="btn btn-primary btn-sm mt-2"><i class="fas fa-video me-1"></i> Join Meeting</a>
                            <?php if (!empty($user['meeting_info'])) : ?><p class="small mt-2"><?= htmlspecialchars($user['meeting_info']) ?></p><?php endif; ?>
                        <?php elseif (($user['availability_status'] ?? '') == 'google_calendar_embed' && !empty($user['google_calendar'])) : ?>
                            <i class="fas fa-calendar-alt status-icon"></i>
                            <p class="status-text">Check My Schedule</p>
                            <div class="calendar-container mt-3">
                                <?php if (strpos($user['google_calendar'], '<iframe') !== false) {
                                    echo $user['google_calendar'];
                                } else {
                                    echo '<iframe src="' . htmlspecialchars($user['google_calendar']) . '" style="border: 0" width="100%" height="300" frameborder="0" scrolling="no"></iframe>';
                                } ?>
                            </div>
                            <small class="form-text text-muted mt-2 d-block">Check my available times on the calendar above.</small>
                        <?php else : ?>
                            <i class="fas fa-question-circle status-icon"></i>
                            <p class="status-text">Availability status not set</p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($user['custom_profile_link'])) : ?>
                        <h5 class="profile-section-title mt-4"><i class="fas fa-share-alt me-2"></i>Share My Profile</h5>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SERVER['HTTP_HOST'] . '/profile/' . $user['custom_profile_link']) ?>" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)"><i class="fas fa-copy"></i></button>
                        </div>
                        <small class="text-muted mb-4 d-block">Click to copy my unique profile link.</small>
                    <?php endif; ?>
                </div>
                <hr>
                <?php if ($user['hide_resume'] == 0 && !empty($user['resume_pdf_path'])) : ?>
                    <p>Resume of <?= htmlspecialchars($user['name'] . ' ' . $user['family']) ?> :</p>
                    <iframe src="<?= htmlspecialchars($user['resume_pdf_path']) ?>" style="border:0;" allow="fullscreen"></iframe>
                    <a href="<?= htmlspecialchars($user['resume_pdf_path']) ?>" class="btn btn-outline-info" target="_blank">Download resume</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // ... (کدهای موجود JavaScript) ...
        document.addEventListener('DOMContentLoaded', function() {
            const introVideo = document.getElementById('introVideo');
            const videoOverlay = document.getElementById('videoOverlay');
            const controlButtonIcon = document.getElementById('controlButtonIcon');

            // Handles the intro video
            if (introVideo && videoOverlay && controlButtonIcon) {
                videoOverlay.addEventListener('click', function() {
                    if (introVideo.paused) {
                        introVideo.play();
                        controlButtonIcon.classList.remove('fa-play');
                        controlButtonIcon.classList.add('fa-pause');
                        videoOverlay.style.background = 'rgba(0, 0, 0, 0.2)';
                    } else {
                        introVideo.pause();
                        controlButtonIcon.classList.remove('fa-pause');
                        controlButtonIcon.classList.add('fa-play');
                        videoOverlay.style.background = 'rgba(0, 0, 0, 0.4)';
                    }
                });
                introVideo.addEventListener('play', function() {
                    controlButtonIcon.classList.remove('fa-play');
                    controlButtonIcon.classList.add('fa-pause');
                    videoOverlay.style.background = 'rgba(0, 0, 0, 0.2)';
                });
                introVideo.addEventListener('pause', function() {
                    controlButtonIcon.classList.remove('fa-pause');
                    controlButtonIcon.classList.add('fa-play');
                    videoOverlay.style.background = 'rgba(0, 0, 0, 0.4)';
                });
                introVideo.addEventListener('ended', function() {
                    controlButtonIcon.classList.remove('fa-pause');
                    controlButtonIcon.classList.add('fa-play');
                    videoOverlay.style.background = 'rgba(0, 0, 0, 0.4)';
                    introVideo.currentTime = 0;
                });
            }

            // Displays messages and refreshes the page
            const urlParams = new URLSearchParams(window.location.search);
            const statusParam = urlParams.get('status');
            const msgParam = urlParams.get('msg');
            if (statusParam && msgParam) {
                showMessage(decodeURIComponent(msgParam), statusParam);
            }
        });

        /**
         * displays a message and then reloads the page
         */
        function showMessage(message, type = 'info') {
            const msgContainer = document.getElementById('message-container');
            if (!msgContainer) {
                console.warn("Message container not found.");
                return;
            }

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;

            // Clears previous messages and adds the new one
            msgContainer.innerHTML = '';
            msgContainer.appendChild(alertDiv);
            msgContainer.style.display = 'block';

            // Fades the message out after 3 seconds and then refreshes the page after 1 second
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();

                // Refreshes the page after the message fades out
                setTimeout(() => {
                    // حذف پارامترهای status و msg از URL قبل از رفرش
                    const newUrl = window.location.href.split('?')[0];
                    window.location.href = newUrl;
                }, 1000);

            }, 1000);
        }

        /**
         * Handles sharing any item (profile, presentation, etc.) using the Web Share API.
         */
        function shareItem(title, text, url) {
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: text,
                    url: url
                }).then(() => {
                    console.log('Sharing was successful!');
                }).catch((error) => {
                    console.error('Error during sharing:', error);
                });
            } else {
                // Fallback for older browsers or desktop
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link has been copied to the clipboard.');
                }).catch(err => {
                    console.error('Failed to copy the link:', err);
                    alert('Failed to copy the link. Please copy it manually: ' + url);
                });
            }
        }

        // Logic for intro video modal
        document.addEventListener('DOMContentLoaded', function() {
            var videoModal = document.getElementById('videoModal');
            var videoPlayer = document.getElementById('introVideoPlayer');

            if (videoModal && videoPlayer) {
                videoModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var videoPath = button.getAttribute('data-video-path');
                    if (videoPath) {
                        videoPlayer.src = videoPath;
                        videoPlayer.load();
                    }
                });

                videoModal.addEventListener('hidden.bs.modal', function() {
                    videoPlayer.pause();
                    videoPlayer.src = "";
                });
            }
        });

        // ***************************************************
        // ****** AJAX Logic for Rating, Liking, Saving  ******
        // ***************************************************

        document.addEventListener('DOMContentLoaded', function() {

            // ------------------ Rating & Comment Logic ------------------
            document.querySelectorAll('.rate-form').forEach(form => {
                const presentationId = form.dataset.presentationId;
                const ratingContainer = form.querySelector('.rating-stars');
                const ratingInput = form.querySelector('input[name="rating_value"]');
                const stars = ratingContainer.querySelectorAll('.star-input');
                const feedbackMsg = form.querySelector('.feedback-msg');

                // Star Hover/Click Logic
                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const rating = this.dataset.rating;
                        ratingInput.value = rating;
                        updateStars(ratingContainer, rating);
                    });

                    // Optional: Hover effect
                    star.addEventListener('mouseover', function() {
                        const hoverRating = this.dataset.rating;
                        updateStars(ratingContainer, hoverRating, true);
                    });

                    star.addEventListener('mouseout', function() {
                        const currentRating = ratingInput.value;
                        updateStars(ratingContainer, currentRating);
                    });
                });

                function updateStars(container, rating, isHover = false) {
                    container.querySelectorAll('.star-input').forEach(s => {
                        const sRating = parseInt(s.dataset.rating);
                        if (sRating <= rating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                            if (!isHover) s.classList.add('text-warning');
                            else s.classList.remove('text-warning');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                            s.classList.remove('text-warning');
                        }
                    });
                }
                updateStars(ratingContainer, ratingInput.value); // Initial display

                // Form Submission Logic
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (!ratingInput.value || parseInt(ratingInput.value) === 0) {
                        feedbackMsg.textContent = 'Please select a star rating.';
                        feedbackMsg.classList.add('text-danger');
                        return;
                    }

                    feedbackMsg.textContent = 'Submitting...';
                    feedbackMsg.classList.remove('text-danger', 'text-success');
                    feedbackMsg.classList.add('text-info');

                    const formData = new FormData(form);

                    fetch('../actions/rate_presentation.php', {
                            method: 'POST',
                            body: new URLSearchParams(formData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                feedbackMsg.textContent = data.message;
                                feedbackMsg.classList.remove('text-info', 'text-danger');
                                feedbackMsg.classList.add('text-success');
                                // Reload the page to show new average rating and comments
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                feedbackMsg.textContent = data.message || 'An error occurred during submission.';
                                feedbackMsg.classList.remove('text-info', 'text-success');
                                feedbackMsg.classList.add('text-danger');
                            }
                        })
                        .catch(error => {
                            feedbackMsg.textContent = 'Network error: Could not connect to the server.';
                            feedbackMsg.classList.remove('text-info', 'text-success');
                            feedbackMsg.classList.add('text-danger');
                            console.error('Error:', error);
                        });
                });
            });


            // ------------------ Liking Logic ------------------
            document.querySelectorAll('.like-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const presentationId = this.dataset.presentationId;
                    const action = this.dataset.action; // 'like' or 'unlike'
                    const icon = document.getElementById(`like-icon-${presentationId}`);
                    const countSpan = document.getElementById(`likes-count-${presentationId}`);
                    let currentCount = parseInt(countSpan.textContent);

                    // Optimistic update
                    if (action === 'like') {
                        icon.classList.remove('far', 'text-muted');
                        icon.classList.add('fas', 'text-danger');
                        countSpan.textContent = currentCount + 1;
                        this.dataset.action = 'unlike';
                    } else {
                        icon.classList.remove('fas', 'text-danger');
                        icon.classList.add('far', 'text-muted');
                        countSpan.textContent = currentCount - 1;
                        this.dataset.action = 'like';
                    }

                    fetch('../actions/toggle_like.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `presentation_id=${presentationId}&action=${action}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status !== 'success') {
                                // Revert changes if server side fails
                                alert(data.message || 'An error occurred while liking/unliking.');
                                window.location.reload();
                            }
                            // Update the count with the fresh count from the server (best practice)
                            if (data.new_count !== undefined) {
                                countSpan.textContent = data.new_count;
                            }
                        })
                        .catch(error => {
                            alert('Network error. Reverting changes.');
                            window.location.reload();
                            console.error('Error:', error);
                        });
                });
            });

            // ------------------ Saving Logic ------------------
            document.querySelectorAll('.save-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const presentationId = this.dataset.presentationId;
                    const action = this.dataset.action; // 'save' or 'unsave'
                    const icon = document.getElementById(`save-icon-${presentationId}`);
                    const textSpan = document.getElementById(`save-text-${presentationId}`);

                    // Optimistic update
                    if (action === 'save') {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        textSpan.textContent = 'Saved';
                        this.dataset.action = 'unsave';
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        textSpan.textContent = 'Save';
                        this.dataset.action = 'save';
                    }

                    fetch('../actions/toggle_save.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `presentation_id=${presentationId}&action=${action}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status !== 'success') {
                                alert(data.message || 'An error occurred while saving/unsaving.');
                                window.location.reload();
                            }
                        })
                        .catch(error => {
                            alert('Network error. Reverting changes.');
                            window.location.reload();
                            console.error('Error:', error);
                        });
                });
            });

        });
    </script>
</body>

</html>