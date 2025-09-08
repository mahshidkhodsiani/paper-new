<?php

session_start();

if (!isset($_SESSION['user_data'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_data']['id']; // For updating current user's data

include "../config.php"; // Database connection file

// --- Unified Message Handling ---
$message = ''; // For success/error messages
$messageType = '';

if (isset($_SESSION['message']) && isset($_SESSION['messageType'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    // Clear message from session to prevent re-display on refresh
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
} elseif (isset($_GET['status']) && isset($_GET['msg'])) {
    $messageType = $_GET['status'];
    $message = urldecode($_GET['msg']);
}



function uploadFile($fileInputName, $baseTargetDir, $allowedExtensions, $maxFileSize, $conn, $userId, $columnName, $oldFilePath, $hideFile = null)
{
    $success = false;
    $msg = '';
    $type = '';
    $targetFilePath = '';

    // Handle file upload if file was selected
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == UPLOAD_ERR_OK) {
        $fileName = $_FILES[$fileInputName]['name'];
        $fileTmpName = $_FILES[$fileInputName]['tmp_name'];
        $fileSize = $_FILES[$fileInputName]['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check file extension
        if (!in_array($fileExt, $allowedExtensions)) {
            $msg = "Invalid file type. Only " . implode(', ', $allowedExtensions) . " files are allowed.";
            $type = 'danger';
            return ['success' => false, 'message' => $msg, 'messageType' => $type];
        }

        // Check file size - if maxFileSize is 0, no limit is enforced by PHP.
        if ($maxFileSize > 0 && $fileSize > $maxFileSize) {
            $msg = "File is too large. Max size allowed is " . ($maxFileSize / (1024 * 1024)) . " MB.";
            $type = 'danger';
            return ['success' => false, 'message' => $msg, 'messageType' => $type];
        }

        // Construct the file storage path based on User ID
        $targetUserDir = $baseTargetDir . $userId . "/";
        // Create the user's directory if it doesn't exist
        if (!is_dir($targetUserDir)) {
            mkdir($targetUserDir, 0777, true);
        }

        // Generate a unique file name
        $newFileName = uniqid('user_' . $userId . '_') . '.' . $fileExt;
        $targetFilePath = $targetUserDir . $newFileName;

        // Move the uploaded file from temporary to final destination
        if (move_uploaded_file($fileTmpName, $targetFilePath)) {
            // Delete the old file if it exists and is not a default file
            if (
                !empty($oldFilePath) && file_exists($oldFilePath) &&
                !in_array($oldFilePath, ['../images/default_resume.pdf', '../videos/default_video.mp4'])
            ) {
                unlink($oldFilePath);
            }
            $success = true;
        } else {
            $msg = "Error uploading file. Please check directory permissions.";
            $type = 'danger';
            return ['success' => false, 'message' => $msg, 'messageType' => $type];
        }
    } elseif (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] != UPLOAD_ERR_NO_FILE) {
        // Handle PHP upload errors
        $phpFileUploadErrors = [
            UPLOAD_ERR_INI_SIZE     => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE    => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL      => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR   => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE   => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION    => 'A PHP extension stopped the file upload.',
        ];
        $msg = "File upload error: " . ($phpFileUploadErrors[$_FILES[$fileInputName]['error']] ?? 'Unknown upload error.');
        $type = 'danger';
        return ['success' => false, 'message' => $msg, 'messageType' => $type];
    }

    // Prepare the SQL query
    if ($success) {
        // Update the file path, hide status, AND the last update timestamp
        $sql = "UPDATE users SET " . $columnName . " = ?, hide_resume = ?, last_resume_update = NOW() WHERE id = ?";
        $params = [$targetFilePath, $hideFile, $userId];
        $types = "sii";
    } else {
        // If NO new file was uploaded, only update the hide status and the last update timestamp
        $sql = "UPDATE users SET hide_resume = ?, last_resume_update = NOW() WHERE id = ?";
        $params = [$hideFile, $userId];
        $types = "ii";
    }

    // Execute the database update
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // After successful database update, re-fetch all user data to sync the session
            $sql_fetch_updated_user = "SELECT id, name, family, email, profile_pic, university, birthdate, education, workplace, meeting_info, linkedin_url, x_url, google_scholar_url, github_url, website_url, biography, custom_profile_link, availability_status, meeting_link, google_calendar, last_resume_update, intro_video_path, resume_pdf_path, hide_resume FROM users WHERE id = ?";
            $stmt_fetch = $conn->prepare($sql_fetch_updated_user);
            if ($stmt_fetch) {
                $stmt_fetch->bind_param("i", $userId);
                $stmt_fetch->execute();
                $result_fetch = $stmt_fetch->get_result();
                if ($result_fetch->num_rows > 0) {
                    $_SESSION['user_data'] = $result_fetch->fetch_assoc();
                }
                $stmt_fetch->close();
            }

            $msg = $success ? "File uploaded and settings updated successfully." : "Settings updated successfully.";
            $type = 'success';
            $stmt->close();
            return ['success' => true, 'message' => $msg, 'messageType' => $type];
        } else {
            // If database update fails and file was uploaded, delete the uploaded file
            if ($success && isset($targetFilePath) && file_exists($targetFilePath)) {
                unlink($targetFilePath);
            }
            $msg = "Database update failed: " . $stmt->error;
            $type = 'danger';
            $stmt->close();
            return ['success' => false, 'message' => $msg, 'messageType' => $type];
        }
    } else {
        $msg = "Database query preparation failed: " . $conn->error;
        $type = 'danger';
        return ['success' => false, 'message' => $msg, 'messageType' => $type];
    }
}


// --- Process Availability Update Form ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_availability'])) {
    $availability_status = filter_input(INPUT_POST, 'availability_status', FILTER_SANITIZE_STRING);
    $meeting_link = filter_input(INPUT_POST, 'meeting_link', FILTER_SANITIZE_URL);
    $google_calendar = filter_input(INPUT_POST, 'google_calendar', FILTER_SANITIZE_URL);

    // Validation
    if (empty($availability_status)) {
        $_SESSION['message'] = 'Please select your availability status.';
        $_SESSION['messageType'] = 'danger';
    } elseif ($availability_status === 'meeting_link' && empty($meeting_link)) {
        $_SESSION['message'] = 'For "Share Meeting Link" status, a meeting link is required.';
        $_SESSION['messageType'] = 'danger';
    } elseif ($availability_status === 'google_calendar_embed' && empty($google_calendar)) {
        $_SESSION['message'] = 'For "Display Google Calendar" status, a Google Calendar embed link is required.';
        $_SESSION['messageType'] = 'danger';
    } else {
        $conn_update = new mysqli($servername, $username, $password, $dbname);
        if ($conn_update->connect_error) {
            die("Connection failed: " . $conn_update->connect_error);
        }
        $conn_update->set_charset("utf8mb4");

        // UPDATE query
        $sql_update = "UPDATE users SET availability_status = ?, meeting_link = ?, google_calendar = ?, last_resume_update = NOW() WHERE id = ?";
        $stmt_update = $conn_update->prepare($sql_update);

        if ($stmt_update) {
            $stmt_update->bind_param("sssi", $availability_status, $meeting_link, $google_calendar, $userId);
            if ($stmt_update->execute()) {
                $_SESSION['message'] = 'Availability status updated successfully.';
                $_SESSION['messageType'] = 'success';

                // --- Update $_SESSION['user_data'] after successful save ---
                // Fetch all relevant fields including new ones to keep session data current
                $sql_fetch_updated_user = "SELECT id, name, family, email, profile_pic, university, birthdate, education, workplace, meeting_info, linkedin_url, x_url, google_scholar_url, github_url, website_url, biography, custom_profile_link, availability_status, meeting_link, google_calendar, last_resume_update, intro_video_path, resume_pdf_path, hide_resume FROM users WHERE id = ?"; // Added resume_pdf_path, hide_resume
                $stmt_fetch = $conn_update->prepare($sql_fetch_updated_user);
                if ($stmt_fetch) {
                    $stmt_fetch->bind_param("i", $userId);
                    $stmt_fetch->execute();
                    $result_fetch = $stmt_fetch->get_result();
                    if ($result_fetch->num_rows > 0) {
                        $_SESSION['user_data'] = $result_fetch->fetch_assoc();
                    }
                    $stmt_fetch->close();
                }
                // --- End session update ---

            } else {
                $_SESSION['message'] = 'Error updating status: ' . $stmt_update->error;
                $_SESSION['messageType'] = 'danger';
            }
            $stmt_update->close();
        } else {
            $_SESSION['message'] = 'Query preparation error: ' . $conn_update->error;
            $_SESSION['messageType'] = 'danger';
        }
        $conn_update->close();
    }

    // Redirect to prevent form resubmission (Post/Redirect/Get pattern)
    header("Location: index.php"); // No need for GET params here, as messages are in SESSION
    exit();
}

// --- Process Resume PDF Upload Form ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_resume_pdf'])) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    $baseTargetDir = "../uploads/pdfs/";
    $allowedExtensions = ['pdf'];
    $maxFileSize = 0; // No size limit

    // Get hide_resume value (0 or 1)
    $hideResume = isset($_POST['hide_resume']) ? (int)$_POST['hide_resume'] : 0;

    // Call the unified uploadFile function
    $uploadResult = uploadFile(
        'resume_pdf',
        $baseTargetDir,
        $allowedExtensions,
        $maxFileSize,
        $conn,
        $userId,
        'resume_pdf_path',
        $_SESSION['user_data']['resume_pdf_path'] ?? '',
        $hideResume
    );

    // Store the result in session for display after redirect
    $_SESSION['message'] = $uploadResult['message'];
    $_SESSION['messageType'] = $uploadResult['messageType'];

    $conn->close();
    header("Location: ./"); // Redirect without GET parameters
    exit();
}

