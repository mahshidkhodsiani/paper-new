<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_data'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_data']['id']; // برای ثبت نمره در جدول competition_judgments
$userEmail = $_SESSION['user_data']['email']; // برای چک کردن دسترسی در competition_judges

include "../config.php";

$message = '';
$messageType = '';
$competitionId = $_GET['id'] ?? null;
$competitionInfo = null;
$submissions = []; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");


// -----------------------------------------------------------
// --- 1. Verify Judge and Fetch Competition Info ---
// -----------------------------------------------------------
if ($competitionId) {
    // Check if the user is a registered judge for this competition using the email column
    $sql_comp = "
        SELECT c.*, cj.title AS judge_role 
        FROM competitions c
        JOIN competition_judges cj ON c.id = cj.competition_id
        WHERE c.id = ? AND cj.email = ? -- !!! FIX: چک کردن دسترسی با email
    ";
    $stmt_comp = $conn->prepare($sql_comp);

    if ($stmt_comp) {
        $stmt_comp->bind_param("is", $competitionId, $userEmail); // !!! FIX: استفاده از ID مسابقه (int) و ایمیل کاربر (string)
        $stmt_comp->execute();
        $result_comp = $stmt_comp->get_result();

        if ($result_comp->num_rows > 0) {
            $competitionInfo = $result_comp->fetch_assoc();
        } else {
            // Not authorized to judge this competition
            header("Location: my_judgments.php?status=danger&msg=" . urlencode("You are not authorized to judge this competition."));
            exit();
        }
        $stmt_comp->close();
    }
} else {
    header("Location: my_judgments.php?status=danger&msg=" . urlencode("Invalid competition ID."));
    exit();
}


