<?php
session_start();
include "../config.php"; // Database connection file

$user = null; // Variable to hold user data
$profileId = null;

// Handle potential database connection errors early
if (!($conn instanceof mysqli) || $conn->connect_error) {
    // Log the error for debugging (optional, but good practice)
    error_log("Database connection failed in profile.php: " . $conn->connect_error);
    // Redirect to an error page or display a message
    header("Location: ../error.php?code=db_conn_failed");
    exit();
}

// Get user ID from GET parameter (for viewing other profiles)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profileId = intval($_GET['id']);

    // Use prepared statements for security against SQL injection
    $sql = "SELECT id, name, family, email, profile_pic, university, birthdate, education, workplace, meeting_info, linkedin_url, x_url, google_scholar_url, github_url, website_url, biography, custom_profile_link, availability_status, meeting_link, google_calendar, last_resume_update, intro_video_path 
            FROM users 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $profileId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            // User with given ID not found
            error_log("User not found for profileId: " . $profileId);
        }
        $stmt->close(); // Close statement after use
    } else {
        // Error preparing the statement
        error_log("Failed to prepare statement in profile.php: " . $conn->error);
    }
} else {
    // If no ID is provided or it's not numeric, potentially redirect to the logged-in user's profile
    // or to a generic people listing, or an error page.
    // For now, redirecting to home page as in your original code.
}

// If user not found (either no ID or ID not found in DB), redirect
if (!$user) {
    header("Location: ../index.php"); // or to error page: error.php?code=404
    exit();
}

// --- Message handling (if redirected from another page) ---
$message = ''; // For success/error messages
$messageType = '';
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $messageType = $_GET['status'];
    $message = urldecode($_GET['msg']);
}

// --- Handling multiple universities/educations (assuming separated by semicolons) ---
// Use null coalescing operator to avoid "Undefined array key" notices if the column is null
$user_universities_array = !empty($user['university']) ? explode(';', $user['university']) : [];
$user_educations_array = !empty($user['education']) ? explode(';', $user['education']) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile of <?php echo htmlspecialchars($user['name'] . ' ' . $user['family']); ?></title>

    <?php include "../includes.php"; // This should include your CSS/JS frameworks 
    ?>
    <link rel="stylesheet" href="styles.css">

    <style>
        /* CSS for circular video container */
        .circular-video-container {
            position: relative;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 30px auto;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #007bff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .circular-video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .circular-video-container .control-button-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .circular-video-container .control-button-overlay:hover {
            background: rgba(0, 0, 0, 0.6);
        }

        .circular-video-container .control-button-overlay i {
            color: white;
            font-size: 3em;
            pointer-events: none;
        }

        /* If no video uploaded */
        .circular-video-container.no-video {
            background-color: #f0f0f0;
            border: 1px dashed #ccc;
        }

        .circular-video-container.no-video i.fa-video-slash {
            color: #aaa;
            font-size: 3em;
        }

        /* CSS for availability status box */
        .availability-status-box {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .availability-status-box .status-icon {
            font-size: 2.5em;
            margin-bottom: 10px;
            display: block;
        }

        .availability-status-box .status-text {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 5px;
        }

        /* Colors for different statuses */
        .availability-status-box.status-available {
            background-color: #e6ffe6;
            border: 1px solid #4CAF50;
            color: #388E3C;
        }

        .availability-status-box.status-available .status-icon {
            color: #4CAF50;
        }

        .availability-status-box.status-busy {
            background-color: #ffe6e6;
            border: 1px solid #f44336;
            color: #D32F2F;
        }

        .availability-status-box.status-busy .status-icon {
            color: #f44336;
        }

        .availability-status-box.status-meeting-link {
            background-color: #e6f7ff;
            border: 1px solid #2196F3;
            color: #1976D2;
        }

        .availability-status-box.status-meeting-link .status-icon {
            color: #2196F3;
        }

        .availability-status-box.status-google-calendar {
            background-color: #fffde7;
            border: 1px solid #FFC107;
            color: #FFA000;
        }

        .availability-status-box.status-google-calendar .status-icon {
            color: #FFC107;
        }

        /* Style for section titles */
        .profile-section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 20px;
            color: #007bff;
            font-weight: bold;
        }

        .social-links a {
            font-size: 2em;
            margin-right: 15px;
            color: #007bff;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: #0056b3;
        }

        .calendar-container {
            position: relative;
            padding-bottom: 75%;
            /* Aspect ratio for calendar */
            height: 0;
            overflow: hidden;
        }

        .calendar-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row">
            <?php include "sidebar.php"; ?>

            <div class="col-md-6">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">


                    <?php
                    // Use null coalescing operator for intro_video_path to prevent errors if null
                    $introVideoPath = htmlspecialchars($user['intro_video_path'] ?? '');
                    // Check if the path is not empty and the file exists on the server
                    $hasVideo = !empty($introVideoPath) && file_exists($introVideoPath);
                    ?>
                    <div class="circular-video-container <?= !$hasVideo ? 'no-video' : '' ?>" id="introVideoContainer">
                        <?php if ($hasVideo) : ?>
                            <video id="introVideo" preload="metadata">
                                <source src="<?= $introVideoPath ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="control-button-overlay" id="videoOverlay">
                                <i class="fas fa-play" id="controlButtonIcon"></i>
                            </div>
                        <?php else : ?>
                            <i class="fas fa-video-slash"></i>
                            <p class="text-muted mt-3" style="position: absolute; bottom: 20px;">No introduction video</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($message)) : ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h5 class="profile-section-title"><i class="fas fa-briefcase me-2"></i>Academic and Professional Information</h5>
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
                        <?php if (!empty($user['birthdate'])) : ?>
                            <p><i class="fas fa-birthday-cake me-2 text-primary"></i>Birthdate: <?= htmlspecialchars($user['birthdate']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($user['last_resume_update'])) : ?>
                            <p><i class="fas fa-calendar-alt me-2 text-primary"></i>Last resume update: <?= date('Y-m-d H:i', strtotime($user['last_resume_update'])) ?></p>
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
                            <h5 class="profile-section-title"><i class="fas fa-link me-2"></i>Social Media and Web Links</h5>
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
                </div>
            </div>

            <div class="col-md-3">
                <div class="optional-sidebar shadow-sm p-3 mb-5 bg-white rounded">
                    <?php if (!empty($message)) : ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <h5 class="profile-section-title mt-4">
                        <i class="fas fa-clock me-2"></i>Current Availability Status
                    </h5>

                    <div class="availability-status-box
    <?php
    // Use null coalescing to provide a default empty string if 'availability_status' is not set
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
            echo 'status-available'; // Default to available if status is not explicitly set
            break;
    }
    ?>
