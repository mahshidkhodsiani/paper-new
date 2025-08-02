<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_data']) || !isset($_SESSION['user_data']['id'])) {
    header("Location: /paper-new/login.php");
    exit();
}

$userId = $_SESSION['user_data']['id'];

// Include database connection
include "../config.php";

// Check if saved_id is provided
if (!isset($_POST['saved_id']) || !is_numeric($_POST['saved_id'])) {
    // Redirect back to the saved presentations page with an error
    $message = urlencode("Invalid request: Missing or invalid saved ID.");
    header("Location: /paper-new/profile/saved_presentations.php?status=danger&msg=$message");
    exit();
}

$savedId = intval($_POST['saved_id']);

// Use a prepared statement to safely delete the record
$sql = "DELETE FROM saved_presentations WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ii", $savedId, $userId);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Success: record was deleted
            $message = urlencode("Presentation successfully removed from your saved list.");
            header("Location: /paper-new/profile/saved_presentations.php?status=success&msg=$message");
            exit();
        } else {
            // No rows were affected (maybe the record didn't exist or belonged to another user)
            $message = urlencode("Could not find the presentation to remove or you do not have permission to delete it.");
            header("Location: /paper-new/profile/saved_presentations.php?status=warning&msg=$message");
            exit();
        }
    } else {
        // SQL execution failed
        $message = urlencode("Database error: " . $stmt->error);
        header("Location: /paper-new/profile/saved_presentations.php?status=danger&msg=$message");
        exit();
    }
    $stmt->close();
} else {
    // Statement preparation failed
    $message = urlencode("Database error: Could not prepare the statement.");
    header("Location: /paper-new/profile/saved_presentations.php?status=danger&msg=$message");
    exit();
}

// Close connection
$conn->close();
