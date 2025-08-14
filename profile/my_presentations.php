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

// --- Handle Form Submissions (Add or Delete Operations) ---
if (isset($_POST['action_type'])) {
    // Logic for adding a new presentation
    if ($_POST['action_type'] === 'add_presentation') {
        $conn_add = new mysqli($servername, $username, $password, $dbname);
        if ($conn_add->connect_error) {
            $message = "Database connection failed for adding presentation: " . $conn_add->connect_error;
            $messageType = 'danger';
        } else {
            $conn_add->set_charset("utf8mb4");

            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $role = $_POST['role'] ?? 'Author'; // Default role
            $keywords = $_POST['keywords'] ?? '';
            $pdf_path = '';
            $video_path = '';

            // Handle PDF file upload (required)
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == UPLOAD_ERR_OK) {
                // Construct paths for saving file to server and database
                $userUploadDir = '../uploads/pdfs/' . $userId . '/';
                $localUploadDir = realpath(__DIR__ . '/../uploads/pdfs/' . $userId . '/');

                // Create user-specific upload directory if it doesn't exist
                if (!is_dir($localUploadDir)) {
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
                    } else {
                        $message = "Error uploading the PDF file.";
                        $messageType = 'danger';
                    }
                } else {
                    $message = "Invalid PDF file type. Only PDF files are allowed.";
                    $messageType = 'danger';
                }
            } else {
                $message = "PDF file is required.";
                $messageType = 'danger';
            }

            // Handle video file upload (optional)
            if (empty($message) && isset($_FILES['video_file']) && $_FILES['video_file']['error'] == UPLOAD_ERR_OK) {
                // Construct paths for saving file to server and database
                $userUploadDir = '../uploads/videos/' . $userId . '/';
                $localUploadDir = realpath(__DIR__ . '/../uploads/videos/' . $userId . '/');

                // Create user-specific upload directory if it doesn't exist
                if (!is_dir($localUploadDir)) {
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
                    } else {
                        $message = "Error uploading the video file.";
                        $messageType = 'danger';
                    }
                } else {
                    $message = "Invalid video file type. Allowed formats: MP4, WebM, OGG.";
                    $messageType = 'danger';
                }
            }

            // If no errors occurred during file upload and a file path is available, insert into database
            if (empty($message) && (!empty($pdf_path) || !empty($video_path))) {
                $sql_add = "INSERT INTO Presentations (user_id, title, description, pdf_path, video_path, role, keywords) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_add = $conn_add->prepare($sql_add);

                if ($stmt_add) {
                    $stmt_add->bind_param("issssss", $userId, $title, $description, $pdf_path, $video_path, $role, $keywords);
                    if ($stmt_add->execute()) {
                        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&msg=" . urlencode("Presentation added successfully!"));
                        exit();
                    } else {
                        $message = "Error adding presentation: " . $stmt_add->error;
                        $messageType = 'danger';
                    }
                    $stmt_add->close();
                } else {
                    $message = "Database query preparation failed: " . $conn_add->error;
                    $messageType = 'danger';
                }
            }
            $conn_add->close();
        }
    }
    // Logic for deleting an existing presentation
    elseif ($_POST['action_type'] === 'delete_presentation') {
        if (isset($_POST['presentation_id'])) {
            $presentationId = $_POST['presentation_id'];

            $conn_del = new mysqli($servername, $username, $password, $dbname);
            if ($conn_del->connect_error) {
                $message = "Database connection failed for deleting presentation: " . $conn_del->connect_error;
                $messageType = 'danger';
            } else {
                $conn_del->set_charset("utf8mb4");

                $sql_verify = "SELECT pdf_path, video_path FROM presentations WHERE id = ? AND user_id = ?";
                $stmt_verify = $conn_del->prepare($sql_verify);

                if ($stmt_verify) {
                    $stmt_verify->bind_param("ii", $presentationId, $userId);
                    $stmt_verify->execute();
                    $result_verify = $stmt_verify->get_result();

                    if ($result_verify->num_rows > 0) {
                        $row_verify = $result_verify->fetch_assoc();
                        $storedPdfPath = $row_verify['pdf_path'];
                        $storedVideoPath = $row_verify['video_path'];

                        $sql_delete = "DELETE FROM presentations WHERE id = ? AND user_id = ?";
                        $stmt_delete = $conn_del->prepare($sql_delete);

                        if ($stmt_delete) {
                            $stmt_delete->bind_param("ii", $presentationId, $userId);
                            if ($stmt_delete->execute()) {
                                // Delete files from the server
                                if (!empty($storedPdfPath) && file_exists(realpath($storedPdfPath))) {
                                    unlink(realpath($storedPdfPath));
                                }
                                if (!empty($storedVideoPath) && file_exists(realpath($storedVideoPath))) {
                                    unlink(realpath($storedVideoPath));
                                }

                                header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&msg=" . urlencode("Presentation deleted successfully!"));
                                exit();
                            } else {
                                $message = "Error deleting presentation from database: " . $stmt_delete->error;
                                $messageType = 'danger';
                            }
                            $stmt_delete->close();
                        } else {
                            $message = "Database delete query preparation failed: " . $conn_del->error;
                            $messageType = 'danger';
                        }
                    } else {
                        $message = "Presentation not found or you don't have permission to delete it.";
                        $messageType = 'danger';
                    }
                    $stmt_verify->close();
                } else {
                    $message = "Database verification query preparation failed: " . $conn_del->error;
                    $messageType = 'danger';
                }
                $conn_del->close();
            }
        } else {
            $message = "Invalid request for deletion.";
            $messageType = 'danger';
        }
    }
}

