<?php

include "config.php";
include "send_invitation_email.php";

// تابع برای اعتبارسنجی و تمیز کردن داده‌ها
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// var_dump($_SESSION);
// die;
// $userId = $_SESSION['user_data']['id']; // For updating current user's data


// بررسی اینکه درخواست از نوع POST است
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $userId = $_POST['userID'];
    // --- اعتبارسنجی فیلدهای اجباری ---
    $required_fields = [
        'organizer',
        'organizerEmail',
        'competitionTitle',
        'competitionDescription',
        'startDate',
        'endDate',
        'competitionVisibility',
        'participationAccess',
        'votingSystem',
        'submissionType'
    ];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => "فیلد '$field' اجباری است."]);
            $conn->close();
            exit;
        }
    }

    // --- آماده‌سازی متغیرها برای bind_param برای جلوگیری از خطاهای Notice و Fatal ---
    $organizer_name = sanitizeInput($_POST['organizer']);
    $organizer_email = sanitizeInput($_POST['organizerEmail']);
    $competition_title = sanitizeInput($_POST['competitionTitle']);
    $competition_description = sanitizeInput($_POST['competitionDescription']);
    $start_date = sanitizeInput($_POST['startDate']);
    $end_date = sanitizeInput($_POST['endDate']);
    $timezone = sanitizeInput($_POST['timezone']);
    $room_link = sanitizeInput($_POST['roomLink']);
    $session_track = sanitizeInput($_POST['sessionTrack']);
    $presentation_duration = (int)$_POST['presentationDuration'];
    $buffer_duration = (int)$_POST['bufferDuration'];
    $presentation_order = $_POST['presentationOrder'];
    $competition_visibility = $_POST['competitionVisibility'];
    $participation_access = $_POST['participationAccess'];
    $voting_system = $_POST['votingSystem'];
    $max_votes_per_participant = (int)$_POST['maxVotes'];
    $results_visibility = $_POST['resultsVisibility'];
    $max_participants = (int)$_POST['maxParticipants'];
    $max_submissions = (int)$_POST['maxSubmissions'];
    $judge_notes = sanitizeInput($_POST['judgeNotes']);
    $submission_type = $_POST['submissionType'];
    $max_file_size = (int)$_POST['maxFileSize'];
    $allowed_formats = isset($_POST['allowedFormats']) ? implode(',', $_POST['allowedFormats']) : '';
    $submission_guidelines = sanitizeInput($_POST['submissionGuidelines']);
    $custom_fields_json = $_POST['customFields'];
    $slide_deck_required = (isset($_FILES['slideDeck']) && $_FILES['slideDeck']['error'] == UPLOAD_ERR_OK) ? 1 : 0;
    $abstract_text_field = isset($_POST['abstractText']) ? 1 : 0;
    $poster_image_optional = (isset($_FILES['posterImage']) && $_FILES['posterImage']['error'] == UPLOAD_ERR_OK) ? 1 : 0;
    $consent_recording = isset($_POST['consentRecording']) ? 1 : 0;
    $consent_public_display = isset($_POST['consentPublicDisplay']) ? 1 : 0;
    $scoring_rubric = $_POST['scoringRubric'];
    $score_weighting_system = $_POST['scoreWeighting'];
    $custom_css = sanitizeInput($_POST['customCss']);
    $redirect_url = sanitizeInput($_POST['redirectUrl']);
    $enable_comments = isset($_POST['enableComments']) ? 1 : 0;
    $moderate_submissions = isset($_POST['moderateSubmissions']) ? 1 : 0;
    $enable_blind_review = isset($_POST['enableBlindReview']) ? 1 : 0;
    $require_conflict = isset($_POST['requireConflict']) ? 1 : 0;
    $late_submission_grace_period = (int)$_POST['lateSubmissionGrace'];
    $judging_visibility = $_POST['judgingVisibility'];
    $webhook_url = sanitizeInput($_POST['webhookUrl']);
    $export_options = isset($_POST['exportOptions']) ? implode(',', $_POST['exportOptions']) : '';
    $per_criterion_score_scale = $_POST['scoreScale'];
    $tie_break_policy = $_POST['tieBreakPolicy'];
    $qa_time = (int)$_POST['qaTime'];
    $leaderboard_visibility = $_POST['leaderboardVisibility'];
    $notify_new_submission = isset($_POST['notifyNewSubmission']) ? 1 : 0;
    $send_schedule = isset($_POST['sendSchedule']) ? 1 : 0;
    $email_winners = isset($_POST['emailWinners']) ? 1 : 0;
    $results_publish_date = sanitizeInput($_POST['resultsPublishDate']);
    $winner_email_template = sanitizeInput($_POST['winnerEmailTemplate']);

    // --- ذخیره اطلاعات اصلی رقابت ---
    $stmt = $conn->prepare("INSERT INTO competitions (user_id,
        organizer_name, organizer_email, competition_title, competition_description, start_date, end_date, timezone,
        room_link, session_track, presentation_duration, buffer_duration, presentation_order, competition_visibility,
        participation_access, voting_system, max_votes_per_participant, results_visibility, max_participants,
        max_submissions_per_participant, judge_notes, submission_type, max_file_size, allowed_formats,
        submission_guidelines, custom_fields_json, slide_deck_required, abstract_text_field, poster_image_optional,
        consent_recording, consent_public_display, scoring_rubric, score_weighting_system, custom_css, redirect_url,
        enable_comments, moderate_submissions, enable_blind_review, require_conflict, late_submission_grace_period,
        judging_visibility, webhook_url, export_options, per_criterion_score_scale, tie_break_policy, qa_time,
        leaderboard_visibility, notify_new_submission, send_schedule, email_winners, results_publish_date,
        winner_email_template
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $types = "isssssssssiiisssisssisisiiiiisssssiiiissssssiissssss";

    $stmt->bind_param(
        $types,
        $userId,
        $organizer_name,
        $organizer_email,
        $competition_title,
        $competition_description,
        $start_date,
        $end_date,
        $timezone,
        $room_link,
        $session_track,
        $presentation_duration,
        $buffer_duration,
        $presentation_order,
        $competition_visibility,
        $participation_access,
        $voting_system,
        $max_votes_per_participant,
        $results_visibility,
        $max_participants,
        $max_submissions,
        $judge_notes,
        $submission_type,
        $max_file_size,
        $allowed_formats,
        $submission_guidelines,
        $custom_fields_json,
        $slide_deck_required,
        $abstract_text_field,
        $poster_image_optional,
        $consent_recording,
        $consent_public_display,
        $scoring_rubric,
        $score_weighting_system,
        $custom_css,
        $redirect_url,
        $enable_comments,
        $moderate_submissions,
        $enable_blind_review,
        $require_conflict,
        $late_submission_grace_period,
        $judging_visibility,
        $webhook_url,
        $export_options,
        $per_criterion_score_scale,
        $tie_break_policy,
        $qa_time,
        $leaderboard_visibility,
        $notify_new_submission,
        $send_schedule,
        $email_winners,
        $results_publish_date,
        $winner_email_template
    );

    $stmt->execute();
    $competition_id = $stmt->insert_id;
    $stmt->close();

    // --- ذخیره داوران ---
    if (!empty($_POST['judgeName'])) {
        $stmt = $conn->prepare("INSERT INTO competition_judges (competition_id, name, email, title, linkedin_url) VALUES (?, ?, ?, ?, ?)");
        foreach ($_POST['judgeName'] as $index => $name) {
            $judge_name = sanitizeInput($name);
            $judge_email = sanitizeInput($_POST['judgeEmail'][$index]);
            $judge_title = sanitizeInput($_POST['judgeTitle'][$index]);
            $judge_linkedin = sanitizeInput($_POST['judgeLinkedIn'][$index]);
            $stmt->bind_param(
                "issss",
                $competition_id,
                $judge_name,
                $judge_email,
                $judge_title,
                $judge_linkedin
            );
            $stmt->execute();
        }
        $stmt->close();
    }

    // --- ذخیره شرکت‌کنندگان ---
    if (!empty($_POST['participantName'])) {
        $stmt = $conn->prepare("INSERT INTO competition_participants (competition_id, name, email) VALUES (?, ?, ?)");
        foreach ($_POST['participantName'] as $index => $name) {
            $participant_name = sanitizeInput($name);
            $participant_email = sanitizeInput($_POST['participantEmail'][$index]);
            $stmt->bind_param(
                "iss",
                $competition_id,
                $participant_name,
                $participant_email
            );
            $stmt->execute();
        }
        $stmt->close();
    }

    // --- ذخیره جوایز ---
    if (!empty($_POST['awardName'])) {
        $stmt = $conn->prepare("INSERT INTO competition_awards (competition_id, award_name, award_value, number_of_winners, per_winner_prize) VALUES (?, ?, ?, ?, ?)");
        foreach ($_POST['awardName'] as $index => $name) {
            $award_name = sanitizeInput($name);
            $award_value = sanitizeInput($_POST['awardValue'][$index]);
            $number_of_winners = (int)$_POST['numberOfWinners'][$index];
            $per_winner_prize = sanitizeInput($_POST['perWinnerPrize'][$index]);
            $stmt->bind_param(
                "issis",
                $competition_id,
                $award_name,
                $award_value,
                $number_of_winners,
                $per_winner_prize
            );
            $stmt->execute();
        }
        $stmt->close();
    }

    // --- ذخیره فایل‌های آپلود شده ---
    $uploads_map = [
        'logo' => 'logo',
        'sampleCertificate' => 'sample_certificate',
        'competitionRubric' => 'competition_rubric',
        'posterImage' => 'poster_image',
        'slideDeck' => 'slide_deck'
    ];

    // ایجاد دایرکتوری جدید بر اساس competition_id
    $upload_dir = 'uploads/competitions/' . $competition_id . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $stmt = $conn->prepare("INSERT INTO competition_uploads (competition_id, type, file_path, mime_type, file_size) VALUES (?, ?, ?, ?, ?)");

    foreach ($uploads_map as $form_field => $db_type) {
        if (isset($_FILES[$form_field]) && $_FILES[$form_field]['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES[$form_field];
            $filename = basename($file['name']);
            $target_file = $upload_dir . uniqid() . '_' . $filename;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $file_type = $file['type'];
                $file_size = $file['size'];
                $stmt->bind_param(
                    "isssi",
                    $competition_id,
                    $db_type,
                    $target_file,
                    $file_type,
                    $file_size
                );
                $stmt->execute();
            }
        }
    }
    $stmt->close();

    // --- پاسخ موفقیت آمیز ---
    // echo json_encode(['success' => true, 'message' => 'اطلاعات با موفقیت ذخیره شد.']);



    // --- ارسال ایمیل دعوت به داوران ---
    if (!empty($_POST['judgeEmail'])) {
        $competition_title = sanitizeInput($_POST['competitionTitle']);
        $competition_link = 'https://paperet.com/competition/' . $competition_id; // **این لینک را با لینک واقعی مسابقه خود جایگزین کنید**
        foreach ($_POST['judgeEmail'] as $index => $email) {
            $name = sanitizeInput($_POST['judgeName'][$index]);
            sendInvitationEmail($email, $name, $competition_title, $competition_link, 'Referee');
        }
    }

    // --- ارسال ایمیل دعوت به شرکت‌کنندگان ---
    if (!empty($_POST['participantEmail'])) {
        $competition_title = sanitizeInput($_POST['competitionTitle']);
        $competition_link = 'https://paperet.com/competition/' . $competition_id; // **این لینک را با لینک واقعی مسابقه خود جایگزین کنید**
        foreach ($_POST['participantEmail'] as $index => $email) {
            $name = sanitizeInput($_POST['participantName'][$index]);
            sendInvitationEmail($email, $name, $competition_title, $competition_link, 'شرکت‌کننده');
        }
    }



    header("Location: discover_competitions");
    exit(); // برای جلوگیری از اجرای ادامه کد پس از ریدایرکت

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'فقط درخواست‌های POST پذیرفته می‌شود.']);
}

$conn->close();
