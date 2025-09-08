<?php
session_start();

// Include database configuration
include(__DIR__ . '/config.php');

// Check for valid database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
$userId = null;
if (isset($_SESSION['user_data']['id'])) {
    $userId = $_SESSION['user_data']['id'];
} else {
    header("Location: ../login.php");
    exit();
}

// Check if competition ID is provided and is a valid number
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my_competitions.php");
    exit();
}

$competitionId = (int)$_GET['id'];
$competitionData = [];
$judgeData = [];
$participantData = [];
$awardData = [];
$uploadsData = [];


// Fetch existing competition data to populate the form
$fetchCompetitionSql = "SELECT * FROM competitions WHERE id = ? AND user_id = ?";
$fetchCompetitionStmt = $conn->prepare($fetchCompetitionSql);
$fetchCompetitionStmt->bind_param("ii", $competitionId, $userId);
$fetchCompetitionStmt->execute();
$competitionResult = $fetchCompetitionStmt->get_result();

if ($competitionResult->num_rows > 0) {
    $competitionData = $competitionResult->fetch_assoc();
} else {
    header("Location: my_competitions.php");
    exit();
}

// Fetch judges, participants, awards, and uploads
$fetchJudgesSql = "SELECT * FROM competition_judges WHERE competition_id = ?";
$fetchJudgesStmt = $conn->prepare($fetchJudgesSql);
$fetchJudgesStmt->bind_param("i", $competitionId);
$fetchJudgesStmt->execute();
$judgeResult = $fetchJudgesStmt->get_result();
while ($row = $judgeResult->fetch_assoc()) {
    $judgeData[] = $row;
}

$fetchParticipantsSql = "SELECT * FROM competition_participants WHERE competition_id = ?";
$fetchParticipantsStmt = $conn->prepare($fetchParticipantsSql);
$fetchParticipantsStmt->bind_param("i", $competitionId);
$fetchParticipantsStmt->execute();
$participantResult = $fetchParticipantsStmt->get_result();
while ($row = $participantResult->fetch_assoc()) {
    $participantData[] = $row;
}

$fetchAwardsSql = "SELECT * FROM competition_awards WHERE competition_id = ?";
$fetchAwardsStmt = $conn->prepare($fetchAwardsSql);
$fetchAwardsStmt->bind_param("i", $competitionId);
$fetchAwardsStmt->execute();
$awardResult = $fetchAwardsStmt->get_result();
while ($row = $awardResult->fetch_assoc()) {
    $awardData[] = $row;
}

$fetchUploadsSql = "SELECT * FROM competition_uploads WHERE competition_id = ?";
$fetchUploadsStmt = $conn->prepare($fetchUploadsSql);
$fetchUploadsStmt->bind_param("i", $competitionId);
$fetchUploadsStmt->execute();
$uploadsResult = $fetchUploadsStmt->get_result();
while ($row = $uploadsResult->fetch_assoc()) {
    $uploadsData[$row['type']] = $row;
}

