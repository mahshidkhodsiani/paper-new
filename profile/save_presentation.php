<?php
session_start();

// Log session for debugging (for testing only, remove later)
// error_log("Session ID in save_presentation.php: " . session_id());
// error_log("User ID in save_presentation.php: " . ($_SESSION['user_id'] ?? 'NOT SET'));


include "../config.php"; // Database connection file

// Variable to store message and its type
$message = '';
$messageType = 'danger'; // Default: error

// The profile page URL to redirect to
// Assuming save_presentation.php is in 'paper-new/profile/'
// and profile.php is in 'paper-new/people/'.
$redirectBaseUrl = '../../people/profile.php';

$profileIdToRedirectTo = null;

// Get the profile ID from the referrer URL if available
// This is still using HTTP_REFERER which is not ideal, but matches your current setup.
// A more robust solution would be to pass the target profile ID via a hidden input in the form.
if (isset($_SERVER['HTTP_REFERER'])) {
    $refererParts = parse_url($_SERVER['HTTP_REFERER']);
    if (isset($refererParts['query'])) {
        parse_str($refererParts['query'], $refererQuery);
        if (isset($refererQuery['id'])) {
            $profileIdToRedirectTo = $refererQuery['id'];
        }
    }
}

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $message = 'Please log in to save the presentation.';
    $messageType = 'danger';
    // Construct URL for redirection
    $redirectUrl = $redirectBaseUrl;
    if ($profileIdToRedirectTo) {
        $redirectUrl .= '?id=' . $profileIdToRedirectTo;
        $redirectUrl .= '&status=' . $messageType . '&msg=' . urlencode($message); // Use & for subsequent params
    } else {
        $redirectUrl .= '?status=' . $messageType . '&msg=' . urlencode($message); // Use ? for first param
    }
    header("Location: " . $redirectUrl);
    exit();
}

$loggedInUserId = $_SESSION['user_id'];

// 2. Check for presentation_id
if (!isset($_POST['presentation_id']) || !is_numeric($_POST['presentation_id'])) {
    $message = 'Invalid presentation ID.';
    $messageType = 'danger';
    // Construct URL for redirection
    $redirectUrl = $redirectBaseUrl;
    if ($profileIdToRedirectTo) {
        $redirectUrl .= '?id=' . $profileIdToRedirectTo;
        $redirectUrl .= '&status=' . $messageType . '&msg=' . urlencode($message);
    } else {
        $redirectUrl .= '?status=' . $messageType . '&msg=' . urlencode($message);
    }
    header("Location: " . $redirectUrl);
    exit();
}

$presentationId = intval($_POST['presentation_id']);

// 3. Check if presentation is already saved (optional but recommended)
$check_sql = "SELECT id FROM saved_presentations WHERE user_id = ? AND presentation_id = ?";
$check_stmt = $conn->prepare($check_sql);
if ($check_stmt) {
    $check_stmt->bind_param("ii", $loggedInUserId, $presentationId);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $message = 'This presentation has already been saved.';
        $messageType = 'warning'; // Or 'info'
        // Construct URL for redirection
        $redirectUrl = $redirectBaseUrl;
        if ($profileIdToRedirectTo) {
            $redirectUrl .= '?id=' . $profileIdToRedirectTo;
            $redirectUrl .= '&status=' . $messageType . '&msg=' . urlencode($message);
        } else {
            $redirectUrl .= '?status=' . $messageType . '&msg=' . urlencode($message);
        }
        header("Location: " . $redirectUrl);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();
} else {
    $message = 'Error preparing saved status check: ' . $conn->error;
    $messageType = 'danger';
    // Construct URL for redirection
    $redirectUrl = $redirectBaseUrl;
    if ($profileIdToRedirectTo) {
        $redirectUrl .= '?id=' . $profileIdToRedirectTo;
        $redirectUrl .= '&status=' . $messageType . '&msg=' . urlencode($message);
    } else {
        $redirectUrl .= '?status=' . $messageType . '&msg=' . urlencode($message);
    }
    header("Location: " . $redirectUrl);
    exit();
}


// 4. Insert into database
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
    $message = 'Error preparing save statement: ' . $conn->error;
    $messageType = 'danger';
}

$conn->close();

// Final URL construction for redirection
$redirectUrl = $redirectBaseUrl;
if ($profileIdToRedirectTo) {
    $redirectUrl .= '?id=' . $profileIdToRedirectTo;
    $redirectUrl .= '&status=' . $messageType . '&msg=' . urlencode($message);
} else {
    $redirectUrl .= '?status=' . $messageType . '&msg=' . urlencode($message);
}

header("Location: " . $redirectUrl);
exit();
