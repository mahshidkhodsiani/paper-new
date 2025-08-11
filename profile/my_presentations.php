<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_data'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_data']['id'];
// Include the database connection file.
// Make sure this path is correct relative to the my-presentations.php file.
// For example, if my-presentations.php is in 'your_project/profile/', then config.php should be in 'your_project/'.
include "../config.php";

$message = ''; // Variable to store success/error messages
$messageType = ''; // Variable to define the type of message (e.g., 'success', 'danger')

// --- Handle Form Submissions (Add or Delete Operations) ---
// This block processes incoming POST requests based on the 'action_type' hidden field.
if (isset($_POST['action_type'])) {
    // Logic for adding a new presentation
    if ($_POST['action_type'] === 'add_presentation') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $file_path = '';

        // Establish a new database connection for this specific operation (good practice for isolation)
        $conn_add = new mysqli($servername, $username, $password, $dbname);
        if ($conn_add->connect_error) {
            $message = "Database connection failed for adding presentation: " . $conn_add->connect_error;
            $messageType = 'danger';
        } else {
            $conn_add->set_charset("utf8mb4"); // Set character set for proper handling of non-ASCII characters

            // Handle file upload
            if (isset($_FILES['presentation_file']) && $_FILES['presentation_file']['error'] == UPLOAD_ERR_OK) {
                // Define the upload directory. This path should be relative to where this script runs.
                // It dynamically includes the user ID for organized storage: uploads/pdfs/{user_id}/
                // $uploadDir = 'uploads/pdfs/' . $userId . '/';
                $uploadDir = '../uploads/pdfs/' . $userId . '/'; // این مسیر یک سطح به بالا (به ریشه paper-new) می‌رود، سپس به 'uploads'

                // Create the upload directory if it doesn't exist. 'true' enables recursive creation.
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true); // 0777 grants full permissions (adjust as needed for security)
                }

                $fileName = basename($_FILES['presentation_file']['name']); // Get original file name
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Get file extension

                // Define allowed file types
                $allowedTypes = ['pdf', 'ppt', 'pptx', 'mp4', 'webm', 'ogg'];

                if (in_array($fileExt, $allowedTypes)) {
                    // Generate a unique file name to prevent conflicts
                    $uniqueFileName = uniqid('pres_', true) . '.' . $fileExt;
                    $targetFilePath = $uploadDir . $uniqueFileName; // Full path to save the file

                    // Move the uploaded file from temporary location to the target directory
                    if (move_uploaded_file($_FILES['presentation_file']['tmp_name'], $targetFilePath)) {
                        $file_path = $targetFilePath; // Store this relative path in the database
                    } else {
                        $message = "Error uploading file.";
                        $messageType = 'danger';
                    }
                } else {
                    $message = "Invalid file type. Allowed formats: PDF, PPT, PPTX, MP4, WebM, OGG.";
                    $messageType = 'danger';
                }
            } else {
                $message = "No file uploaded or an upload error occurred.";
                $messageType = 'danger';
            }

            // If no errors occurred during file upload and a file path is available, insert into database
            if (empty($message) && !empty($file_path)) {
                $sql_add = "INSERT INTO Presentations (user_id, title, description, file_path) VALUES (?, ?, ?, ?)";
                $stmt_add = $conn_add->prepare($sql_add);

                if ($stmt_add) {
                    // Bind parameters to the prepared statement
                    $stmt_add->bind_param("isss", $userId, $title, $description, $file_path);
                    if ($stmt_add->execute()) {
                        // Success: Redirect to the same page to clear POST data and display a success message
                        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&msg=" . urlencode("Presentation added successfully!"));
                        exit();
                    } else {
                        $message = "Error adding presentation: " . $stmt_add->error;
                        $messageType = 'danger';
                    }
                    $stmt_add->close(); // Close the statement
                } else {
                    $message = "Database query preparation failed: " . $conn_add->error;
                    $messageType = 'danger';
                }
            }
            $conn_add->close(); // Close the database connection for adding
        }
    }
    // Logic for deleting an existing presentation
    elseif ($_POST['action_type'] === 'delete_presentation') {
        if (isset($_POST['presentation_id'])) {
            $presentationId = $_POST['presentation_id'];

            // Establish a new database connection for deletion
            $conn_del = new mysqli($servername, $username, $password, $dbname);
            if ($conn_del->connect_error) {
                $message = "Database connection failed for deleting presentation: " . $conn_del->connect_error;
                $messageType = 'danger';
            } else {
                $conn_del->set_charset("utf8mb4");

                // Step 1: Verify ownership and retrieve the file path from the database (for security)
                $sql_verify = "SELECT file_path FROM presentations WHERE id = ? AND user_id = ?";
                $stmt_verify = $conn_del->prepare($sql_verify);

                if ($stmt_verify) {
                    $stmt_verify->bind_param("ii", $presentationId, $userId);
                    $stmt_verify->execute();
                    $result_verify = $stmt_verify->get_result();

                    if ($result_verify->num_rows > 0) {
                        $row_verify = $result_verify->fetch_assoc();
                        $storedFilePath = $row_verify['file_path']; // Get the actual path from DB

                        // Step 2: Delete the record from the database
                        $sql_delete = "DELETE FROM presentations WHERE id = ? AND user_id = ?";
                        $stmt_delete = $conn_del->prepare($sql_delete);

                        if ($stmt_delete) {
                            $stmt_delete->bind_param("ii", $presentationId, $userId);
                            if ($stmt_delete->execute()) {
                                // Step 3: Delete the actual file from the server
                                if (!empty($storedFilePath) && file_exists($storedFilePath)) {
                                    // Crucial Security Check: Ensure the file being deleted is within the expected
                                    // user-specific upload directory to prevent directory traversal attacks.
                                    $expectedUploadPrefix = 'uploads/pdfs/' . $userId . '/';
                                    if (strpos($storedFilePath, $expectedUploadPrefix) === 0) {
                                        unlink($storedFilePath); // Delete the file from the filesystem
                                    } else {
                                        // Log a warning if the file path is suspicious, but proceed with DB deletion
                                        error_log("Security Alert: Attempted to delete file outside expected upload directory. Path: " . $storedFilePath);
                                    }
                                }
                                // Success: Redirect to the same page to clear POST data and display success message
                                header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&msg=" . urlencode("Presentation deleted successfully!"));
                                exit();
                            } else {
                                $message = "Error deleting presentation from database: " . $stmt_delete->error;
                                $messageType = 'danger';
                            }
                            $stmt_delete->close(); // Close the delete statement
                        } else {
                            $message = "Database delete query preparation failed: " . $conn_del->error;
                            $messageType = 'danger';
                        }
                    } else {
                        $message = "Presentation not found or you don't have permission to delete it.";
                        $messageType = 'danger';
                    }
                    $stmt_verify->close(); // Close the verification statement
                } else {
                    $message = "Database verification query preparation failed: " . $conn_del->error;
                    $messageType = 'danger';
                }
                $conn_del->close(); // Close the database connection for deletion
            }
        } else {
            $message = "Invalid request for deletion.";
            $messageType = 'danger';
        }
    }
}
// --- End of Handle Form Submissions ---