// -----------------------------------------------------------
// --- 2. Handle Submission of Judgment (POST) ---
// -----------------------------------------------------------
// این بخش نیازی به تغییر ندارد زیرا از judge_user_id (که همان $userId است) برای ثبت نمره استفاده می‌کند.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_judgment'])) {
    $uploadId = $_POST['upload_id'] ?? null; 
    $scoreValue = filter_var($_POST['score_value'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $publicComment = trim($_POST['public_comment'] ?? '');
    $privateComment = trim($_POST['private_comment'] ?? '');

    if ($uploadId && $scoreValue >= 0) {
        $sql_judgment = "
            REPLACE INTO competition_judgments 
            (competition_id, upload_id, judge_user_id, score_value, public_comment, private_comment) 
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt_judgment = $conn->prepare($sql_judgment);

        if ($stmt_judgment) {
            $stmt_judgment->bind_param("iidiss", $competitionId, $uploadId, $userId, $scoreValue, $publicComment, $privateComment);
            
            if ($stmt_judgment->execute()) {
                $message = "Judgment for upload ID $uploadId saved successfully!";
                $messageType = 'success';
            } else {
                $message = "Error saving judgment: " . $stmt_judgment->error;
                $messageType = 'danger';
            }
            $stmt_judgment->close();
        } else {
            $message = "Database query preparation failed: " . $conn->error;
            $messageType = 'danger';
        }
    } else {
        $message = "Invalid submission data or score provided.";
        $messageType = 'danger';
    }
}


// -----------------------------------------------------------
// --- 3. Fetch Uploads and Existing Judgments ---
// -----------------------------------------------------------
$sql_uploads = "
    SELECT 
        cu.id AS upload_id,
        cu.file_path,
        cu.type AS file_type,
        -- Subquery for participant name 
        (SELECT name FROM competition_participants WHERE competition_id = cu.competition_id LIMIT 1) AS participant_name,
        (SELECT email FROM competition_participants WHERE competition_id = cu.competition_id LIMIT 1) AS participant_email,
        COALESCE(cj.score_value, -1) AS judge_score, 
        cj.public_comment,
        cj.private_comment
    FROM competition_uploads cu
    LEFT JOIN competition_judgments cj ON cu.id = cj.upload_id AND cj.judge_user_id = ?
    WHERE cu.competition_id = ? 
    ORDER BY cu.id ASC
";
$stmt_uploads = $conn->prepare($sql_uploads);

if ($stmt_uploads) {
    $stmt_uploads->bind_param("ii", $userId, $competitionId);
    $stmt_uploads->execute();
    $result_uploads = $stmt_uploads->get_result();
    
    while ($row = $result_uploads->fetch_assoc()) {
        $submissions[] = $row;
    }
    $stmt_uploads->close();
} else {
    $message = "Error fetching uploads: " . $conn->error;
    $messageType = 'danger';
}


?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judge: <?= htmlspecialchars($competitionInfo['competition_title'] ?? 'Competition') ?></title>
    
    <?php // include "../includes.php"; ?> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .submission-card {
            border: 1px solid #ddd;
            border-left: 5px solid #007bff;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .judge-form-header {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
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
                    
                    <a href="my_judgments.php" class="btn btn-secondary btn-sm mb-3"><i class="fas fa-arrow-left"></i> Back to My Assignments</a>

                    <h4 class="mb-4 text-primary">Judging: **<?= htmlspecialchars($competitionInfo['competition_title']) ?>**</h4>
                    <p class="text-muted">Your role: **<?= htmlspecialchars($competitionInfo['judge_role'] ?? 'Judge') ?>**</p>
                    <hr>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($submissions)): ?>
                        <div class="alert alert-info" role="alert">
                            No uploads have been made for this competition yet.
                        </div>
                    <?php else: ?>
                        
                        <?php foreach ($submissions as $upload): ?>
                            <div class="submission-card">
                                <h5 class="submission-title">
                                    <i class="fas fa-file-alt me-2"></i> 
                                    Upload ID #<?= htmlspecialchars($upload['upload_id']) ?>: 
                                    <?= htmlspecialchars($upload['file_type'] ?? 'File') ?>
                                </h5>
                                <p class="text-muted mb-2">
                                    Participant: **<?= htmlspecialchars($upload['participant_name'] ?? 'N/A') ?>** (Email: <?= htmlspecialchars($upload['participant_email'] ?? 'N/A') ?>)
                                </p>

                                <div class="mb-3">
                                    <?php if (!empty($upload['file_path'])): ?>
                                        <a href="<?= htmlspecialchars($upload['file_path']) ?>" target="_blank" class="btn btn-sm btn-info text-white me-2">
                                            <i class="fas fa-download"></i> Download Submitted File
                                        </a>
                                        <span class="text-muted ms-3">File Type: <?= htmlspecialchars($upload['file_type']) ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="judge-form-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-balance-scale me-1"></i> Your Judgment 
                                        <?php if ($upload['judge_score'] != -1): ?>
                                            <span class="badge bg-success float-end">Saved Score: <?= htmlspecialchars($upload['judge_score']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning float-end">Pending</span>
                                        <?php endif; ?>
                                    </h6>
                                </div>
                                
                                <form action="judge_competition.php?id=<?= $competitionId ?>" method="post">
                                    <input type="hidden" name="upload_id" value="<?= $upload['upload_id'] ?>">
                                    
                                    <div class="mb-3">
                                        <label for="score_<?= $upload['upload_id'] ?>" class="form-label">Score (Max 10.00)</label>
                                        <input type="number" step="0.01" min="0" max="10" class="form-control" id="score_<?= $upload['upload_id'] ?>" name="score_value" 
                                            value="<?= $upload['judge_score'] != -1 ? htmlspecialchars($upload['judge_score']) : '' ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="public_comment_<?= $upload['upload_id'] ?>" class="form-label">Public Comment (Visible to Participant)</label>
                                        <textarea class="form-control" id="public_comment_<?= $upload['upload_id'] ?>" name="public_comment" rows="2"><?= htmlspecialchars($upload['public_comment'] ?? '') ?></textarea>
                                        <small class="text-info">This comment will be seen by the participant.</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="private_comment_<?= $upload['upload_id'] ?>" class="form-label">Private Notes (Visible to other Judges/Organizer)</label>
                                        <textarea class="form-control" id="private_comment_<?= $upload['upload_id'] ?>" name="private_comment" rows="2"><?= htmlspecialchars($upload['private_comment'] ?? '') ?></textarea>
                                        <small class="text-danger">This is only visible to the Judging panel and Organizer.</small>
                                    </div>
                                    
                                    <button type="submit" name="submit_judgment" class="btn btn-primary"><i class="fas fa-save"></i> Save Judgment</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                        
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

</body>

</html>