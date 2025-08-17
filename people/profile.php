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
        $presentations_sql = "SELECT id, title, description, pdf_path, video_path, role, created_at, keywords FROM presentations WHERE user_id = ? ORDER BY created_at DESC";
        $presentations_stmt = $conn->prepare($presentations_sql);
        if ($presentations_stmt) {
            $presentations_stmt->bind_param("i", $profileId);
            $presentations_stmt->execute();
            $presentations_result = $presentations_stmt->get_result();

            while ($row = $presentations_result->fetch_assoc()) {
                $presentationId = $row['id'];
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
                            <p class="text-justify"><?= nl2br(htmlspecialchars($user['biography'])) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($user['linkedin_url']) || !empty($user['x_url']) || !empty($user['google_scholar_url']) || !empty($user['github_url']) || !empty($user['website_url'])) : ?>
                        <div class="mb-4">
                            <h5 class="profile-section-title"><i class="fas fa-link me-2"></i>Social and Web Links</h5>
                            <div class="social-links">
                                <?php if (!empty($user['linkedin_url'])) : ?>
                                    <a href="<?= htmlspecialchars($user['linkedin_url']) ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($user['x_url'])) : ?>
                                    <a href="<?= htmlspecialchars($user['x_url']) ?>" target="_blank" title="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
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
                    <?php if (!empty($presentations)) : ?>
                        <div class="mb-4">
                            <h5 class="profile-section-title"><i class="fas fa-chalkboard-teacher me-2"></i>Presentations</h5>
                            <?php foreach ($presentations as $presentation) : ?>
                                <div class="presentation-item">
                                    <h6><?= htmlspecialchars($presentation['title']) ?></h6>
                                    <p class="text-muted"><small>Role: <?= htmlspecialchars($presentation['role']) ?></small></p>
                                    <?php if (!empty($presentation['description'])) : ?>
                                        <p><?= nl2br(htmlspecialchars($presentation['description'])) ?></p>
                                    <?php endif; ?>
                                    <div class="actions">
                                        <?php if (!empty($presentation['pdf_path'])) : ?>
                                            <p class="mb-0">
                                                <a href="<?= htmlspecialchars($presentation['pdf_path']) ?>" target="_blank" class="view-link me-3">
                                                    <i class="fas fa-file-pdf me-1"></i> View PDF
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($presentation['video_path'])) : ?>
                                            <p class="mb-0">
                                                <a href="<?= htmlspecialchars($presentation['video_path']) ?>" target="_blank" class="view-link">
                                                    <i class="fas fa-video me-1"></i> View Video
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                        <?php
                                        $isLoggedIn = !empty($loggedInUserId);
                                        $isOwnPresentation = ($loggedInUserId == $profileId);
                                        $isAlreadySaved = in_array($presentation['id'], $savedPresentationIds);
                                        if ($isLoggedIn && !$isOwnPresentation) :
                                            if ($isAlreadySaved) : ?>
                                                <button class="btn btn-success btn-sm btn-saved" disabled>
                                                    <i class="fas fa-check-circle me-1"></i> Saved
                                                </button>
                                            <?php else : ?>
                                                <form action="../profile/save_presentation.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="presentation_id" value="<?= htmlspecialchars($presentation['id']) ?>">
                                                    <input type="hidden" name="current_profile_id" value="<?= htmlspecialchars($profileId) ?>">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm btn-save-presentation">
                                                        <i class="fas fa-plus me-1"></i> Add to Saved
                                                    </button>
                                                </form>
                                        <?php endif;
                                        endif; ?>
                                    </div>
                                    <small class="text-muted mt-2 d-block">Uploaded on: <?= date('Y-m-d', strtotime($presentation['created_at'])) ?></small>

                                    <?php if ($presentation['rating_count'] > 0) : ?>
                                        <div class="rating-section mt-3">
                                            <h6 class="mb-1"><i class="fas fa-star me-1 text-warning"></i>Rating</h6>
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="rating-stars me-2" data-rating="<?= htmlspecialchars($presentation['avg_rating']) ?>">
                                                    <?php
                                                    $avgRating = $presentation['avg_rating'];
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $avgRating) {
                                                            echo '<i class="fas fa-star text-warning"></i>';
                                                        } else {
                                                            echo '<i class="far fa-star text-muted"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <p class="mb-0 fw-bold"><?= htmlspecialchars($avgRating) ?> / 5</p>
                                                <small class="text-muted ms-2">(<?= htmlspecialchars($presentation['rating_count']) ?> votes)</small>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($loggedInUserId && $loggedInUserId != $profileId && !$presentation['has_user_rated']) : ?>
                                        <div class="rating-form" data-presentation-id="<?= htmlspecialchars($presentation['id']) ?>">
                                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                <i class="far fa-star rating-star" data-rating="<?= $i ?>"></i>
                                            <?php endfor; ?>
                                            <div class="mt-2" style="display:none;" id="comment-box-<?= htmlspecialchars($presentation['id']) ?>">
                                                <textarea class="form-control" rows="2" placeholder="Leave a comment..."></textarea>
                                            </div>
                                            <button class="btn btn-primary btn-sm mt-2 submit-rating-btn" style="display:none;">Submit Rating</button>
                                        </div>
                                    <?php elseif ($loggedInUserId && $presentation['has_user_rated']) : ?>
                                        <div class="alert alert-info py-2 px-3 d-inline-block">
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

                                    <button class="btn btn-secondary btn-sm mt-2" onclick="shareItem(
                                        'Presentation: <?= htmlspecialchars($presentation['title']) ?> by <?= htmlspecialchars($user['name'] . ' ' . $user['family']) ?>',
                                        'Check out this presentation on: <?= htmlspecialchars($presentation['title']) ?>',
                                        '<?=$presentation['pdf_path']?>'
                                        )">
                                        <i class="fas fa-share-alt me-1"></i> Share this Presentation
                                    </button>
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

                                    <hr>
                                </div>
                            <?php endforeach; ?>
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
         * @param {string} message - the message to display
         * @param {string} type - The type of the alert (e.g., 'success', 'danger', 'info')
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
                    window.location.reload();
                }, 1000);

            }, 1000);
        }

        /**
         * Handles sharing any item (profile, presentation, etc.) using the Web Share API.
         * @param {string} title - The title of the item to share.
         * @param {string} text - The descriptive text for the item.
         * @param {string} url - The URL of the item to share.
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
    </script>
</body>

</html>