// --- Fetch Presentations for the current user (This part runs after add/delete operations) ---
$presentations = [];
// Open a new database connection specifically for fetching data.
// This ensures cleanliness and avoids issues with previous connections (add/delete).
$conn_fetch = new mysqli($servername, $username, $password, $dbname);
if ($conn_fetch->connect_error) {
    die("Connection failed: " . $conn_fetch->connect_error); // Fatal error if fetch connection fails
}
$conn_fetch->set_charset("utf8mb4");

// Prepare the SQL query to select presentations belonging to the current user
// Assumes 'Presentations' table has 'id', 'title', 'description', and 'file_path' columns
$sql_fetch_presentations = "SELECT id, title, description, file_path FROM presentations WHERE user_id = ?";
$stmt_fetch_presentations = $conn_fetch->prepare($sql_fetch_presentations);

if ($stmt_fetch_presentations) {
    $stmt_fetch_presentations->bind_param("i", $userId); // Bind the user ID
    $stmt_fetch_presentations->execute();
    $result_presentations = $stmt_fetch_presentations->get_result();

    if ($result_presentations->num_rows > 0) {
        // Fetch all presentations and store them in an array
        while ($row = $result_presentations->fetch_assoc()) {
            $presentations[] = $row;
        }
    }
    $stmt_fetch_presentations->close(); // Close the fetch statement
} else {
    $message = "Database query preparation for fetching presentations failed: " . $conn_fetch->error;
    $messageType = 'danger';
}

