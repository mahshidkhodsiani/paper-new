<?php
session_start();
include "../config.php"; // Database connection file

$user = null;
$profileId = null;
$presentations = [];
$savedPresentationIds = [];

if (!($conn instanceof mysqli) || $conn->connect_error) {
    error_log("Database connection failed in profile.php: " . $conn->connect_error);
    header("Location: ../error.php?code=db_conn_failed");
    exit();
}

$loggedInUserId = $_SESSION['user_id'] ?? ($_SESSION['user_data']['id'] ?? null);

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profileId = intval($_GET['id']);
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
        // کوئری اصلاح شده: likes_count و views_count از دیتابیس خوانده شدند
        $presentations_sql = "SELECT id, title, description, pdf_path, video_path, role, created_at, keywords, likes_count, views_count, video_duration, slides_path, supplements_path FROM presentations WHERE user_id = ? ORDER BY created_at DESC";
        $presentations_stmt = $conn->prepare($presentations_sql);
        if ($presentations_stmt) {
            $presentations_stmt->bind_param("i", $profileId);
            $presentations_stmt->execute();
            $presentations_result = $presentations_stmt->get_result();

            while ($row = $presentations_result->fetch_assoc()) {
                $presentationId = $row['id'];

                // منطق لایک واقعی
                $row['has_user_liked'] = false;
                if ($loggedInUserId) {
                    $sql_check_like = "SELECT COUNT(*) AS liked FROM likes WHERE user_id = ? AND presentation_id = ?";
                    $stmt_check_like = $conn->prepare($sql_check_like);
                    if ($stmt_check_like) {
                        $stmt_check_like->bind_param("ii", $loggedInUserId, $presentationId);
                        $stmt_check_like->execute();
                        $result_check_like = $stmt_check_like->get_result()->fetch_assoc();
                        if ($result_check_like['liked'] > 0) {
                            $row['has_user_liked'] = true;
                        }
                        $stmt_check_like->close();
                    }
                }

                // منطق رتبه‌بندی و کامنت
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

                $row['has_user_rated'] = false;
                $row['user_rating'] = 0;
                $row['user_comment'] = '';

                if ($loggedInUserId) {
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
                }

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
    <link rel="icon" type="image/x-icon" href="../images/logo.png">

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

        /* CSS برای شبیه‌سازی ظاهر عکس */
        .presentation-card {
            border-left: 5px solid #007bff;
            /* نوار آبی رنگ کنار کارت */
        }

        /* اصلاح برای تیره‌تر شدن متن متا دیتا */
        .presentation-meta {
            color: #495057 !important;
            /* رنگ تیره تر (gray-700) */
            font-weight: 500;
        }

        /* اصلاح برای قرارگیری دکمه پلی ویدیو در وسط */
        .media-placeholder {
            background-color: #f8f9fa;
            border-radius: .25rem;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            position: relative;
            /* برای اطمینان از قرارگیری در مرکز */
        }

        .video-play-link {
            /* لینک اصلی ویدیو حذف شد تا مدال باز شود. */
            color: inherit;
            /* حذف رنگ آبی لینک */
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <video id="introVideoPlayer" controls class="w-100" style="max-height: 80vh;">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-3" id="message-container"></div>
    <div class="container mt-4">
        <div class="row">
            <?php include "sidebar.php"; ?>
            <div class="col-md-6">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">

                    <?php if ($loggedInUserId == $profileId) : ?>
                        <h3 class="mb-4 fw-bold">My Presentations</h3>
                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-primary"><i class="fas fa-upload me-2"></i> Upload New</button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($presentations)) : ?>
                        <div class="mb-4">
                            <?php foreach ($presentations as $presentation) : ?>
                                <div class="card presentation-card mb-4 shadow-sm">
                                    <div class="card-body">
                                        <h4 class="card-title mb-1 fw-bold"><?= htmlspecialchars($presentation['title']) ?></h4>
                                        <p class="small mb-3 presentation-meta">
                                            <i class="fas fa-calendar-alt me-1"></i> <?= date('M d, Y', strtotime($presentation['created_at'])) ?>
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-eye me-1"></i> <?= htmlspecialchars(number_format($presentation['views_count'] ?? 0)) ?> views
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-comment me-1"></i> <?= htmlspecialchars($presentation['rating_count'] ?? 0) ?> comments
                                        </p>

                                        <div class="row mb-3 align-items-center">
                                            <div class="col-md-8">
                                                <?php if (!empty($presentation['video_path'])) : ?>
                                                    <div class="ratio ratio-16x9 media-placeholder"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#videoModal"
                                                        data-video-path="<?= htmlspecialchars($presentation['video_path']) ?>"
                                                        aria-label="Play Presentation Video">
                                                        <div class="text-center video-play-link">
                                                            <i class="fas fa-play-circle fa-4x text-muted"></i>
                                                        </div>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="ratio ratio-16x9 media-placeholder">
                                                        <p class="text-muted">Video Not Available</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php if (!empty($presentation['pdf_path'])) : ?>
                                                    <div class="text-center p-3 border rounded">
                                                        <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                                        <p class="fw-bold mb-1">Research Paper</p>
                                                        <p class="text-muted small mb-3">12 pages · 2.4 MB</p>

                                                        <a href="<?= htmlspecialchars($presentation['pdf_path']) ?>" target="_blank" class="btn btn-primary btn-sm me-1 view-link"><i class="fas fa-eye me-1"></i> View</a>
                                                        <a href="<?= htmlspecialchars($presentation['pdf_path']) ?>" download class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i> Download</a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($presentation['description'])) : ?>
                                            <p class="card-text mb-4"><?= nl2br(htmlspecialchars($presentation['description'])) ?></p>
                                        <?php endif; ?>

                                        <div class="mb-4">
                                            <?php if (!empty($presentation['slides_path'])) : ?>
                                                <p class="mb-2 text-muted">
                                                    <i class="far fa-file-powerpoint me-1 text-info"></i> Presentation Slides.pptx
                                                    <a href="<?= htmlspecialchars($presentation['slides_path']) ?>" download class="float-end text-secondary"><i class="fas fa-download"></i></a>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($presentation['supplements_path'])) : ?>
                                                <p class="mb-0 text-muted">
                                                    <i class="fas fa-file-archive me-1 text-secondary"></i> Supplementary Materials.zip
                                                    <a href="<?= htmlspecialchars($presentation['supplements_path']) ?>" download class="float-end text-secondary"><i class="fas fa-download"></i></a>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <hr>

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="actions d-flex align-items-center">

                                                <?php if ($loggedInUserId) : ?>
                                                    <button class="btn btn-link text-decoration-none like-toggle p-0 me-3"
                                                        data-presentation-id="<?= htmlspecialchars($presentation['id']) ?>"
                                                        data-is-liked="<?= $presentation['has_user_liked'] ? 'true' : 'false' ?>"
                                                        style="color: inherit;">
                                                        <span class="like-icon me-1">
                                                            <i class="<?= $presentation['has_user_liked'] ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' ?>"></i>
                                                        </span>
                                                        <span class="like-count" data-initial-count="<?= htmlspecialchars($presentation['likes_count'] ?? 0) ?>">
                                                            <?= htmlspecialchars($presentation['likes_count'] ?? 0) ?>
                                                        </span>
                                                    </button>
                                                <?php else : ?>
                                                    <span class="text-muted p-0 me-3">
                                                        <i class="far fa-heart me-1"></i>
                                                        <?= htmlspecialchars($presentation['likes_count'] ?? 0) ?>
                                                    </span>
                                                <?php endif; ?>

                                                <p class="mb-0 text-muted me-3">
                                                    <i class="far fa-comment me-1"></i> Comment
                                                </p>

                                                <?php
                                                $isAlreadySaved = in_array($presentation['id'], $savedPresentationIds);
                                                if ($loggedInUserId && $loggedInUserId != $profileId) : ?>
                                                    <button class="btn btn-link text-decoration-none save-toggle p-0 me-3"
                                                        data-presentation-id="<?= htmlspecialchars($presentation['id']) ?>"
                                                        data-is-saved="<?= $isAlreadySaved ? 'true' : 'false' ?>"
                                                        style="color: inherit;">
                                                        <span class="save-icon me-1">
                                                            <i class="<?= $isAlreadySaved ? 'fas fa-bookmark text-primary' : 'far fa-bookmark text-muted' ?>"></i>
                                                        </span>
                                                        Save
                                                    </button>
                                                <?php endif; ?>

                                            </div>

                                            <button class="btn btn-success" onclick="shareItem(
                                                'Presentation: <?= htmlspecialchars($presentation['title']) ?> by <?= htmlspecialchars($user['name'] . ' ' . $user['family']) ?>',
                                                'Check out this presentation on: <?= htmlspecialchars($presentation['title']) ?>',
                                                '<?= $presentation['pdf_path'] ?>'
                                                )">
                                                <i class="fas fa-share-alt me-1"></i> Share
                                            </button>
                                        </div>

                                        <?php if ($presentation['rating_count'] > 0) : ?>
                                            <div class="rating-section mt-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rating-stars me-2" data-rating="<?= htmlspecialchars($presentation['avg_rating']) ?>">
                                                        <?php
                                                        $avgRating = $presentation['avg_rating'];
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $avgRating) {
                                                                echo '<i class="fas fa-star text-warning"></i>';
                                                            } else if ($i - 0.5 <= $avgRating) {
                                                                echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                                            } else {
                                                                echo '<i class="far fa-star text-warning"></i>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <p class="mb-0 fw-bold me-2">(<?= htmlspecialchars($avgRating) ?>/5)</p>
                                                    <small class="text-muted ms-2">(<?= htmlspecialchars($presentation['rating_count']) ?> votes)</small>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($loggedInUserId && $loggedInUserId != $profileId && !$presentation['has_user_rated']) : ?>
                                            <div class="rating-form mt-3" data-presentation-id="<?= htmlspecialchars($presentation['id']) ?>">
                                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                    <i class="far fa-star rating-star" data-rating="<?= $i ?>"></i>
                                                <?php endfor; ?>
                                                <div class="mt-2" style="display:none;" id="comment-box-<?= htmlspecialchars($presentation['id']) ?>">
                                                    <textarea class="form-control" rows="2" placeholder="Leave a comment..."></textarea>
                                                </div>
                                                <button class="btn btn-primary btn-sm mt-2 submit-rating-btn" style="display:none;">Submit Rating</button>
                                            </div>
                                        <?php elseif ($loggedInUserId && $presentation['has_user_rated']) : ?>
                                            <div class="alert alert-info py-2 px-3 d-inline-block mt-3">
                                                You have rated this:
                                                <span class="text-warning">
                                                    <?php for ($i = 1; $i <= $presentation['user_rating']; $i++) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } ?>
                                                </span>
                                                <?php if (!empty($presentation['user_comment'])) : ?>
                                                    <p class="mt-2 mb-0">Your comment: "<?= htmlspecialchars($presentation['user_comment']) ?>"</p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($presentation['comments'])) : ?>
                                            <div class="comment-section mt-4">
                                                <h6><i class="fas fa-comments me-1"></i> User Comments</h6>
                                                <?php foreach ($presentation['comments'] as $comment) : ?>
                                                    <div class="comment-item">
                                                        <p class="mb-1"><strong><?= htmlspecialchars($comment['name'] . ' ' . $comment['family']) ?></strong> <small class="text-muted ms-2"><?= date('M d, Y', strtotime($comment['created_at'])) ?></small></p>
                                                        <p class="mb-0"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div> <?php endforeach; ?>
                        </div>

                    <?php else : ?>
                        <div class="mb-4">
                            <h5 class="profile-section-title"><i class="fas fa-chalkboard-teacher me-2"></i>Presentations</h5>
                            <p class="text-muted">No presentations available for this user.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            <div class="col-md-3">
                <?php /* Sidebar content is here (Availability, Share) - Kept as is for brevity */ ?>
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
    <script src="rate_presentation.js"></script>
    <script>
        // اضافه کردن jQuery برای AJAX (اگر در includes.php نباشد)
        if (typeof jQuery == 'undefined') {
            document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
        }

        // توابع showMessage و shareItem (بدون تغییر)
        document.addEventListener('DOMContentLoaded', function() {
            // ... منطق ویدیوی معرفی و نمایش پیام ...
            const urlParams = new URLSearchParams(window.location.search);
            const statusParam = urlParams.get('status');
            const msgParam = urlParams.get('msg');
            if (statusParam && msgParam) {
                showMessage(decodeURIComponent(msgParam), statusParam);
            }
        });

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

            msgContainer.innerHTML = '';
            msgContainer.appendChild(alertDiv);
            msgContainer.style.display = 'block';

            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();

                setTimeout(() => {
                    // بجای رفرش کامل، در این حالت فقط پیام را حذف می‌کنیم تا رفرش ناخواسته پیش نیاید، 
                    // مگر اینکه سیستم اصلی شما برای رفرش ساخته شده باشد.
                    // window.location.reload(); 
                }, 1000);

            }, 1000);
        }

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
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link has been copied to the clipboard.');
                }).catch(err => {
                    console.error('Failed to copy the link:', err);
                    alert('Failed to copy the link. Please copy it manually: ' + url);
                });
            }
        }

        // منطق فعال‌سازی مدال ویدیو برای پرزنتیشن‌ها 
        document.addEventListener('DOMContentLoaded', function() {
            var videoModal = document.getElementById('videoModal');
            var videoPlayer = document.getElementById('introVideoPlayer');

            if (videoModal && videoPlayer) {
                videoModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    // استفاده از data-video-path برای پرزنتیشن‌ها
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

            // =========================================================
            // منطق AJAX برای Like و Save (بر اساس فایل‌های toggle_like.php و toggle_save.php)
            // =========================================================
            $('.like-toggle').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var presentationId = $button.data('presentation-id');
                var isLiked = $button.data('is-liked') === true || $button.data('is-liked') === 'true'; // اطمینان از خواندن صحیح boolean
                var action = isLiked ? 'unlike' : 'like';
                var $icon = $button.find('.like-icon i');
                var $count = $button.find('.like-count');

                $.ajax({
                    url: '../actions/toggle_like.php', // آدرس واقعی به فایل PHP لایک
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        presentation_id: presentationId,
                        action: action
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // به‌روزرسانی وضعیت و شمارنده در HTML
                            $button.data('is-liked', !isLiked);
                            $count.text(response.new_count);

                            if (action === 'like') {
                                $icon.removeClass('far fa-heart text-muted').addClass('fas fa-heart text-danger');
                            } else {
                                $icon.removeClass('fas fa-heart text-danger').addClass('far fa-heart text-muted');
                            }
                        } else {
                            alert('Like Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error communicating with the server for liking.');
                    }
                });
            });

            // === 2. SAVE Toggle ===
            $('.save-toggle').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var presentationId = $button.data('presentation-id');
                var isSaved = $button.data('is-saved') === true || $button.data('is-saved') === 'true'; // اطمینان از خواندن صحیح boolean
                var action = isSaved ? 'unsave' : 'save';
                var $icon = $button.find('.save-icon i');

                $button.attr('disabled', true); // غیرفعال کردن موقت دکمه

                $.ajax({
                    url: '../actions/toggle_save.php', // آدرس واقعی به فایل PHP ذخیره
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        presentation_id: presentationId,
                        action: action
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $button.data('is-saved', !isSaved);

                            if (action === 'save') {
                                $icon.removeClass('far fa-bookmark text-muted').addClass('fas fa-bookmark text-primary');
                            } else {
                                $icon.removeClass('fas fa-bookmark text-primary').addClass('far fa-bookmark text-muted');
                            }
                        } else {
                            alert('Save Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error communicating with the server for saving.');
                    },
                    complete: function() {
                        $button.attr('disabled', false); // فعال کردن مجدد دکمه
                    }
                });
            });
            // =========================================================
        });
    </script>
</body>

</html>