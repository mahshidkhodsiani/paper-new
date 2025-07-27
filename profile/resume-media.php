<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_data'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_data']['id'];
// Include the database connection file
include "../config.php";

$message = ''; // For success/error messages
$messageType = ''; // Type of message (success, danger, etc.)

/**
 * Helper function to upload files (PDF and Video)
 *
 * @param string $fileInputName The name of the file field in the HTML form (e.g., 'resume_pdf')
 * @param string $baseTargetDir The base path for storing files (e.g., '../uploads/pdfs/')
 * @param array $allowedExtensions An array of allowed file extensions
 * @param int $maxFileSize Maximum file size in bytes (0 means no PHP-side limit)
 * @param mysqli $conn The database connection object
 * @param int $userId The current user's ID
 * @param string $columnName The column name in the database to store the file path (e.g., 'resume_pdf_path')
 * @param string $oldFilePath The path of the old file to be deleted
 * @return bool True if upload and update were successful, false otherwise
 */
function uploadFile($fileInputName, $baseTargetDir, $allowedExtensions, $maxFileSize, $conn, $userId, $columnName, $oldFilePath)
{
    global $message, $messageType;

    // Check if file exists and no upload errors occurred
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
        $fileName = $_FILES[$fileInputName]['name'];
        $fileTmpName = $_FILES[$fileInputName]['tmp_name'];
        $fileSize = $_FILES[$fileInputName]['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check file extension
        if (!in_array($fileExt, $allowedExtensions)) {
            $message = "Invalid file type. Only " . implode(', ', $allowedExtensions) . " files are allowed.";
            $messageType = 'danger';
            return false;
        }

        // Check file size - if maxFileSize is 0, no limit is enforced by PHP.
        if ($maxFileSize > 0 && $fileSize > $maxFileSize) {
            $message = "File is too large. Max size allowed is " . ($maxFileSize / (1024 * 1024)) . " MB.";
            $messageType = 'danger';
            return false;
        }

        // Construct the file storage path based on User ID
        $targetUserDir = $baseTargetDir . $userId . "/";
        // Create the user's directory if it doesn't exist
        if (!is_dir($targetUserDir)) {
            mkdir($targetUserDir, 0777, true); // 0777 for development, 0755 recommended for production servers
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

            // Update the path in the database
            $sql_update = "UPDATE users SET $columnName = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update) {
                $stmt_update->bind_param("si", $targetFilePath, $userId);
                if ($stmt_update->execute()) {
                    // Update the user's session with the new path
                    $_SESSION['user_data'][$columnName] = $targetFilePath;
                    $message = "File uploaded and updated successfully.";
                    $messageType = 'success';
                    return true;
                } else {
                    $message = "Database update failed: " . $stmt_update->error;
                    $messageType = 'danger';
                    // If database update fails, delete the uploaded file
                    unlink($targetFilePath);
                    return false;
                }
                $stmt_update->close();
            } else {
                $message = "Database query preparation failed: " . $conn->error;
                $messageType = 'danger';
                return false;
            }
        } else {
            $message = "Error uploading file. Please check directory permissions.";
            $messageType = 'danger';
            return false;
        }
    } elseif (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] != UPLOAD_ERR_NO_FILE) {
        // Handle PHP upload errors
        $phpFileUploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        ];
        $message = "File upload error: " . ($phpFileUploadErrors[$_FILES[$fileInputName]['error']] ?? 'Unknown upload error.');
        $messageType = 'danger';
        return false;
    }
    return false; // If no file was selected or an unknown error occurred
}

// --- Process Resume PDF Upload Form ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_resume_pdf'])) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    $baseTargetDir = "../uploads/pdfs/"; // Base path for PDF storage
    $allowedExtensions = ['pdf'];
    // **No size limit for PDF (set to 0 as requested)**
    $maxFileSize = 0; // 0 means no PHP-side limit

    uploadFile('resume_pdf', $baseTargetDir, $allowedExtensions, $maxFileSize, $conn, $userId, 'resume_pdf_path', $_SESSION['user_data']['resume_pdf_path'] ?? '');


    // Redirect to prevent form resubmission
    header("Location: resume-media.php?status=" . urlencode($messageType) . "&msg=" . urlencode($message));
    exit();
}

// --- Process Introduction Video Upload Form ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_intro_video'])) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    $baseTargetDir = "../uploads/videos/"; // Base path for video storage
    $allowedExtensions = ['mp4', 'webm', 'ogg'];
    // **No size limit for video (set to 0 as requested)**
    $maxFileSize = 0; // 0 means no PHP-side limit

    uploadFile('intro_video', $baseTargetDir, $allowedExtensions, $maxFileSize, $conn, $userId, 'intro_video_path', $_SESSION['user_data']['intro_video_path'] ?? '');

    $conn->close();
    // Redirect to prevent form resubmission
    header("Location: resume-media.php?status=" . urlencode($messageType) . "&msg=" . urlencode($message));
    exit();
}

// --- Load latest user data from database (only if needed) ---
// This section ensures that displayed information is always up-to-date
if (empty($_SESSION['user_data']['resume_pdf_path']) || empty($_SESSION['user_data']['intro_video_path'])) {
    $conn_fetch = new mysqli($servername, $username, $password, $dbname);
    if ($conn_fetch->connect_error) {
        die("Connection failed: " . $conn_fetch->connect_error);
    }
    $conn_fetch->set_charset("utf8mb4");

    $sql_fetch = "SELECT resume_pdf_path, intro_video_path FROM users WHERE id = ?";
    $stmt_fetch = $conn_fetch->prepare($sql_fetch);
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $userId);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        if ($result_fetch->num_rows > 0) {
            $fetched_paths = $result_fetch->fetch_assoc();
            $_SESSION['user_data']['resume_pdf_path'] = $fetched_paths['resume_pdf_path'];
            $_SESSION['user_data']['intro_video_path'] = $fetched_paths['intro_video_path'];
        }
        $stmt_fetch->close();
    }
    $conn_fetch->close();
}

