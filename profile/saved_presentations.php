<?php

session_start();
if (!isset($_SESSION['user_data']) || !isset($_SESSION['user_data']['id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_data']['id'];

// config.php includes database connection and helpers.php
include "../config.php";

// The safe function no longer needs to be redefined here, as it's included from config.php.

$savedPresentations = []; // Array to store saved presentations (full information)

// Database connection (connection is already done in config.php, but if you need a separate connection, you can do it here too)
// To ensure $conn is accessible:
global $conn; // If $conn is defined globally in config.php

// Query to retrieve saved presentations for the current user
$sql = "
    SELECT
        sp.id AS saved_id,
        sp.saved_at,
        p.id AS presentation_id,
        p.title,
        p.description,
        p.file_path,
        p.created_at -- 'created_at' column name is used from the presentations table
    FROM
        saved_presentations sp
    JOIN
        presentations p ON sp.presentation_id = p.id
    WHERE
        sp.user_id = ?
    ORDER BY
        sp.saved_at DESC
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $savedPresentations[] = $row;
        }
    }
    $stmt->close();
} else {
    // You can display an error message to the user
    // For example:
    // echo "<div class='alert alert-danger'>Error preparing query: " . $conn->error . "</div>";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Presentations</title>

    <?php include "../includes.php"; // This includes general CSS/JS
    ?>
    <link rel="stylesheet" href="styles.css">


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>

    <?php include "header.php"; // Includes navigation header
    ?>

    <div class="container">
        <div class="row">

            <?php include "sidebar.php"; // Includes navigation sidebar
            ?>

            <div class="col-md-6">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">
                    <h4 class="mb-4">Saved Presentations</h4>

                    <?php if (empty($savedPresentations)): ?>
                        <div class="alert alert-info" role="alert">
                            You haven't saved any presentations yet.
                        </div>
                    <?php else: ?>
                        <div class="list-group">


                            <?php foreach ($savedPresentations as $presentation): ?>
                                <div class="list-group-item flex-column align-items-start mb-2 shadow-sm rounded">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <h5 class="mb-1">
                                            <a href="<?php echo safe($presentation['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">
                                                <?php echo safe($presentation['title']); ?>
                                            </a>
                                        </h5>
                                        <small class="text-muted text-end">
                                            Saved on: <?php echo date('Y-m-d H:i', strtotime($presentation['saved_at'])); ?>
                                            <?php if (isset($presentation['created_at'])): ?>
                                                <br> Uploaded on: <?php echo date('Y-m-d H:i', strtotime($presentation['created_at'])); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <p class="mb-1 mt-2"><?php echo safe($presentation['description']); ?></p>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <small class="text-muted">File Path: <?php echo safe($presentation['file_path']); ?></small>

                                        <form action="unsave_presentation.php" method="POST" onsubmit="return confirm('Are you sure you want to remove this presentation from your saved list?');">
                                            <input type="hidden" name="saved_id" value="<?php echo safe($presentation['saved_id']); ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash-alt me-1"></i> Unsave
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="col-md-3">
                <div class="optional-sidebar shadow-sm p-3 mb-5 bg-white rounded">
                    <h4>Notes</h4>
                    <p>Here you can place additional information related to saved presentations.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logic for highlighting sidebar menu item
            const menuItems = document.querySelectorAll('.list-group-item-action');
            const currentPage = 'saved_presentations.php'; // Current file name

            menuItems.forEach(item => {
                item.classList.remove('active');
                const linkHref = item.getAttribute('href');
                // Ensure linkHref is not empty
                if (linkHref) {
                    const linkFileName = linkHref.split('/').pop().split('?')[0]; // To remove GET parameters
                    if (linkFileName === currentPage) {
                        item.classList.add('active');
                    }
                }
            });
        });
    </script>
</body>

</html>