<?php
session_start();

// Include database configuration - استفاده از مسیر مطلق برای جلوگیری از خطا
// فرض بر این است که config.php یک سطح بالاتر از my_competitions.php قرار دارد.
include(__DIR__ . '/../config.php');

// Check for valid database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
$userId = null;
if (isset($_SESSION['user_data']['id'])) {
    $userId = $_SESSION['user_data']['id'];
} else {
    // If not logged in, redirect to login page
    header("Location: ../login.php");
    exit();
}

// --- Logic to handle competition deletion ---
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];

    // Check if the competition belongs to the current user
    $checkSql = "SELECT user_id FROM competitions WHERE id = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $deleteId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Competition belongs to the user, proceed with deletion
        $deleteSql = "DELETE FROM competitions WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $deleteId);

        if ($deleteStmt->execute()) {
            // Deletion successful, redirect to refresh the page
            header("Location: my_competitions.php");
            exit();
        } else {
            // Deletion failed, handle error
            // For example, display an error message
            $deleteError = "Error deleting competition: " . $conn->error;
        }
    } else {
        // Competition does not belong to the user or does not exist
        $deleteError = "You do not have permission to delete this competition.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Competitions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }

        .competition-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            text-align: center;
            position: relative;
        }

        .card-body-custom {
            padding: 15px;
            padding-top: 20px;
        }

        .card-title-custom {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .icon-text {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #555;
        }

        .icon-text svg {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            fill: #54595F;
        }

        .btn-custom {
            width: 80%;
            margin-top: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9">
                <h3 class="mb-4">My Competitions</h3>
                <div class="row">
                    <?php
                    // Display deletion error message if any
                    if (isset($deleteError)) {
                        echo "<div class='alert alert-danger'>$deleteError</div>";
                    }

                    // استفاده از Prepared Statement برای جلوگیری از SQL Injection
                    $sql = "SELECT * FROM competitions WHERE user_id = ? ORDER BY id DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) { ?>
                            <div class="col-md-6 mb-4">
                                <div class="competition-card p-3">
                                    <div class="card-body-custom">
                                        <h5 class="card-title-custom">
                                            <i class="fas fa-trophy text-warning"></i>
                                            <?php echo htmlspecialchars($row['competition_title']); ?>
                                        </h5>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-user"></i>
                                            Organizer: <?php echo htmlspecialchars($row['organizer_name']); ?>
                                        </p>
                                        <p class="mb-3">
                                            <?php echo nl2br(htmlspecialchars($row['competition_description'])); ?>
                                        </p>
                                        <div class="icon-text">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?php echo htmlspecialchars($row['start_date']); ?>
                                            تا
                                            <?php echo htmlspecialchars($row['end_date']); ?>
                                        </div>
                               

                                        <div class="mt-3">
                                            <a href="../edit_competition.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>

                                            

                                            <a href="my_competitions.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this competition?');">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p class='text-muted'>no competition.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            var currentPath = window.location.pathname;
            var fileName = currentPath.split('/').pop();

            $('.list-group-item-action').removeClass('active');

            $('.list-group-item-action').each(function() {
                var linkHref = $(this).attr('href');
                var linkFileName = linkHref.split('/').pop();

                if (linkFileName === fileName) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
    <?php include "footer.php"; ?>
</body>

</html>