// Logic to handle form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $organizerName = $_POST['organizer'];
    $organizerEmail = $_POST['organizerEmail'];
    $competitionTitle = $_POST['competitionTitle'];
    $competitionDescription = $_POST['competitionDescription'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $timezone = $_POST['timezone'];
    $roomLink = $_POST['roomLink'];
    $sessionTrack = $_POST['sessionTrack'];
    $presentationDuration = (int)$_POST['presentationDuration'];
    $bufferDuration = (int)$_POST['bufferDuration'];
    $presentationOrder = $_POST['presentationOrder'];
    $competitionVisibility = $_POST['competitionVisibility'];
    $participationAccess = $_POST['participationAccess'];
    $votingSystem = $_POST['votingSystem'];
    $maxVotesPerParticipant = (int)$_POST['maxVotes'];
    $resultsVisibility = $_POST['resultsVisibility'];
    $maxParticipants = (int)$_POST['maxParticipants'];
    $maxSubmissionsPerParticipant = (int)$_POST['maxSubmissions'];
    $judgeNotes = $_POST['judgeNotes'];
    $submissionType = $_POST['submissionType'];
    $maxFileSize = (int)$_POST['maxFileSize'];
    // $allowedFormats = isset($_POST['allowedFormats']) ? implode(',', $_POST['allowedFormats']) : '';
    $allowedFormats = $_POST['allowedFormats'];
    $submissionGuidelines = $_POST['submissionGuidelines'];
    $customFieldsJson = $_POST['customFields'];
    $slideDeckRequired = isset($_POST['slideDeckRequired']) ? 1 : 0;
    $abstractTextField = isset($_POST['abstractText']) ? 1 : 0;
    $posterImageOptional = isset($_POST['posterImage']) ? 1 : 0;
    $consentRecording = isset($_POST['consentRecording']) ? 1 : 0;
    $consentPublicDisplay = isset($_POST['consentPublicDisplay']) ? 1 : 0;
    $scoringRubric = $_POST['scoringRubric'];
    $scoreWeightingSystem = $_POST['scoreWeighting'];
    $customCss = $_POST['customCss'];
    $redirectUrl = $_POST['redirectUrl'];
    $enableComments = isset($_POST['enableComments']) ? 1 : 0;
    $moderateSubmissions = isset($_POST['moderateSubmissions']) ? 1 : 0;
    $enableBlindReview = isset($_POST['enableBlindReview']) ? 1 : 0;
    $requireConflict = isset($_POST['requireConflict']) ? 1 : 0;
    $lateSubmissionGracePeriod = (int)$_POST['lateSubmissionGrace'];
    $judgingVisibility = $_POST['judgingVisibility'];
    $webhookUrl = $_POST['webhookUrl'];
    // $exportOptions = isset($_POST['exportOptions']) ? implode(',', $_POST['exportOptions']) : '';
    $exportOptions = $_POST['exportOptions'];
    $perCriterionScoreScale = $_POST['scoreScale'];
    $tieBreakPolicy = $_POST['tieBreakPolicy'];
    $qaTime = (int)$_POST['qaTime'];
    $leaderboardVisibility = $_POST['leaderboardVisibility'];
    $notifyNewSubmission = isset($_POST['notifyNewSubmission']) ? 1 : 0;
    $sendSchedule = isset($_POST['sendSchedule']) ? 1 : 0;
    $emailWinners = isset($_POST['emailWinners']) ? 1 : 0;
    $resultsPublishDate = $_POST['resultsPublishDate'];
    $winnerEmailTemplate = $_POST['winnerEmailTemplate'];
    $competitionCategory = $_POST['competitionCategory'];

    // Update main competitions table
    $updateSql = "UPDATE competitions SET
        organizer_name = ?, organizer_email = ?, competition_title = ?, competition_description = ?, start_date = ?, end_date = ?, timezone = ?,
        room_link = ?, session_track = ?, presentation_duration = ?, buffer_duration = ?, presentation_order = ?, competition_visibility = ?,
        participation_access = ?, voting_system = ?, max_votes_per_participant = ?, results_visibility = ?, max_participants = ?,
        max_submissions_per_participant = ?, judge_notes = ?, submission_type = ?, max_file_size = ?, allowed_formats = ?,
        submission_guidelines = ?, custom_fields_json = ?, slide_deck_required = ?, abstract_text_field = ?, poster_image_optional = ?,
        consent_recording = ?, consent_public_display = ?, scoring_rubric = ?, score_weighting_system = ?, custom_css = ?, redirect_url = ?,
        enable_comments = ?, moderate_submissions = ?, enable_blind_review = ?, require_conflict = ?, late_submission_grace_period = ?,
        judging_visibility = ?, webhook_url = ?, export_options = ?, per_criterion_score_scale = ?, tie_break_policy = ?, qa_time = ?,
        leaderboard_visibility = ?, notify_new_submission = ?, send_schedule = ?, email_winners = ?, results_publish_date = ?,
        winner_email_template = ?, competition_category = ?
            WHERE id = ? AND user_id = ?";

    $updateStmt = $conn->prepare($updateSql);

    $updateStmt->bind_param(
        "ssssssssssssssssssssssisssiiiisssisiiisssiiiiissssssii", // یک 'i' به انتهای رشته اضافه شده است.
        $organizerName,
        $organizerEmail,
        $competitionTitle,
        $competitionDescription,
        $startDate,
        $endDate,
        $timezone,
        $roomLink,
        $sessionTrack,
        $presentationDuration,
        $bufferDuration,
        $presentationOrder,
        $competitionVisibility,
        $participationAccess,
        $votingSystem,
        $maxVotesPerParticipant,
        $resultsVisibility,
        $maxParticipants,
        $maxSubmissionsPerParticipant,
        $judgeNotes,
        $submissionType,
        $maxFileSize,
        $allowedFormats,
        $submissionGuidelines,
        $customFieldsJson,
        $slideDeckRequired,
        $abstractTextField,
        $posterImageOptional,
        $consentRecording,
        $consentPublicDisplay,
        $scoringRubric,
        $scoreWeightingSystem,
        $customCss,
        $redirectUrl,
        $enableComments,
        $moderateSubmissions,
        $enableBlindReview,
        $requireConflict,
        $lateSubmissionGracePeriod,
        $judgingVisibility,
        $webhookUrl,
        $exportOptions,
        $perCriterionScoreScale,
        $tieBreakPolicy,
        $qaTime,
        $leaderboardVisibility,
        $notifyNewSubmission,
        $sendSchedule,
        $emailWinners,
        $resultsPublishDate,
        $winnerEmailTemplate,
        $competitionCategory,
        $competitionId,
        $userId
    );

    $updateStmt->execute();

    // Update judges
    $deleteJudgesSql = "DELETE FROM competition_judges WHERE competition_id = ?";
    $deleteJudgesStmt = $conn->prepare($deleteJudgesSql);
    $deleteJudgesStmt->bind_param("i", $competitionId);
    $deleteJudgesStmt->execute();

    if (!empty($_POST['judgeName'])) {
        $insertJudgeSql = "INSERT INTO competition_judges (competition_id, name, email, title, linkedin_url) VALUES (?, ?, ?, ?, ?)";
        $insertJudgeStmt = $conn->prepare($insertJudgeSql);
        foreach ($_POST['judgeName'] as $index => $name) {
            $judgeName = $_POST['judgeName'][$index];
            $judgeEmail = $_POST['judgeEmail'][$index];
            $judgeTitle = $_POST['judgeTitle'][$index];
            $judgeLinkedIn = $_POST['judgeLinkedIn'][$index];
            $insertJudgeStmt->bind_param("issss", $competitionId, $judgeName, $judgeEmail, $judgeTitle, $judgeLinkedIn);
            $insertJudgeStmt->execute();
        }
    }

    // Update participants
    $deleteParticipantsSql = "DELETE FROM competition_participants WHERE competition_id = ?";
    $deleteParticipantsStmt = $conn->prepare($deleteParticipantsSql);
    $deleteParticipantsStmt->bind_param("i", $competitionId);
    $deleteParticipantsStmt->execute();

    if (!empty($_POST['participantName'])) {
        $insertParticipantSql = "INSERT INTO competition_participants (competition_id, name, email) VALUES (?, ?, ?)";
        $insertParticipantStmt = $conn->prepare($insertParticipantSql);
        foreach ($_POST['participantName'] as $index => $name) {
            $participantName = $_POST['participantName'][$index];
            $participantEmail = $_POST['participantEmail'][$index];
            $insertParticipantStmt->bind_param("iss", $competitionId, $participantName, $participantEmail);
            $insertParticipantStmt->execute();
        }
    }

    // Update awards
    $deleteAwardsSql = "DELETE FROM competition_awards WHERE competition_id = ?";
    $deleteAwardsStmt = $conn->prepare($deleteAwardsSql);
    $deleteAwardsStmt->bind_param("i", $competitionId);
    $deleteAwardsStmt->execute();

    if (!empty($_POST['awardName'])) {
        $insertAwardSql = "INSERT INTO competition_awards (competition_id, award_name, award_value, number_of_winners, per_winner_prize) VALUES (?, ?, ?, ?, ?)";
        $insertAwardStmt = $conn->prepare($insertAwardSql);
        foreach ($_POST['awardName'] as $index => $name) {
            $awardName = $_POST['awardName'][$index];
            $awardValue = $_POST['awardValue'][$index];
            $numberOfWinners = (int)$_POST['numberOfWinners'][$index];
            $perWinnerPrize = $_POST['perWinnerPrize'][$index];
            $insertAwardStmt->bind_param("issis", $competitionId, $awardName, $awardValue, $numberOfWinners, $perWinnerPrize);
            $insertAwardStmt->execute();
        }
    }

    // Redirect to my_competitions with a success message
    header("Location: profile/my_competitions.php?status=updated");
    exit();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Edit Competition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <style>
        .tab-content {
            margin-top: 1rem;
        }

        .file-upload-info {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h3 class="mb-3">Edit Competition</h3>
                <form id="competitionForm" novalidate action="edit_competition.php?id=<?php echo $competitionId; ?>" method="POST" enctype="multipart/form-data">
                    <input name="competitionId" type="hidden" value="<?php echo $competitionId; ?>">
                    <input name="userID" type="hidden" value="<?php echo htmlspecialchars($userId); ?>">

                    <ul class="nav nav-tabs" id="mainTabs" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-1-tab" data-bs-toggle="tab" data-bs-target="#tab-1" type="button" role="tab" aria-controls="tab-1" aria-selected="true">1. Basic info</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-2-tab" data-bs-toggle="tab" data-bs-target="#tab-2" type="button" role="tab" aria-controls="tab-2" aria-selected="false">2. Details</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-3-tab" data-bs-toggle="tab" data-bs-target="#tab-3" type="button" role="tab" aria-controls="tab-3" aria-selected="false">3. Privacy & Voting</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-4-tab" data-bs-toggle="tab" data-bs-target="#tab-4" type="button" role="tab" aria-controls="tab-4" aria-selected="false">4. Submission</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-5-tab" data-bs-toggle="tab" data-bs-target="#tab-5" type="button" role="tab" aria-controls="tab-5" aria-selected="false">5. Judging</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-6-tab" data-bs-toggle="tab" data-bs-target="#tab-6" type="button" role="tab" aria-controls="tab-6" aria-selected="false">6. Communication & Design</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-7-tab" data-bs-toggle="tab" data-bs-target="#tab-7" type="button" role="tab" aria-controls="tab-7" aria-selected="false">7. Judges</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-8-tab" data-bs-toggle="tab" data-bs-target="#tab-8" type="button" role="tab" aria-controls="tab-8" aria-selected="false">8. Participants</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-9-tab" data-bs-toggle="tab" data-bs-target="#tab-9" type="button" role="tab" aria-controls="tab-9" aria-selected="false">9. Awards</button></li>
                    </ul>

                    <div class="tab-content" id="mainTabsContent">
                        <div class="tab-pane fade show active" id="tab-1" role="tabpanel" aria-labelledby="tab-1-tab">
                            <div class="card shadow-sm p-3">
                                <h5 class="card-title">Organizer & Branding</h5>
                                <div class="mb-3">
                                    <label for="organizer" class="form-label">Organization *</label>
                                    <input type="text" class="form-control" id="organizer" name="organizer" value="<?php echo htmlspecialchars($competitionData['organizer_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="organizerEmail" class="form-label">Organizer Contact Email *</label>
                                    <input type="email" class="form-control" id="organizerEmail" name="organizerEmail" value="<?php echo htmlspecialchars($competitionData['organizer_email'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="logo" class="form-label">Organization Logo</label>
                                    <input type="file" class="form-control" id="logo" name="logo">
                                    <?php if (isset($uploadsData['logo'])): ?>
                                        <div class="file-upload-info mt-2">Current file: <a href="<?php echo htmlspecialchars($uploadsData['logo']['file_path']); ?>" target="_blank"><?php echo basename($uploadsData['logo']['file_path']); ?></a></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="competitionCategory" class="form-label">Category</label>
                                    <select class="form-select" id="competitionCategory" name="competitionCategory">
                                        <option value="">Select a category</option>
                                        <?php
                                        $categories = ["Hackathon", "Pitch Competition", "Science Fair", "Design Contest", "Video Game"];
                                        foreach ($categories as $category) {
                                            $selected = ($competitionData['competition_category'] ?? '') === $category ? 'selected' : '';
                                            echo "<option value=\"$category\" $selected>$category</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-2" role="tabpanel" aria-labelledby="tab-2-tab">
                            <div class="card shadow-sm p-3">
                                <h5 class="card-title">Competition Details</h5>
                                <div class="mb-3">
                                    <label for="competitionTitle" class="form-label">Competition Title *</label>
                                    <input type="text" class="form-control" id="competitionTitle" name="competitionTitle" value="<?php echo htmlspecialchars($competitionData['competition_title'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="competitionDescription" class="form-label">Description *</label>
                                    <textarea class="form-control" id="competitionDescription" name="competitionDescription" rows="3" required><?php echo htmlspecialchars($competitionData['competition_description'] ?? ''); ?></textarea>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="startDate" class="form-label">Start Date *</label>
                                        <input type="date" class="form-control" id="startDate" name="startDate" value="<?php echo htmlspecialchars($competitionData['start_date'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="endDate" class="form-label">End Date *</label>
                                        <input type="date" class="form-control" id="endDate" name="endDate" value="<?php echo htmlspecialchars($competitionData['end_date'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <input type="text" class="form-control" id="timezone" name="timezone" value="<?php echo htmlspecialchars($competitionData['timezone'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="roomLink" class="form-label">Virtual Room Link</label>
                                    <input type="url" class="form-control" id="roomLink" name="roomLink" value="<?php echo htmlspecialchars($competitionData['room_link'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="sessionTrack" class="form-label">Session Track</label>
                                    <input type="text" class="form-control" id="sessionTrack" name="sessionTrack" value="<?php echo htmlspecialchars($competitionData['session_track'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="presentationDuration" class="form-label">Presentation Duration (minutes)</label>
                                    <input type="number" class="form-control" id="presentationDuration" name="presentationDuration" value="<?php echo htmlspecialchars($competitionData['presentation_duration'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="bufferDuration" class="form-label">Buffer Duration (minutes)</label>
                                    <input type="number" class="form-control" id="bufferDuration" name="bufferDuration" value="<?php echo htmlspecialchars($competitionData['buffer_duration'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="presentationOrder" class="form-label">Presentation Order</label>
                                    <select class="form-select" id="presentationOrder" name="presentationOrder">
                                        <?php
                                        $options = ["Random", "Manual", "By Submission Time"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['presentation_order'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-3" role="tabpanel" aria-labelledby="tab-3-tab">
                            <div class="card shadow-sm p-3">
                                <h5 class="card-title">Privacy & Voting</h5>
                                <div class="mb-3">
                                    <label for="competitionVisibility" class="form-label">Competition Visibility *</label>
                                    <select class="form-select" id="competitionVisibility" name="competitionVisibility" required>
                                        <?php
                                        $options = ["Public", "Unlisted", "Private"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['competition_visibility'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="participationAccess" class="form-label">Participation Access *</label>
                                    <select class="form-select" id="participationAccess" name="participationAccess" required>
                                        <?php
                                        $options = ["Public", "By Invitation Only"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['participation_access'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="votingSystem" class="form-label">Voting System *</label>
                                    <select class="form-select" id="votingSystem" name="votingSystem" required>
                                        <?php
                                        $options = ["Public", "By Invitation Only", "Disabled"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['voting_system'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="maxVotes" class="form-label">Max Votes Per Participant</label>
                                    <input type="number" class="form-control" id="maxVotes" name="maxVotes" value="<?php echo htmlspecialchars($competitionData['max_votes_per_participant'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="resultsVisibility" class="form-label">Results Visibility</label>
                                    <select class="form-select" id="resultsVisibility" name="resultsVisibility">
                                        <?php
                                        $options = ["Public", "Judges Only", "Private"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['results_visibility'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-4" role="tabpanel" aria-labelledby="tab-4-tab">
                            <div class="card shadow-sm p-3">
                                <h5 class="card-title">Submission Details</h5>
                                <div class="mb-3">
                                    <label for="maxParticipants" class="form-label">Max Participants</label>
                                    <input type="number" class="form-control" id="maxParticipants" name="maxParticipants" value="<?php echo htmlspecialchars($competitionData['max_participants'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="maxSubmissions" class="form-label">Max Submissions Per Participant</label>
                                    <input type="number" class="form-control" id="maxSubmissions" name="maxSubmissions" value="<?php echo htmlspecialchars($competitionData['max_submissions_per_participant'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="submissionType" class="form-label">Submission Type *</label>
                                    <select class="form-select" id="submissionType" name="submissionType" required>
                                        <?php
                                        $options = ["Text", "File Upload", "URL"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['submission_type'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="maxFileSize" class="form-label">Max File Size (KB)</label>
                                    <input type="number" class="form-control" id="maxFileSize" name="maxFileSize" value="<?php echo htmlspecialchars($competitionData['max_file_size'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="allowedFormats" class="form-label">Allowed File Formats</label>
                                    <input type="text" class="form-control" id="allowedFormats" name="allowedFormats" value="<?php echo htmlspecialchars($competitionData['allowed_formats'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="submissionGuidelines" class="form-label">Submission Guidelines</label>
                                    <textarea class="form-control" id="submissionGuidelines" name="submissionGuidelines" rows="3"><?php echo htmlspecialchars($competitionData['submission_guidelines'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="customFields" class="form-label">Custom Fields (JSON)</label>
                                    <textarea class="form-control" id="customFields" name="customFields" rows="3"><?php echo htmlspecialchars($competitionData['custom_fields_json'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="slideDeckRequired" name="slideDeckRequired" <?php echo ($competitionData['slide_deck_required'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="slideDeckRequired">Slide Deck Required</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="abstractText" name="abstractText" <?php echo ($competitionData['abstract_text_field'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="abstractText">Abstract Text Field</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="posterImage" name="posterImage" <?php echo ($competitionData['poster_image_optional'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="posterImage">Poster Image Optional</label>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-5" role="tabpanel" aria-labelledby="tab-5-tab">
                            <div class="card shadow-sm p-3">
                                <h5 class="card-title">Judging</h5>
                                <div class="mb-3">
                                    <label for="scoringRubric" class="form-label">Scoring Rubric</label>
                                    <textarea class="form-control" id="scoringRubric" name="scoringRubric" rows="3"><?php echo htmlspecialchars($competitionData['scoring_rubric'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="scoreWeighting" class="form-label">Score Weighting System</label>
                                    <select class="form-select" id="scoreWeighting" name="scoreWeighting">
                                        <?php
                                        $options = ["Equal", "Custom"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['score_weighting_system'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="judgeNotes" class="form-label">Judge Notes</label>
                                    <textarea class="form-control" id="judgeNotes" name="judgeNotes" rows="3"><?php echo htmlspecialchars($competitionData['judge_notes'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="enableBlindReview" name="enableBlindReview" <?php echo ($competitionData['enable_blind_review'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enableBlindReview">Enable Blind Review</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="requireConflict" name="requireConflict" <?php echo ($competitionData['require_conflict'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="requireConflict">Require Conflict of Interest</label>
                                </div>
                                <div class="mb-3">
                                    <label for="lateSubmissionGrace" class="form-label">Late Submission Grace Period (minutes)</label>
                                    <input type="number" class="form-control" id="lateSubmissionGrace" name="lateSubmissionGrace" value="<?php echo htmlspecialchars($competitionData['late_submission_grace_period'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="judgingVisibility" class="form-label">Judging Visibility</label>
                                    <select class="form-select" id="judgingVisibility" name="judgingVisibility">
                                        <?php
                                        $options = ["Public", "Judges Only", "Private"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['judging_visibility'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="webhookUrl" class="form-label">Webhook URL</label>
                                    <input type="url" class="form-control" id="webhookUrl" name="webhookUrl" value="<?php echo htmlspecialchars($competitionData['webhook_url'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="exportOptions" class="form-label">Export Options</label>
                                    <input type="text" class="form-control" id="exportOptions" name="exportOptions" value="<?php echo htmlspecialchars($competitionData['export_options'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="scoreScale" class="form-label">Per-Criterion Score Scale</label>
                                    <select class="form-select" id="scoreScale" name="scoreScale">
                                        <?php
                                        $options = ["1-5", "1-10", "1-100"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['per_criterion_score_scale'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tieBreakPolicy" class="form-label">Tie-Break Policy</label>
                                    <select class="form-select" id="tieBreakPolicy" name="tieBreakPolicy">
                                        <?php
                                        $options = ["Random", "Manual"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['tie_break_policy'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="qaTime" class="form-label">Q&A Time (minutes)</label>
                                    <input type="number" class="form-control" id="qaTime" name="qaTime" value="<?php echo htmlspecialchars($competitionData['qa_time'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="leaderboardVisibility" class="form-label">Leaderboard Visibility</label>
                                    <select class="form-select" id="leaderboardVisibility" name="leaderboardVisibility">
                                        <?php
                                        $options = ["Public", "Judges Only", "Private"];
                                        foreach ($options as $option) {
                                            $selected = ($competitionData['leaderboard_visibility'] ?? '') === $option ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-6" role="tabpanel" aria-labelledby="tab-6-tab">
                            <div class="card shadow-sm p-3">
                                <h5 class="card-title">Communication & Design</h5>
                                <div class="mb-3">
                                    <label for="customCss" class="form-label">Custom CSS</label>
                                    <textarea class="form-control" id="customCss" name="customCss" rows="3"><?php echo htmlspecialchars($competitionData['custom_css'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="redirectUrl" class="form-label">Redirect URL</label>
                                    <input type="url" class="form-control" id="redirectUrl" name="redirectUrl" value="<?php echo htmlspecialchars($competitionData['redirect_url'] ?? ''); ?>">
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="enableComments" name="enableComments" <?php echo ($competitionData['enable_comments'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enableComments">Enable Comments</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="moderateSubmissions" name="moderateSubmissions" <?php echo ($competitionData['moderate_submissions'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="moderateSubmissions">Moderate Submissions</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="notifyNewSubmission" name="notifyNewSubmission" <?php echo ($competitionData['notify_new_submission'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notifyNewSubmission">Notify New Submission</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="sendSchedule" name="sendSchedule" <?php echo ($competitionData['send_schedule'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="sendSchedule">Send Schedule</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="emailWinners" name="emailWinners" <?php echo ($competitionData['email_winners'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="emailWinners">Email Winners</label>
                                </div>
                                <div class="mb-3">
                                    <label for="resultsPublishDate" class="form-label">Results Publish Date</label>
                                    <input type="date" class="form-control" id="resultsPublishDate" name="resultsPublishDate" value="<?php echo htmlspecialchars($competitionData['results_publish_date'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="winnerEmailTemplate" class="form-label">Winner Email Template</label>
                                    <textarea class="form-control" id="winnerEmailTemplate" name="winnerEmailTemplate" rows="3"><?php echo htmlspecialchars($competitionData['winner_email_template'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-7" role="tabpanel" aria-labelledby="tab-7-tab">
                            <div class="card shadow-sm p-3">
                                <h5 class="card-title">Judges</h5>
                                <div id="judgesContainer">
                                    <?php foreach ($judgeData as $index => $judge): ?>
                                        <div class="card mb-3 p-3">
                                            <div class="d-flex justify-content-end mb-2">
                                                <button type="button" class="btn-close remove-item-btn" aria-label="Close"></button>
                                            </div>
                                            <div class="mb-3">
                                                <label for="judgeName<?php echo $index; ?>" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="judgeName<?php echo $index; ?>" name="judgeName[]" value="<?php echo htmlspecialchars($judge['name'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="judgeEmail<?php echo $index; ?>" class="form-label">Email Address</label>
                                                <input type="email" class="form-control" id="judgeEmail<?php echo $index; ?>" name="judgeEmail[]" value="<?php echo htmlspecialchars($judge['email'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="judgeTitle<?php echo $index; ?>" class="form-label">Title/Organization</label>
                                                <input type="text" class="form-control" id="judgeTitle<?php echo $index; ?>" name="judgeTitle[]" value="<?php echo htmlspecialchars($judge['title'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="judgeLinkedIn<?php echo $index; ?>" class="form-label">LinkedIn URL</label>
                                                <input type="url" class="form-control" id="judgeLinkedIn<?php echo $index; ?>" name="judgeLinkedIn[]" value="<?php echo htmlspecialchars($judge['linkedin_url'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" id="addJudgeBtn" class="btn btn-primary btn-sm mt-3"><i class="fas fa-plus"></i> Add Another Judge</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-8" role="tabpanel" aria-labelledby="tab-8-tab">
                            <div class="card shadow-sm p-3">
                                <h5 class="card-title">Participants</h5>
                                <div id="participantsContainer">
                                    <?php foreach ($participantData as $index => $participant): ?>
                                        <div class="card mb-3 p-3">
                                            <div class="d-flex justify-content-end mb-2">
                                                <button type="button" class="btn-close remove-item-btn" aria-label="Close"></button>
                                            </div>
                                            <div class="mb-3">
                                                <label for="participantName<?php echo $index; ?>" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="participantName<?php echo $index; ?>" name="participantName[]" value="<?php echo htmlspecialchars($participant['name'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="participantEmail<?php echo $index; ?>" class="form-label">Email Address</label>
                                                <input type="email" class="form-control" id="participantEmail<?php echo $index; ?>" name="participantEmail[]" value="<?php echo htmlspecialchars($participant['email'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" id="addParticipantBtn" class="btn btn-primary btn-sm mt-3"><i class="fas fa-plus"></i> Add Another Participant</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-9" role="tabpanel" aria-labelledby="tab-9-tab">
                            <div class="card shadow-sm p-3">
                                <h5 class="card-title">Awards</h5>
                                <div id="awardsContainer">
                                    <?php foreach ($awardData as $index => $award): ?>
                                        <div class="card mb-3 p-3">
                                            <div class="d-flex justify-content-end mb-2">
                                                <button type="button" class="btn-close remove-award-btn" aria-label="Close"></button>
                                            </div>
                                            <div class="mb-3">
                                                <label for="awardName<?php echo $index; ?>" class="form-label">Award Name</label>
                                                <input type="text" class="form-control" id="awardName<?php echo $index; ?>" name="awardName[]" value="<?php echo htmlspecialchars($award['award_name'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="awardValue<?php echo $index; ?>" class="form-label">Award Value</label>
                                                <input type="text" class="form-control" id="awardValue<?php echo $index; ?>" name="awardValue[]" value="<?php echo htmlspecialchars($award['award_value'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="numberOfWinners<?php echo $index; ?>" class="form-label">Number of Winners</label>
                                                <input type="number" class="form-control" id="numberOfWinners<?php echo $index; ?>" name="numberOfWinners[]" value="<?php echo htmlspecialchars($award['number_of_winners'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="perWinnerPrize<?php echo $index; ?>" class="form-label">Per-Winner Prize (optional)</label>
                                                <input type="text" class="form-control" id="perWinnerPrize<?php echo $index; ?>" name="perWinnerPrize[]" value="<?php echo htmlspecialchars($award['per_winner_prize'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" id="addAwardBtn" class="btn btn-primary btn-sm mt-3"><i class="fas fa-plus"></i> Add Another Award</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button id="submitBtn" class="btn btn-success btn-lg" type="submit">Update Competition</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const judgesContainer = document.getElementById('judgesContainer');
            const participantsContainer = document.getElementById('participantsContainer');
            const awardsContainer = document.getElementById('awardsContainer');

            let judgeCount = <?php echo count($judgeData); ?>;
            let participantCount = <?php echo count($participantData); ?>;
            let awardCount = <?php echo count($awardData); ?>;

            document.getElementById('addJudgeBtn').addEventListener('click', function() {
                judgeCount++;
                const newJudgeHtml = `
                <div class="card mb-3 p-3">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn-close remove-item-btn" aria-label="Close"></button>
                    </div>
                    <div class="mb-3">
                        <label for="judgeName${judgeCount}" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="judgeName${judgeCount}" name="judgeName[]">
                    </div>
                    <div class="mb-3">
                        <label for="judgeEmail${judgeCount}" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="judgeEmail${judgeCount}" name="judgeEmail[]">
                    </div>
                    <div class="mb-3">
                        <label for="judgeTitle${judgeCount}" class="form-label">Title/Organization</label>
                        <input type="text" class="form-control" id="judgeTitle${judgeCount}" name="judgeTitle[]">
                    </div>
                    <div class="mb-3">
                        <label for="judgeLinkedIn${judgeCount}" class="form-label">LinkedIn URL</label>
                        <input type="url" class="form-control" id="judgeLinkedIn${judgeCount}" name="judgeLinkedIn[]">
                    </div>
                </div>
            `;
                judgesContainer.insertAdjacentHTML('beforeend', newJudgeHtml);
            });

            document.getElementById('addParticipantBtn').addEventListener('click', function() {
                participantCount++;
                const newParticipantHtml = `
                <div class="card mb-3 p-3">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn-close remove-item-btn" aria-label="Close"></button>
                    </div>
                    <div class="mb-3">
                        <label for="participantName${participantCount}" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="participantName${participantCount}" name="participantName[]">
                    </div>
                    <div class="mb-3">
                        <label for="participantEmail${participantCount}" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="participantEmail${participantCount}" name="participantEmail[]">
                    </div>
                </div>
            `;
                participantsContainer.insertAdjacentHTML('beforeend', newParticipantHtml);
            });

            document.getElementById('addAwardBtn').addEventListener('click', function() {
                awardCount++;
                const newAwardHtml = `
                <div class="card mb-3 p-3">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn-close remove-award-btn" aria-label="Close"></button>
                    </div>
                    <div class="mb-3">
                        <label for="awardName${awardCount}" class="form-label">Award Name</label>
                        <input type="text" class="form-control" id="awardName${awardCount}" name="awardName[]">
                    </div>
                    <div class="mb-3">
                        <label for="awardValue${awardCount}" class="form-label">Award Value</label>
                        <input type="text" class="form-control" id="awardValue${awardCount}" name="awardValue[]">
                    </div>
                    <div class="mb-3">
                        <label for="numberOfWinners${awardCount}" class="form-label">Number of Winners</label>
                        <input type="number" class="form-control" id="numberOfWinners${awardCount}" name="numberOfWinners[]">
                    </div>
                    <div class="mb-3">
                        <label for="perWinnerPrize${awardCount}" class="form-label">Per-Winner Prize (optional)</label>
                        <input type="text" class="form-control" id="perWinnerPrize${awardCount}" name="perWinnerPrize[]">
                    </div>
                </div>
            `;
                awardsContainer.insertAdjacentHTML('beforeend', newAwardHtml);
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item-btn') || e.target.classList.contains('remove-award-btn')) {
                    e.target.closest('.card').remove();
                }
            });
        });
    </script>
</body>

</html>