// --- Fetch Presentations for the current user (This part runs after add/delete operations) ---
$presentations = [];
$conn_fetch = new mysqli($servername, $username, $password, $dbname);
if ($conn_fetch->connect_error) {
    die("Connection failed: " . $conn_fetch->connect_error);
}
$conn_fetch->set_charset("utf8mb4");

// Prepare the SQL query to select presentations belonging to the current user
$sql_fetch_presentations = "SELECT id, title, description, pdf_path, video_path, role, keywords FROM presentations WHERE user_id = ?";
$stmt_fetch_presentations = $conn_fetch->prepare($sql_fetch_presentations);

if ($stmt_fetch_presentations) {
    $stmt_fetch_presentations->bind_param("i", $userId);
    $stmt_fetch_presentations->execute();
    $result_presentations = $stmt_fetch_presentations->get_result();

    if ($result_presentations->num_rows > 0) {
        while ($row = $result_presentations->fetch_assoc()) {
            $presentationId = $row['id'];

            // Fetch average rating and count
            $sql_avg_rating = "SELECT AVG(rating_value) AS avg_rating, COUNT(id) AS rating_count FROM ratings WHERE presentation_id = ?";
            $stmt_avg_rating = $conn_fetch->prepare($sql_avg_rating);
            if ($stmt_avg_rating) {
                $stmt_avg_rating->bind_param("i", $presentationId);
                $stmt_avg_rating->execute();
                $result_avg = $stmt_avg_rating->get_result()->fetch_assoc();
                $row['avg_rating'] = round($result_avg['avg_rating'] ?? 0, 1);
                $row['rating_count'] = $result_avg['rating_count'] ?? 0;
                $stmt_avg_rating->close();
            }

            // Fetch comments
            $comments_sql = "SELECT u.name, u.family, r.comment, r.created_at FROM ratings r JOIN users u ON r.rater_user_id = u.id WHERE r.presentation_id = ? AND r.comment IS NOT NULL AND r.comment != '' ORDER BY r.created_at DESC";
            $comments_stmt = $conn_fetch->prepare($comments_sql);
            if ($comments_stmt) {
                $comments_stmt->bind_param("i", $presentationId);
                $comments_stmt->execute();
                $comments_result = $comments_stmt->get_result();
                $row['comments'] = [];
                while ($comment_row = $comments_result->fetch_assoc()) {
                    $row['comments'][] = $comment_row;
                }
                $comments_stmt->close();
            }

            $presentations[] = $row;
        }
    }
    $stmt_fetch_presentations->close();
} else {
    $message = "Database query preparation for fetching presentations failed: " . $conn_fetch->error;
    $messageType = 'danger';
}

$conn_fetch->close();

