<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_data'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_data']['id'];
include "../config.php";

$message = '';
$messageType = '';
$presentationData = null;
$presentationId = $_GET['id'] ?? null; // Get the presentation ID from the URL

// --- Database Connection Setup ---
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// -----------------------------------------------------------
// --- 1. Fetch Existing Presentation Data ---
// -----------------------------------------------------------
if ($presentationId) {
    // UPDATED: Added co_authors and content_type to the SELECT query
    $sql_fetch = "SELECT id, title, description, co_authors, content_type, pdf_path, video_path, role, keywords FROM presentations WHERE id = ? AND user_id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);

    if ($stmt_fetch) {
        $stmt_fetch->bind_param("ii", $presentationId, $userId);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows > 0) {
            $presentationData = $result_fetch->fetch_assoc();
        } else {
            $message = "Content not found or you don't have permission to edit it.";
            $messageType = 'danger';
            $presentationId = null; // Invalidate ID if not found
        }
        $stmt_fetch->close();
    } else {
        $message = "Database query preparation failed for fetching data: " . $conn->error;
        $messageType = 'danger';
    }
} else {
    $message = "Invalid content ID provided.";
    $messageType = 'danger';
}


// -----------------------------------------------------------
// --- 2. Handle Form Submission (Update Operation) ---
// -----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $presentationData) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $co_authors = $_POST['co_authors'] ?? ''; // New field

    // Handle content type logic
    $content_type = $_POST['content_type'] ?? 'Presentation';
    if ($content_type === 'Other' && isset($_POST['custom_content_type']) && !empty($_POST['custom_content_type'])) {
        $content_type = $_POST['custom_content_type'];
    }
    
    $role = $_POST['role'] ?? 'Author';
    $keywords = $_POST['keywords'] ?? '';

    // Retain existing paths unless new files are uploaded
    $pdf_path = $presentationData['pdf_path'];
    $video_path = $presentationData['video_path'];

    $old_pdf_path = $presentationData['pdf_path'];
    $old_video_path = $presentationData['video_path'];

    $has_error = false;

    // --- Handle PDF file upload (optional update) ---
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == UPLOAD_ERR_OK) {
        $userUploadDir = '../uploads/pdfs/' . $userId . '/';
        $localUploadDir = __DIR__ . '/../uploads/pdfs/' . $userId . '/';

        if (!is_dir($localUploadDir)) {
            // Note: In a real app, mkdir might fail, so check if it succeeded
            mkdir($localUploadDir, 0777, true); 
        }

        $fileName = basename($_FILES['pdf_file']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExt === 'pdf') {
            $uniqueFileName = 'pres_' . uniqid() . '.' . $fileExt;
            $targetFilePath = $localUploadDir . '/' . $uniqueFileName;
            $dbPath = $userUploadDir . $uniqueFileName;

            if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $targetFilePath)) {
                $pdf_path = $dbPath;
                // Delete old PDF file if it exists and a new one was uploaded successfully
                if (!empty($old_pdf_path) && file_exists(realpath($old_pdf_path))) {
                    unlink(realpath($old_pdf_path));
                }
            } else {
                $message = "Error uploading the new PDF file. Please check folder permissions.";
                $messageType = 'danger';
                $has_error = true;
            }
        } else {
            $message = "Invalid PDF file type. Only PDF files are allowed.";
            $messageType = 'danger';
            $has_error = true;
        }
    }

    // --- Handle video file upload (optional update) ---
    if (!$has_error && isset($_FILES['video_file']) && $_FILES['video_file']['error'] == UPLOAD_ERR_OK) {
        $userUploadDir = '../uploads/videos/' . $userId . '/';
        $localUploadDir = __DIR__ . '/../uploads/videos/' . $userId . '/';

        if (!is_dir($localUploadDir)) {
             // Note: In a real app, mkdir might fail, so check if it succeeded
            mkdir($localUploadDir, 0777, true);
        }

        $fileName = basename($_FILES['video_file']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedVideoTypes = ['mp4', 'webm', 'ogg'];

        if (in_array($fileExt, $allowedVideoTypes)) {
            $uniqueFileName = 'user_' . $userId . '_' . uniqid() . '.' . $fileExt;
            $targetFilePath = $localUploadDir . '/' . $uniqueFileName;
            $dbPath = $userUploadDir . $uniqueFileName;

            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetFilePath)) {
                $video_path = $dbPath;
                // Delete old video file if it exists and a new one was uploaded successfully
                if (!empty($old_video_path) && file_exists(realpath($old_video_path))) {
                    unlink(realpath($old_video_path));
                }
            } else {
                $message = "Error uploading the new video file.";
                $messageType = 'danger';
                $has_error = true;
            }
        } else {
            $message = "Invalid video file type. Allowed formats: MP4, WebM, OGG.";
            $messageType = 'danger';
            $has_error = true;
        }
    }

    // --- Database Update ---
    if (!$has_error) {
        // UPDATED: Added co_authors and content_type to the UPDATE query
        $sql_update = "UPDATE presentations SET title = ?, description = ?, co_authors = ?, content_type = ?, pdf_path = ?, video_path = ?, role = ?, keywords = ? WHERE id = ? AND user_id = ?";
        $stmt_update = $conn->prepare($sql_update);

        if ($stmt_update) {
            // UPDATED: Added two 's' for co_authors and content_type (8 strings + 2 integers)
            $stmt_update->bind_param("ssssssssii", $title, $description, $co_authors, $content_type, $pdf_path, $video_path, $role, $keywords, $presentationId, $userId);
            if ($stmt_update->execute()) {
                // Redirect back to my_presentations with a success message
                header("Location: my_presentations.php?status=success&msg=" . urlencode("Content updated successfully!"));
                exit();
            } else {
                $message = "Error updating content: " . $stmt_update->error;
                $messageType = 'danger';
            }
            $stmt_update->close();
        } else {
            $message = "Database update query preparation failed: " . $conn->error;
            $messageType = 'danger';
        }
    }
    // Re-fetch data to reflect non-redirected changes (e.g., if there was an upload error)
    if ($presentationId) {
        $presentationData['title'] = $title;
        $presentationData['description'] = $description;
        $presentationData['co_authors'] = $co_authors;
        $presentationData['content_type'] = $content_type;
        $presentationData['role'] = $role;
        $presentationData['keywords'] = $keywords;
        $presentationData['pdf_path'] = $pdf_path;
        $presentationData['video_path'] = $video_path;
    }
}