// --- Handle messages passed via GET (after redirect) ---
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $messageType = $_GET['status'];
    $message = urldecode($_GET['msg']);
}

// Current user data from session for display
$user = $_SESSION['user_data'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume & Introduction Media</title>

    <?php
    // Ensure this file includes links to Bootstrap and Font Awesome
    include "../includes.php";
    ?>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* CSS for delete button styling */
        .delete-btn {
            margin-top: 10px;
        }

        .file-display-box {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }

        .file-display-box h6 {
            margin-bottom: 15px;
            color: #333;
        }

        .file-display-box iframe,
        .file-display-box video {
            width: 100%;
            height: 300px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .file-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            /* For responsiveness on small screens */
        }
    </style>

</head>

<body>

    <?php
    // Include the page header
    include "header.php";
    ?>

    <div class="container mt-4">
        <div class="row">

            <?php
            // Include the sidebar
            include "sidebar.php";
            ?>

            <div class="col-md-6">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">
                    <h4 class="mb-4">Manage Your Resume and Introduction Media</h4>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="mb-5 file-display-box">
                        <h6><i class="fas fa-file-pdf me-2"></i>Upload Your Resume (PDF)</h6>
                        <?php if (!empty($user['resume_pdf_path']) && file_exists($user['resume_pdf_path'])): ?>
                            <p>Current Resume: <a href="<?= htmlspecialchars($user['resume_pdf_path']) ?>" target="_blank">View PDF</a></p>
                            <iframe src="<?= htmlspecialchars($user['resume_pdf_path']) ?>" style="border:0;" allow="fullscreen"></iframe>
                            <div class="file-actions">
                                <a href="<?= htmlspecialchars($user['resume_pdf_path']) ?>" target="_blank" class="btn btn-info btn-sm">View Current PDF</a>
                                <form action="delete_file.php" method="post" onsubmit="return confirm('Are you sure you want to delete your resume PDF? This action cannot be undone.');">
                                    <input type="hidden" name="file_type" value="resume_pdf">
                                    <input type="hidden" name="file_path" value="<?= htmlspecialchars($user['resume_pdf_path']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm delete-btn">Delete PDF</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No resume PDF uploaded yet.</p>
                            <img src="../images/default_resume.png" alt="Default Resume Placeholder" class="img-fluid mb-3" style="max-width: 150px;">
                        <?php endif; ?>

                        <form action="" method="post" enctype="multipart/form-data" class="mt-3">
                            <div class="mb-3">
                                <label for="resumePdf" class="form-label">Choose PDF File</label>
                                <input class="form-control" type="file" id="resumePdf" name="resume_pdf" accept=".pdf">
                            </div>
                            <button type="submit" name="upload_resume_pdf" class="btn btn-primary btn-sm">Upload Resume</button>
                        </form>
                    </div>

                    <div class="mb-5 file-display-box">
                        <h6><i class="fas fa-video me-2"></i>Upload Your Introduction Video</h6>
                        <?php if (!empty($user['intro_video_path']) && file_exists($user['intro_video_path'])): ?>
                            <video controls>
                                <source src="<?= htmlspecialchars($user['intro_video_path']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="file-actions">
                                <a href="<?= htmlspecialchars($user['intro_video_path']) ?>" target="_blank" class="btn btn-info btn-sm">Download/View Video</a>
                                <form action="delete_file.php" method="post" onsubmit="return confirm('Are you sure you want to delete your introduction video? This action cannot be undone.');">
                                    <input type="hidden" name="file_type" value="intro_video">
                                    <input type="hidden" name="file_path" value="<?= htmlspecialchars($user['intro_video_path']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm delete-btn">Delete Video</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No introduction video uploaded yet.</p>
                            <img src="../images/default_video.png" alt="Default Video Placeholder" class="img-fluid mb-3" style="max-width: 150px;">
                        <?php endif; ?>

                        <form action="" method="post" enctype="multipart/form-data" class="mt-3">
                            <div class="mb-3">
                                <label for="introVideo" class="form-label">Choose Video File (MP4, WebM, OGG)</label>
                                <input class="form-control" type="file" id="introVideo" name="intro_video" accept="video/mp4,video/webm,video/ogg">
                                <small class="text-muted">No strict size limit on server-side, but very large files may fail due to PHP or web server configurations.</small>
                            </div>
                            <button type="submit" name="upload_intro_video" class="btn btn-primary btn-sm">Upload Video</button>
                        </form>
                    </div>

                </div>
            </div>

            <div class="col-md-3">
                <div class="optional-sidebar shadow-sm p-3 mb-5 bg-white rounded">
                    <h5 class="profile-section-title"><i class="fas fa-info-circle me-2"></i>Tips</h5>
                    <p class="text-muted">
                        A well-crafted resume and an engaging introduction video can significantly boost your profile's appeal.
                        Keep your video concise and professional!
                    </p>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to handle automatic dismissal and URL cleanup for alerts
            const urlParams = new URLSearchParams(window.location.search);
            const statusParam = urlParams.get('status');
            const msgParam = urlParams.get('msg');

            if (statusParam && msgParam) {
                // Remove the URL parameters after a short delay (e.g., 3 seconds)
                setTimeout(() => {
                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }, 3000);

                // Optionally, if you want to automatically dismiss the Bootstrap alert:
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