// --- Handle messages passed via GET parameters (e.g., after a redirect) ---
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $messageType = $_GET['status'];
    $message = urldecode($_GET['msg']);
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Presentations</title>

    <?php include "../includes.php"; ?>
    <link rel="stylesheet" href="styles.css">
    <style>
        .presentations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .presentations-table th,
        .presentations-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .presentations-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row">

            <?php include "sidebar.php"; ?>

            <div class="col-md-9">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <h5 class="mt-4">Add New Presentation</h5>
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="presentationTitle" class="form-label">Presentation Title</label>
                            <input type="text" class="form-control" id="presentationTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="presentationDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="presentationDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="presentationRole" class="form-label">Your Role</label>
                            <select class="form-select" id="presentationRole" name="role" required>
                                <option value="Author">Author</option>
                                <option value="Presenter">Presenter</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="presentationKeywords" class="form-label">Keywords (e.g., AI, Machine Learning, Data Science)</label>
                            <input type="text" class="form-control" id="presentationKeywords" name="keywords">
                            <small class="text-muted">Separate keywords with commas.</small>
                        </div>
                        <div class="mb-3">
                            <label for="pdfFile" class="form-label">PDF File (Required)</label>
                            <input class="form-control" type="file" id="pdfFile" name="pdf_file" accept=".pdf" required>
                            <small class="text-muted">Only PDF files are allowed.</small>
                        </div>
                        <div class="mb-3">
                            <label for="videoFile" class="form-label">Video File (Optional)</label>
                            <input class="form-control" type="file" id="videoFile" name="video_file" accept=".mp4, .webm, .ogg">
                            <small class="text-muted">Allowed formats: MP4, WebM, OGG.</small>
                        </div>
                        <button type="submit" name="action_type" value="add_presentation" class="btn btn-success">Add Presentation</button>
                    </form>




                    <br>
                    <h4 class="mb-4">My Presentations</h4>



                    <?php if (!empty($presentations)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover presentations-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Rating</th>
                                        <th>File</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($presentations as $presentation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($presentation['title']); ?></td>
                                            <td><?php echo htmlspecialchars($presentation['description']); ?></td>
                                            <td>
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
                                                <small class="text-muted ms-2">(<?= htmlspecialchars($presentation['rating_count']) ?> votes)</small>
                                            </td>
                                            <td>
                                                <?php
                                                // Check for file existence using realpath to handle relative paths correctly
                                                $isPdfAvailable = !empty($presentation['pdf_path']) && file_exists(realpath($presentation['pdf_path']));
                                                $isVideoAvailable = !empty($presentation['video_path']) && file_exists(realpath($presentation['video_path']));
                                                ?>

                                                <?php if ($isPdfAvailable): ?>
                                                    <a href="<?php echo htmlspecialchars($presentation['pdf_path']); ?>" target="_blank">View PDF</a>
                                                <?php endif; ?>

                                                <?php if ($isVideoAvailable): ?>
                                                    <?php if ($isPdfAvailable): ?> | <?php endif; ?>
                                                    <a href="<?php echo htmlspecialchars($presentation['video_path']); ?>" target="_blank">View Video</a>
                                                <?php endif; ?>

                                                <?php if (!$isPdfAvailable && !$isVideoAvailable): ?>
                                                    File Not Available
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="presentation-actions">

                                                    <form action="" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this presentation? This action cannot be undone.');">
                                                        <input type="hidden" name="action_type" value="delete_presentation">
                                                        <input type="hidden" name="presentation_id" value="<?php echo $presentation['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php if (!empty($presentation['comments'])): ?>
                                            <tr>
                                                <td colspan="5">
                                                    <div class="card my-3">
                                                        <div class="card-header bg-primary text-white">
                                                            <h6 class="mb-0"><i class="fas fa-comments me-1"></i> User Comments</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <?php foreach ($presentation['comments'] as $comment): ?>
                                                                <div class="mb-3 border-bottom pb-2">
                                                                    <p class="mb-1">
                                                                        <strong><?= htmlspecialchars($comment['name'] . ' ' . $comment['family']) ?></strong>
                                                                        <small class="text-muted float-end"><?= date('M d, Y', strtotime($comment['created_at'])) ?></small>
                                                                    </p>
                                                                    <p class="mb-0"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info" role="alert">
                            You haven't uploaded any presentations yet.
                        </div>
                    <?php endif; ?>

                    <hr>



                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

    <?php include "footer.php"; ?>

</body>

</html>