// Fallback if presentation data couldn't be loaded (e.g., bad ID)
if (!$presentationData && $presentationId) {
    header("Location: my_presentations.php?status=danger&msg=" . urlencode($message));
    exit();
}

// -----------------------------------------------------------
// --- HTML Output ---
// -----------------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Content: <?= htmlspecialchars($presentationData['title'] ?? 'N/A') ?></title>

    <?php include "../includes.php"; ?>
    <link rel="stylesheet" href="styles.css">

    <link rel="icon" type="image/x-icon" href="../images/logo.png">

</head>

<body>

    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row">

            <?php include "sidebar.php"; ?>

            <div class="col-md-9">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">
                    
                    <a href="my_presentations.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to My Content</a>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($presentationData): ?>
                        <h4 class="mt-4 mb-4">Edit Content: <span class="text-primary"><?= htmlspecialchars($presentationData['title']) ?></span></h4>

                        <form action="edit_presentation.php?id=<?= $presentationId ?>" method="post" enctype="multipart/form-data">
                            
                            <input type="hidden" name="presentation_id" value="<?= $presentationId ?>">

                            <div class="mb-3">
                                <label for="presentationTitle" class="form-label">Title</label>
                                <input type="text" class="form-control" id="presentationTitle" name="title" value="<?= htmlspecialchars($presentationData['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="presentationDescription" class="form-label">Abstract/Description</label>
                                <textarea class="form-control" id="presentationDescription" name="description" rows="3"><?= htmlspecialchars($presentationData['description']) ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="coAuthors" class="form-label">Co-Authors' Names (Optional)</label>
                                <input type="text" class="form-control" id="coAuthors" name="co_authors" value="<?= htmlspecialchars($presentationData['co_authors'] ?? '') ?>">
                                <small class="text-muted">Separate names with commas (e.g., John Doe, Alice Smith).</small>
                            </div>

                            <div class="mb-3">
                                <label for="contentType" class="form-label">Content Type</label>
                                <?php
                                    // Logic to determine if the stored value is a custom type
                                    $defaultOptions = ['Presentation', 'Article', 'Journal', 'Conference', 'ClassProject'];
                                    $currentType = $presentationData['content_type'] ?? 'Presentation';
                                    $isCustomType = !in_array($currentType, $defaultOptions);
                                ?>
                                <select class="form-select" id="contentType" name="<?= ($isCustomType ? 'temp_content_type' : 'content_type') ?>" required onchange="toggleCustomType(this)">
                                    <option value="Presentation" <?= ($currentType == 'Presentation' ? 'selected' : '') ?>>Presentation</option>
                                    <option value="Article" <?= ($currentType == 'Article' ? 'selected' : '') ?>>Article</option>
                                    <option value="Journal" <?= ($currentType == 'Journal' ? 'selected' : '') ?>>Journal</option>
                                    <option value="Conference" <?= ($currentType == 'Conference' ? 'selected' : '') ?>>Conference Paper</option>
                                    <option value="ClassProject" <?= ($currentType == 'ClassProject' ? 'selected' : '') ?>>Class Project</option>
                                    <option value="Other" <?= ($isCustomType ? 'selected' : '') ?>>Other (Specify below)</option>
                                </select>
                                
                                <div id="customTypeDiv" class="mt-2" style="display:<?= ($isCustomType ? 'block' : 'none') ?>;">
                                    <input type="text" class="form-control" id="customContentType" name="<?= ($isCustomType ? 'content_type' : 'custom_content_type') ?>" placeholder="Enter custom type" value="<?= ($isCustomType ? htmlspecialchars($currentType) : '') ?>">
                                </div>
                                <small class="text-muted">Select the type of content.</small>
                            </div>

                            <div class="mb-3">
                                <label for="presentationRole" class="form-label">Your Role</label>
                                <select class="form-select" id="presentationRole" name="role" required>
                                    <option value="Author" <?= ($presentationData['role'] == 'Author') ? 'selected' : '' ?>>Author</option>
                                    <option value="Presenter" <?= ($presentationData['role'] == 'Presenter') ? 'selected' : '' ?>>Presenter</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="presentationKeywords" class="form-label">Keywords (e.g., AI, Machine Learning, Data Science)</label>
                                <input type="text" class="form-control" id="presentationKeywords" name="keywords" value="<?= htmlspecialchars($presentationData['keywords']) ?>">
                                <small class="text-muted">Separate keywords with commas.</small>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="pdfFile" class="form-label">PDF File (Current: 
                                    <?php if (!empty($presentationData['pdf_path'])): ?>
                                        <a href="<?= htmlspecialchars($presentationData['pdf_path']) ?>" target="_blank">View Current PDF</a>
                                    <?php else: ?>
                                        None
                                    <?php endif; ?>
                                )</label>
                                <input class="form-control" type="file" id="pdfFile" name="pdf_file" accept=".pdf">
                                <small class="text-muted">Upload a new PDF file to replace the current one. Leave blank to keep the current file. The PDF is required for content submission.</small>
                            </div>

                            <div class="mb-3">
                                <label for="videoFile" class="form-label">Video File (Current: 
                                    <?php if (!empty($presentationData['video_path'])): ?>
                                        <a href="<?= htmlspecialchars($presentationData['video_path']) ?>" target="_blank">View Current Video</a>
                                    <?php else: ?>
                                        None
                                    <?php endif; ?>
                                )</label>
                                <input class="form-control" type="file" id="videoFile" name="video_file" accept=".mp4, .webm, .ogg">
                                <small class="text-muted">Upload a new video file to replace the current one. Leave blank to keep the current file.</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleCustomType(selectElement) {
            const customDiv = document.getElementById('customTypeDiv');
            const customInput = document.getElementById('customContentType');

            if (selectElement.value === 'Other') {
                customDiv.style.display = 'block';
                customInput.setAttribute('name', 'content_type'); // Use custom input for POST
                customInput.setAttribute('required', 'required');
                selectElement.removeAttribute('name'); // Exclude select from POST
                selectElement.removeAttribute('required'); // Should not be required if custom is
            } else {
                customDiv.style.display = 'none';
                customInput.removeAttribute('name');
                customInput.removeAttribute('required');
                selectElement.setAttribute('name', 'content_type'); // Use select for POST
                selectElement.setAttribute('required', 'required');
            }
        }
    </script>
    
    <?php include "footer.php"; ?>

</body>

</html>