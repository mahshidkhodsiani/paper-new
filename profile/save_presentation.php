<?php

session_start();

// include file to establish database connection
include "../config.php";

// variables for message and message type
$message = '';
$messageType = 'danger';

// redirect base URL (using absolute path from root)
// $redirectBaseUrl = '/people/profile.php';
$redirectBaseUrl = '/paper-new/people/profile.php';


// Check if profile ID is sent via POST from the form
$profileIdToRedirectTo = null;
if (isset($_POST['current_profile_id']) && is_numeric($_POST['current_profile_id'])) {
    $profileIdToRedirectTo = intval($_POST['current_profile_id']);
}

// helper function to build the redirect URL
function buildRedirectUrl($baseUrl, $profileId, $status, $msg)
{
    $url = $baseUrl;
    $params = [];
    if ($profileId) {
        $params[] = 'id=' . urlencode($profileId);
    }
    $params[] = 'status=' . urlencode($status);
    $params[] = 'msg=' . urlencode($msg);

    if (!empty($params)) {
        $url .= '?' . implode('&', $params);
    }
    return $url;
}

// 1. check if user is logged in
$loggedInUserId = $_SESSION['user_id'] ?? ($_SESSION['user_data']['id'] ?? null);

if (!$loggedInUserId) {
    $message = 'Please log in to save a presentation.';
    $messageType = 'danger';
    header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
    exit();
}

// 2. check presentation_id from POST data
if (!isset($_POST['presentation_id']) || !is_numeric($_POST['presentation_id'])) {
    $message = 'Invalid presentation ID.';
    $messageType = 'danger';
    header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
    exit();
}

$presentationId = intval($_POST['presentation_id']);

// Check for database connection
if (!isset($conn) || $conn->connect_error) {
    $message = 'Database connection error: ' . ($conn->connect_error ?? 'unknown');
    $messageType = 'danger';
    header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
    exit();
}

// 3. check if presentation is already saved (recommended)
$check_sql = "SELECT id FROM saved_presentations WHERE user_id = ? AND presentation_id = ?";
$check_stmt = $conn->prepare($check_sql);
if ($check_stmt) {
    $check_stmt->bind_param("ii", $loggedInUserId, $presentationId);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $message = 'This presentation has already been saved.';
        $messageType = 'warning';
        $check_stmt->close();
        header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
        exit();
    }
    $check_stmt->close();
} else {
    $message = 'Failed to prepare check query: ' . $conn->error;
    $messageType = 'danger';
    header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
    exit();
}

// 4. insert into database
$insert_sql = "INSERT INTO saved_presentations (user_id, presentation_id) VALUES (?, ?)";
$insert_stmt = $conn->prepare($insert_sql);

if ($insert_stmt) {
    $insert_stmt->bind_param("ii", $loggedInUserId, $presentationId);
    if ($insert_stmt->execute()) {
        $message = 'Presentation saved successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error saving presentation: ' . $insert_stmt->error;
        $messageType = 'danger';
    }
    $insert_stmt->close();
} else {
    $message = 'Failed to prepare save query: ' . $conn->error;
    $messageType = 'danger';
}

// Final redirect with message
header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
exit();
