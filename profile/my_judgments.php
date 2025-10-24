<?php
session_start();

// Include database configuration - assuming config.php is one level up
include(__DIR__ . '/../config.php');

// Check if user is logged in and get email
$userEmail = null; // متغیر جدید برای ذخیره ایمیل
if (isset($_SESSION['user_data']['id']) && isset($_SESSION['user_data']['email'])) {
    $userId = $_SESSION['user_data']['id']; // ID همچنان برای کوئری‌های آینده مفید است
    $userEmail = $_SESSION['user_data']['email']; // ایمیل کاربر جاری
} else {
    // If not logged in, redirect to login page
    header("Location: ../login.php");
    exit();
}

// --- Fetch Competitions where the current user is a judge ---
$judgments = [];
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Query: Join competitions with competition_judges where the judge's email matches the current user's email
$sql = "
    SELECT 
        c.id, 
        c.competition_title, 
        c.organizer_name, 
        c.competition_description,
        c.start_date,
        c.end_date,
        cj.title AS judge_role 
    FROM competitions c
    JOIN competition_judges cj ON c.id = cj.competition_id
    WHERE cj.email = ?  -- !!! FIX: استفاده از ستون email
    ORDER BY c.end_date DESC
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $userEmail); // !!! FIX: نوع پارامتر به 's' (رشته) تغییر یافت.
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $judgments[] = $row;
        }
    }
    $stmt->close();
} else {
    $error_message = "Database query preparation failed: " . $conn->error;
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Judgments</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body { background-color: #f0f2f5; }
        .competition-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            position: relative;
        }
        .card-body-custom { padding: 15px; }
        .card-title-custom { font-size: 1.25rem; font-weight: bold; margin-bottom: 5px; }
        .icon-text { display: flex; align-items: center; margin-top: 5px; font-size: 0.9rem; color: #555; }
        .icon-text i { width: 20px; margin-right: 8px; color: #007bff; }
    </style>
</head>

<body>

    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9">
                <h3 class="mb-4"><i class="fas fa-gavel text-primary me-2"></i> My Judging Assignments</h3>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>

                <div class="row">
                    <?php if (!empty($judgments)): ?>
                        <?php foreach ($judgments as $competition): ?>
                            <div class="col-md-6 mb-4">
                                <div class="competition-card">
                                    <div class="card-body-custom">
                                        <h5 class="card-title-custom">
                                            <?= htmlspecialchars($competition['competition_title']); ?>
                                        </h5>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-user"></i>
                                            Organizer: **<?= htmlspecialchars($competition['organizer_name']); ?>**
                                        </p>
                                        <div class="icon-text">
                                            <i class="fas fa-user-tag"></i>
                                            Your Role: **<?= htmlspecialchars($competition['judge_role'] ?: 'Judge'); ?>**
                                        </div>
                                        <div class="icon-text">
                                            <i class="fas fa-calendar-alt"></i>
                                            Start: <?= htmlspecialchars($competition['start_date']); ?>
                                        </div>
                                        <div class="icon-text">
                                            <i class="fas fa-calendar-check"></i>
                                            End: <?= htmlspecialchars($competition['end_date']); ?>
                                        </div>
                                        
                                        <p class="mt-3 mb-3 text-start description-text">
                                            <?= nl2br(htmlspecialchars(substr($competition['competition_description'], 0, 100) . (strlen($competition['competition_description']) > 100 ? '...' : ''))); ?>
                                        </p>
                                        
                                        <div class="mt-3 text-center">
                                            <a href="judge_competition.php?id=<?php echo $competition['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-balance-scale"></i> Start/Continue Judging
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info" role="alert">
                                You are not currently assigned as a judge for any competition.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <script>
        $(document).ready(function() {
            var fileName = 'my_judgments.php';
            $('.list-group-item-action').removeClass('active');
            $('.list-group-item-action[href$="' + fileName + '"]').addClass('active');
        });
    </script>
    
    <?php include "footer.php"; ?>
</body>

</html>