$conn_fetch->close(); // Close the database connection for fetching

// --- Handle messages passed via GET parameters (e.g., after a redirect) ---
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $messageType = $_GET['status'];
    $message = urldecode($_GET['msg']); // Decode URL-encoded messages
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Presentations</title>

    <?php
    // Include shared CSS/JS files like Bootstrap and Font Awesome.
    // Ensure this path is correct relative to the current file.
    include "../includes.php";
    ?>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Custom CSS for presentation cards/table layout */
        .presentation-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .presentation-card h6 {
            margin-bottom: 10px;
            color: #333;
        }

        .presentation-card p {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }

        .presentation-actions a {
            margin-right: 10px;
            /* Spacing between action buttons */
        }

        /* Styling for the presentations table */
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
            /* Aligned left for LTR languages */
        }

        .presentations-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>

</head>

<body>

    <?php
    // Include the page header component.
    include "header.php"; // Make sure this path is correct
    ?>

    <div class="container mt-4">
        <div class="row">

            <?php
            // Include the sidebar component.
            include "sidebar.php"; // Make sure this path is correct
            ?>

            <div class="col-md-9">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">
                    <h4 class="mb-4">My Presentations</h4>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($presentations)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover presentations-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
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
                                                <?php if (!empty($presentation['file_path']) && file_exists($presentation['file_path'])): ?>
                                                    <a href="<?php echo htmlspecialchars($presentation['file_path']); ?>" target="_blank">View File</a>
                                                <?php else: ?>
                                                    File Not Available
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="presentation-actions">
                                                    <a href="edit-presentation.php?id=<?php echo $presentation['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                                    <form action="" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this presentation? This action cannot be undone.');">
                                                        <input type="hidden" name="action_type" value="delete_presentation">
                                                        <input type="hidden" name="presentation_id" value="<?php echo $presentation['id']; ?>">
                                                        <input type="hidden" name="file_path" value="<?php echo htmlspecialchars($presentation['file_path']); ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info" role="alert">
                            You haven't uploaded any presentations yet.
                        </div>
                    <?php endif; ?>

                    ---
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
                            <label for="presentationFile" class="form-label">Presentation File (PDF, PPTX, MP4, etc.)</label>
                            <input class="form-control" type="file" id="presentationFile" name="presentation_file" accept=".pdf,.ppt,.pptx,.mp4,.webm,.ogg" required>
                            <small class="text-muted">Allowed formats: PDF, PPT, PPTX, MP4, WebM, OGG</small>
                        </div>
                        <button type="submit" name="action_type" value="add_presentation" class="btn btn-success">Add Presentation</button>
                    </form>

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
                // Remove URL parameters after a delay to clean up the URL bar
                setTimeout(() => {
                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }, 3000); // Clears after 3 seconds

                // Automatically hide the Bootstrap alert message after a delay
                const alertElement = document.querySelector('.alert');
                if (alertElement) {
                    setTimeout(() => {
                        const bsAlert = new bootstrap.Alert(alertElement); // Initialize Bootstrap Alert
                        bsAlert.close(); // Close the alert
                    }, 3000); // Hides after 3 seconds
                }
            }
        });
    </script>

    <?php include "footer.php"; ?>

</body>

</html>