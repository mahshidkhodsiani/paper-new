<?php
session_start();
include "../config.php"; // Database connection file

$user = null; // Variable to hold user data
$profileId = null;
$presentations = []; // Variable to hold presentations data
$savedPresentationIds = []; // To store IDs of presentations already saved by the current user

// Handle potential database connection errors early
if (!($conn instanceof mysqli) || $conn->connect_error) {
    error_log("Database connection failed in profile.php: " . $conn->connect_error);
    header("Location: ../error.php?code=db_conn_failed");
    exit();
}

// Check if a user is logged in to determine if "Save" button should be shown
// This assumes you have $_SESSION['user_id'] set upon login
$loggedInUserId = $_SESSION['user_id'] ?? null;

// Get user ID from GET parameter (for viewing other profiles)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profileId = intval($_GET['id']);

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
            error_log("User not found for profileId: " . $profileId);
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement in profile.php: " . $conn->error);
    }

    // Fetch presentations for the profileId
    if ($user) {
        $presentations_sql = "SELECT id, title, description, file_path, created_at FROM presentations WHERE user_id = ? ORDER BY created_at DESC";
        $presentations_stmt = $conn->prepare($presentations_sql);
        if ($presentations_stmt) {
            $presentations_stmt->bind_param("i", $profileId);
            $presentations_stmt->execute();
            $presentations_result = $presentations_stmt->get_result();
            while ($row = $presentations_result->fetch_assoc()) {
                $presentations[] = $row;
            }
            $presentations_stmt->close();
        } else {
            error_log("Failed to prepare presentations statement in profile.php: " . $conn->error);
        }

        // New: Fetch saved presentations for the logged-in user if available
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
            } else {
                error_log("Failed to prepare saved presentations statement: " . $conn->error);
            }
        }
    }
} else {
    // Redirect logic if no ID is provided in the URL (e.g., redirect to index or logged-in user's profile)
    if ($loggedInUserId) {
        header("Location: ../../people/profile.php?id=" . $loggedInUserId);
    } else {
        header("Location: ../index.php");
    }
    exit();
}

// If user data could not be fetched (e.g., invalid ID)
if (!$user) {
    header("Location: ../index.php"); // Redirect to home page or an error page
    exit();
}

// --- Message handling (if redirected from another page with status/msg in URL) ---
// This part remains to receive status/msg, but the JS will handle the URL cleanup
$message = '';
$messageType = '';
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $messageType = htmlspecialchars($_GET['status']); // Sanitize input
    $message = htmlspecialchars(urldecode($_GET['msg'])); // Sanitize and decode URL message
}

// --- Handling multiple universities/educations (assuming separated by semicolons) ---
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
    <!-- If you have a separate styles.css file in the same directory as profile.php, uncomment this: -->
    <!-- <link rel="stylesheet" href="styles.css"> -->

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

        /* Style for presentation items */
        .presentation-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }

        .presentation-item h6 {
            color: #343a40;
            margin-bottom: 5px;
        }

        .presentation-item p {
            font-size: 0.9em;
            color: #6c757d;
        }

        .presentation-item .actions {
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* To push button to the right */
        }

        .presentation-item a.view-link {
            color: #007bff;
            font-weight: bold;
        }

        .presentation-item a.view-link:hover {
            text-decoration: none;
            color: #0056b3;
        }

        .presentation-item .btn-save-presentation {
            font-size: 0.85em;
            padding: 5px 10px;
        }

        .presentation-item .btn-saved {
            background-color: #28a745;
            color: white;
            cursor: default;
        }

        .presentation-item .btn-saved:hover {
            background-color: #28a745;
            color: white;
        }

        /* Styles for the dynamic alert at the top (if you decide to re-enable it) */
        /* .alert.fixed-top {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            max-width: 80%;
            margin: 15px auto;
            z-index: 1050;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: .25rem;
            padding: .75rem 1.25rem;
            opacity: 1;
            transition: opacity 0.5s ease-out;
        } */
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
                    $introVideoPath = htmlspecialchars($user['intro_video_path'] ?? '');
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
                            <p class="text-muted mt-3" style="position: absolute; bottom: 20px;">No intro video uploaded</p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h5 class="profile-section-title"><i class="fas fa-briefcase me-2"></i>Academic & Work Information</h5>
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
                            <p><i class="fas fa-birthday-cake me-2 text-primary"></i>Date of Birth: <?= htmlspecialchars($user['birthdate']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($user['last_resume_update'])) : ?>
                            <p><i class="fas fa-calendar-alt me-2 text-primary"></i>Last Resume Update: <?= date('Y-m-d H:i', strtotime($user['last_resume_update'])) ?></p>
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
                            <h5 class="profile-section-title"><i class="fas fa-link me-2"></i>Social & Web Links</h5>
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
                                    <?php if (!empty($presentation['description'])) : ?>
                                        <p><?= nl2br(htmlspecialchars($presentation['description'])) ?></p>
                                    <?php endif; ?>
                                    <div class="actions">
                                        <?php if (!empty($presentation['file_path'])) : ?>
                                            <p class="mb-0">
                                                <a href="<?= htmlspecialchars($presentation['file_path']) ?>" target="_blank" class="view-link">
                                                    <i class="fas fa-file-pdf me-1"></i> View Presentation
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                        <?php
                                        // Check if the current user is logged in AND if this presentation is not their own AND if they haven't saved it yet
                                        $isLoggedIn = !empty($loggedInUserId);
                                        $isOwnPresentation = ($loggedInUserId == $profileId); // profileId is the ID of the user whose profile is being viewed
                                        $isAlreadySaved = in_array($presentation['id'], $savedPresentationIds);

                                        if ($isLoggedIn && !$isOwnPresentation) :
                                            if ($isAlreadySaved) : ?>
                                                <button class="btn btn-success btn-sm btn-saved" disabled>
                                                    <i class="fas fa-check-circle me-1"></i> Saved
                                                </button>
                                            <?php else : ?>
                                                <!-- IMPORTANT CHANGE: Using a standard HTML form for saving -->
                                                <form action="../profile/save_presentation.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="presentation_id" value="<?= htmlspecialchars($presentation['id']) ?>">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm btn-save-presentation">
                                                        <i class="fas fa-plus me-1"></i> Add to Saved
                                                    </button>
                                                </form>
                                        <?php endif;
                                        endif; ?>
                                    </div>
                                    <small class="text-muted mt-2 d-block">Uploaded on: <?= date('Y-m-d', strtotime($presentation['created_at'])) ?></small>
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
                    <h5 class="profile-section-title mt-4">
                        <i class="fas fa-clock me-2"></i>Current Availability Status
                    </h5>

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
                                echo 'status-available'; // Default to available if not set
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
                            <p class="status-text">Check My Schedule</p>
                            <div class="calendar-container mt-3">
                                <?php
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

            // --- IMPORTANT: Logic for URL cleanup (WITHOUT showing any alert) ---
            const urlParams = new URLSearchParams(window.location.search);
            const statusParam = urlParams.get('status');
            const msgParam = urlParams.get('msg');

            if (statusParam && msgParam) {
                // If status and msg parameters exist, clear them from the URL after 3 seconds
                setTimeout(function() {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url.toString());
                }, 3000); // Wait for 3 seconds
            }

            // IMPORTANT: The fetch-related JavaScript for .btn-save-presentation is removed
            // because we are now using a standard HTML form submission.
            // The form will handle the navigation to save_presentation.php directly.
        });
    </script>
</body>

</html>