// Load user data from session (already updated if a POST request just happened)
$user = $_SESSION['user_data'];

// --- Handle multiple universities/educations (assuming semicolon-separated for this example) ---
$user_universities_array = !empty($user['university']) ? explode(';', $user['university']) : [];
$user_educations_array = !empty($user['education']) ? explode(';', $user['education']) : [];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>

    <?php include "../includes.php"; ?>
    <link rel="stylesheet" href="styles.css">

    <link rel="icon" type="image/x-icon" href="../images/logo.png">


    <style>
        /* CSS for the circular video container */
        .circular-video-container {
            position: relative;
            width: 250px;
            /* Adjust size as needed */
            height: 250px;
            /* Must be equal to width for a perfect circle */
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 30px auto;
            /* Center it and add bottom margin */
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #007bff;
            /* Optional border */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .circular-video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Ensures video fills the circle without distortion */
            display: block;
        }

        .circular-video-container .control-button-overlay {
            /* Changed class name for clarity */
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            /* Dark overlay */
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
            /* Allows click to pass through to the overlay div */
        }

        /* If no video is uploaded, show a placeholder */
        .circular-video-container.no-video {
            background-color: #f0f0f0;
            border: 1px dashed #ccc;
        }

        .circular-video-container.no-video i.fa-video-slash {
            color: #aaa;
            font-size: 3em;
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
                    $introVideoPath = htmlspecialchars($user['intro_video_path'] ?? '');
                    $hasVideo = !empty($introVideoPath) && file_exists($introVideoPath);
                    ?>
                    <div class="circular-video-container <?= !$hasVideo ? 'no-video' : '' ?>" id="introVideoContainer">
                        <?php if ($hasVideo): ?>
                            <video id="introVideo" preload="metadata">
                                <source src="<?= $introVideoPath ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="control-button-overlay" id="videoOverlay">
                                <i class="fas fa-play" id="controlButtonIcon"></i>
                            </div>
                        <?php else: ?>
                            <i class="fas fa-video-slash"></i>
                            <p class="text-muted mt-3" style="position: absolute; bottom: 20px;">No Intro Video</p>
                        <?php endif; ?>
                    </div>


                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h5 class="profile-section-title"><i class="fas fa-briefcase me-2"></i>Professional & Educational Information</h5>
                        <?php if (!empty($user_universities_array)): ?>
                            <h6><i class="fas fa-university me-2 text-primary"></i>Universities:</h6>
                            <ul>
                                <?php foreach ($user_universities_array as $uni): ?>
                                    <li><?= htmlspecialchars(trim($uni)) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($user_educations_array)): ?>
                            <h6><i class="fas fa-graduation-cap me-2 text-primary"></i>Education:</h6>
                            <ul>
                                <?php foreach ($user_educations_array as $edu): ?>
                                    <li><?= htmlspecialchars(trim($edu)) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($user['workplace'])): ?>
                            <p><i class="fas fa-building me-2 text-primary"></i>Workplace: <?= htmlspecialchars($user['workplace']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($user['birthdate'])): ?>
                            <p><i class="fas fa-birthday-cake me-2 text-primary"></i>Birthdate: <?= htmlspecialchars($user['birthdate']) ?></p>
                        <?php endif; ?>

                    </div>

                    <?php if (!empty($user['biography'])): ?>
                        <div class="mb-4">
                            <h5 class="profile-section-title"><i class="fas fa-info-circle me-2"></i>About Me</h5>
                            <p class="text-justify"><?= nl2br(htmlspecialchars($user['biography'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($user['linkedin_url']) || !empty($user['x_url']) || !empty($user['google_scholar_url']) || !empty($user['github_url']) || !empty($user['website_url'])): ?>
                        <div class="mb-4">
                            <h5 class="profile-section-title"><i class="fas fa-link me-2"></i>Social & Web Links</h5>
                            <div class="social-links">
                                <?php if (!empty($user['linkedin_url'])): ?>
                                    <a href="<?= htmlspecialchars($user['linkedin_url']) ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($user['x_url'])): ?>
                                    <a href="<?= htmlspecialchars($user['x_url']) ?>" target="_blank" title="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($user['google_scholar_url'])): ?>
                                    <a href="<?= htmlspecialchars($user['google_scholar_url']) ?>" target="_blank" title="Google Scholar"><i class="fas fa-graduation-cap"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($user['github_url'])): ?>
                                    <a href="<?= htmlspecialchars($user['github_url']) ?>" target="_blank" title="GitHub"><i class="fab fa-github"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($user['website_url'])): ?>
                                    <a href="<?= htmlspecialchars($user['website_url']) ?>" target="_blank" title="Website"><i class="fas fa-globe"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="col-md-3">
                <div class="optional-sidebar shadow-sm p-3 mb-5 bg-white rounded">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>



                    <div class="mb-4">
                        <h5 class="profile-section-title"><i class="fas fa-share-alt me-2"></i>Share My Profile</h5>
                        <button class="btn btn-primary btn-lg w-100" onclick="shareProfile('<?= htmlspecialchars($user['name'] . ' ' . $user['family']) ?>')">
                            <i class="fas fa-share-alt me-2"></i> Share
                        </button>
                    </div>





                    <hr>
                    <h5 class="profile-section-title"><i class="fas fa-calendar-check me-2"></i>Set Your Availability</h5>
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="availabilityStatus" class="form-label">Availability Status</label>
                            <select class="form-select" id="availabilityStatus" name="availability_status">
                                <option value="available" <?= (($user['availability_status'] ?? '') == 'available') ? 'selected' : '' ?>>Available</option>
                                <option value="busy" <?= (($user['availability_status'] ?? '') == 'busy') ? 'selected' : '' ?>>Busy</option>
                                <option value="meeting_link" <?= (($user['availability_status'] ?? '') == 'meeting_link') ? 'selected' : '' ?>>Share Meeting Link</option>
                                <option value="google_calendar_embed" <?= (($user['availability_status'] ?? '') == 'google_calendar_embed') ? 'selected' : '' ?>>Display Google Calendar</option>
                            </select>
                        </div>
                        <div class="mb-3" id="meetingLinkInputGroup" style="display: <?= (($user['availability_status'] ?? '') == 'meeting_link') ? 'block' : 'none' ?>;">
                            <label for="meetingLink" class="form-label">Meeting Link (e.g., Google Meet)</label>
                            <input type="url" class="form-control" id="meetingLink" name="meeting_link" value="<?= htmlspecialchars($user['meeting_link'] ?? '') ?>" placeholder="https://meet.google.com/your-room">
                            <small class="form-text text-muted">This link will be displayed when your status is 'Share Meeting Link'.</small>
                        </div>
                        <div class="mb-3" id="googleCalendarEmbedInputGroup" style="display: <?= (($user['availability_status'] ?? '') == 'google_calendar_embed') ? 'block' : 'none' ?>;">
                            <label for="googleCalendarEmbedLink" class="form-label">Google Calendar Embed Link</label>
                            <input type="url" class="form-control" id="googleCalendarEmbedLink" name="google_calendar" value="<?= htmlspecialchars($user['google_calendar'] ?? '') ?>" placeholder="Copy the embed link from your Google Calendar settings">
                            <small class="form-text text-muted">This calendar will be displayed when your status is 'Display Google Calendar'.</small>
                        </div>
                        <button type="submit" name="update_availability" class="btn btn-primary btn-sm mt-2">Update Status</button>
                    </form>

                    <h5 class="profile-section-title mt-4"><i class="fas fa-clock me-2"></i>Current Availability</h5>
                    <div class="availability-status-box
                        <?php
                        switch ($user['availability_status'] ?? 'available') {
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
                        ?>
                    ">
                        <?php if (($user['availability_status'] ?? '') == 'available'): ?>
                            <i class="fas fa-circle-check status-icon"></i>
                            <p class="status-text">Available Now</p>
                        <?php elseif (($user['availability_status'] ?? '') == 'busy'): ?>
                            <i class="fas fa-circle-xmark status-icon"></i>
                            <p class="status-text">Currently Busy</p>
                        <?php elseif (($user['availability_status'] ?? '') == 'meeting_link' && !empty($user['meeting_link'])): ?>
                            <i class="fas fa-handshake status-icon"></i>
                            <p class="status-text">Let's Connect!</p>
                            <a href="<?= htmlspecialchars($user['meeting_link']) ?>" target="_blank" class="btn btn-primary btn-sm mt-2">Join Meeting</a>
                        <?php elseif (($user['availability_status'] ?? '') == 'google_calendar_embed' && !empty($user['google_calendar'])): ?>
                            <i class="fas fa-calendar-alt status-icon"></i>
                            <p class="status-text">My Calendar:</p>
                            <iframe src="<?= htmlspecialchars($user['google_calendar']) ?>" style="border: 0" width="100%" height="300" frameborder="0" scrolling="no"></iframe>
                            <small class="form-text text-muted mt-2 d-block">See my free slots in the calendar above.</small>
                        <?php else: ?>
                            <i class="fas fa-question-circle status-icon"></i>
                            <p class="status-text">Status Not Set</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($user['custom_profile_link'])): ?>
                        <h5 class="profile-section-title mt-4"><i class="fas fa-share-alt me-2"></i>Share My Profile</h5>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SERVER['HTTP_HOST'] . '/profile/' . $user['custom_profile_link']) ?>" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)"><i class="fas fa-copy"></i></button>
                        </div>
                        <small class="text-muted mb-4 d-block">Click to copy your unique profile link.</small>
                    <?php endif; ?>

                    <hr>
                    <p>Upload Your Resume:</p>

                    <?php if (!empty($user['last_resume_update'])): ?>

                        <p>Last Resume Update: <?= date('Y-m-d', strtotime($user['last_resume_update'])); ?></p>
                    <?php endif; ?>

                    <?php
                    if (isset($user['resume_pdf_path']) && $user['resume_pdf_path'] !== NULL) {
                    ?>
                        <iframe src="<?= htmlspecialchars($user['resume_pdf_path']) ?>" style="border:0;" allow="fullscreen"></iframe>

                    <?php
                    }
                    ?>


                    <form action="" method="POST" enctype="multipart/form-data" class="mt-3">
                        <div class="mb-3">
                            <label for="resumePdf" class="form-label">Choose PDF File</label>
                            <input class="form-control" type="file" id="resumePdf" name="resume_pdf" accept=".pdf">
                        </div>

                        <div class="mb-3">
                            <label>Hide from others</label>
                            <input type="hidden" name="hide_resume" value="0">
                            <input type="checkbox" name="hide_resume" value="1" <?= (($user['hide_resume'] ?? 0) == 1) ? 'checked' : ''; ?>>

                        </div>


                        <button type="submit" name="upload_resume_pdf" class="btn btn-primary btn-sm">Upload Resume</button>
                    </form>

                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logic for availability status dropdown
            const availabilityStatusSelect = document.getElementById('availabilityStatus');
            const meetingLinkInputGroup = document.getElementById('meetingLinkInputGroup');
            const googleCalendarEmbedInputGroup = document.getElementById('googleCalendarEmbedInputGroup');

            function updateAvailabilityFieldsDisplay() {
                meetingLinkInputGroup.style.display = 'none';
                googleCalendarEmbedInputGroup.style.display = 'none';

                if (availabilityStatusSelect.value === 'meeting_link') {
                    meetingLinkInputGroup.style.display = 'block';
                } else if (availabilityStatusSelect.value === 'google_calendar_embed') {
                    googleCalendarEmbedInputGroup.style.display = 'block';
                }
            }

            availabilityStatusSelect.addEventListener('change', updateAvailabilityFieldsDisplay);
            updateAvailabilityFieldsDisplay(); // Initial execution on page load


            // Logic for the circular video play/pause button
            const introVideo = document.getElementById('introVideo');
            const videoOverlay = document.getElementById('videoOverlay');
            const controlButtonIcon = document.getElementById('controlButtonIcon'); // Get the icon element

            if (introVideo && videoOverlay && controlButtonIcon) { // Only add listener if video elements exist
                videoOverlay.addEventListener('click', function() {
                    if (introVideo.paused) {
                        introVideo.play();
                        controlButtonIcon.classList.remove('fa-play'); // Change icon to pause
                        controlButtonIcon.classList.add('fa-pause');
                        videoOverlay.style.background = 'rgba(0, 0, 0, 0.2)'; // Less opaque overlay when playing
                    } else {
                        introVideo.pause();
                        controlButtonIcon.classList.remove('fa-pause'); // Change icon to play
                        controlButtonIcon.classList.add('fa-play');
                        videoOverlay.style.background = 'rgba(0, 0, 0, 0.4)'; // More opaque overlay when paused
                    }
                });

                // Update icon and overlay when video state changes (e.g., played by browser controls)
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

                // Show overlay and reset icon when video ends
                introVideo.addEventListener('ended', function() {
                    controlButtonIcon.classList.remove('fa-pause');
                    controlButtonIcon.classList.add('fa-play');
                    videoOverlay.style.background = 'rgba(0, 0, 0, 0.4)';
                    introVideo.currentTime = 0; // Reset video to start for replay
                });
            }


            // --- Unified Logic for Handling Alerts and Cleaning URL ---
            // This targets ALL .alert elements on the page after load.
            const allAlerts = document.querySelectorAll('.alert');
            if (allAlerts.length > 0) {
                setTimeout(() => {
                    allAlerts.forEach(alert => {
                        const bootstrapAlert = bootstrap.Alert.getOrCreateInstance(alert);
                        if (bootstrapAlert) {
                            bootstrapAlert.close();
                        } else {
                            // Fallback if Bootstrap's JS isn't fully loaded or instance not found
                            alert.remove();
                        }
                    });

                    // Clean the URL if it contained status/msg parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('status') || urlParams.has('msg')) {
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({}, document.title, cleanUrl);
                    }
                }, 3000); // 3000 milliseconds = 3 seconds
            }
        });




        function shareProfile(userName) {
            // Checks if the browser supports the Web Share API.
            if (navigator.share) {
                navigator.share({
                    title: 'Profile of ' + userName + ' on Paperet',
                    text: 'Check out ' + userName + '\'s profile on our website and get in touch.',
                    url: window.location.href
                }).then(() => {
                    console.log('Sharing was successful!');
                }).catch((error) => {
                    console.error('Error during sharing:', error);
                });
            } else {
                // Fallback for older browsers or desktop
                const currentUrl = window.location.href;
                navigator.clipboard.writeText(currentUrl).then(() => {
                    alert('Profile link has been copied to the clipboard.');
                }).catch(err => {
                    console.error('Failed to copy the link:', err);
                    alert('Failed to copy the link. Please copy it manually: ' + currentUrl);
                });
            }
        }
    </script>


    <?php include "footer.php"; ?>

</body>

</html>