">
                        <?php if (($user['availability_status'] ?? '') == 'available') : ?>
                            <i class="fas fa-circle-check status-icon"></i>
                            <p class="status-text">Currently Available</p>
                            <?php if (!empty($user['meeting_info'])) : ?>
                                <p class="small"><?= htmlspecialchars($user['meeting_info']) ?></p>
                            <?php endif; ?>

                        <?php elseif (($user['availability_status'] ?? '') == 'busy') : ?>
                            <i class="fas fa-circle-xmark status-icon"></i>
                            <p class="status-text">Currently Busy</p>
                            <?php if (!empty($user['meeting_info'])) : ?>
                                <p class="small"><?= htmlspecialchars($user['meeting_info']) ?></p>
                            <?php endif; ?>

                        <?php elseif (($user['availability_status'] ?? '') == 'meeting_link' && !empty($user['meeting_link'])) : ?>
                            <i class="fas fa-handshake status-icon"></i>
                            <p class="status-text">Available for Meetings</p>
                            <a href="<?= htmlspecialchars($user['meeting_link']) ?>" target="_blank" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-video me-1"></i> Join Meeting
                            </a>
                            <?php if (!empty($user['meeting_info'])) : ?>
                                <p class="small mt-2"><?= htmlspecialchars($user['meeting_info']) ?></p>
                            <?php endif; ?>

                        <?php elseif (($user['availability_status'] ?? '') == 'google_calendar_embed' && !empty($user['google_calendar'])) : ?>
                            <i class="fas fa-calendar-alt status-icon"></i>
                            <p class="status-text">Check My Availability</p>
                            <div class="calendar-container mt-3">
                                <?php
                                // Ensure the embedded calendar code is properly handled (and sanitized if from user input)
                                // If google_calendar might contain arbitrary HTML, you should sanitize it more robustly.
                                // For trusted sources, direct echo might be acceptable.
                                if (strpos($user['google_calendar'], '<iframe') !== false) {
                                    echo $user['google_calendar'];
                                } else {
                                    echo '<iframe src="' . htmlspecialchars($user['google_calendar']) . '" style="border: 0" width="100%" height="300" frameborder="0" scrolling="no"></iframe>';
                                }
                                ?>
                            </div>
                            <small class="form-text text-muted mt-2 d-block">Check my available times in the calendar above.</small>

                        <?php else : ?>
                            <i class="fas fa-question-circle status-icon"></i>
                            <p class="status-text">Availability Not Set</p>
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
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logic for circular video play/pause button
            const introVideo = document.getElementById('introVideo');
            const videoOverlay = document.getElementById('videoOverlay');
            const controlButtonIcon = document.getElementById('controlButtonIcon');

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

            // Logic for URL cleanup and closing alerts
            const urlParams = new URLSearchParams(window.location.search);
            const statusParam = urlParams.get('status');
            const msgParam = urlParams.get('msg');

            if (statusParam && msgParam) {
                setTimeout(() => {
                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }, 3000);

                const alertElement = document.querySelector('.alert');
                if (alertElement) {
                    setTimeout(() => {
                        const bsAlert = new bootstrap.Alert(alertElement);
                        bsAlert.close();
                    }, 3000);
                }
            }
        });
    </script>
</body>

</html>

<?php
// Close database connection at the very end of the script
if ($conn instanceof mysqli) {
    $conn->close();
}
?>