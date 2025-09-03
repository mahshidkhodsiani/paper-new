<?php

session_start();
include "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to participate.']);
    exit;
}

$competition_id = isset($_GET['competition_id']) ? (int)$_GET['competition_id'] : 0;
$user_id = $_SESSION['user_data']['id'];
$user_name = $_SESSION['user_data']['name'] . ' ' . $_SESSION['user_data']['family'];
$user_email = $_SESSION['user_data']['email'];

if ($competition_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid competition ID.']);
    exit;
}

try {
    // Check if user is already a participant using user_id
    $sql_check = "SELECT id FROM competition_participants WHERE competition_id = ? AND user_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $competition_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already registered for this competition.']);
        $stmt_check->close();
        $conn->close();
        exit;
    }
    $stmt_check->close();
    
    // Insert new participant, including the user_id
    $sql_insert = "INSERT INTO competition_participants (competition_id, user_id, name, email) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiss", $competition_id, $user_id, $user_name, $user_email);
    $stmt_insert->execute();

    if ($stmt_insert->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Successfully registered as a participant.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to register. Please try again.']);
    }

    $stmt_insert->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again. Detailed error: ' . $e->getMessage()]);
}

$conn->close();

?>