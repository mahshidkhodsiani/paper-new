<?php
session_start();
header('Content-Type: application/json');
include "../config.php";

$response = ["status" => "error", "message" => "An error occurred."];

if (!isset($_SESSION['user_data'])) {
    $response['message'] = "You must be logged in to rate a presentation.";
    echo json_encode($response);
    exit();
}

$rater_user_id = $_SESSION['user_data']['id'];
$presentation_id = $_POST['presentation_id'] ?? null;
$rating_value = $_POST['rating_value'] ?? null;
$comment = $_POST['comment'] ?? null;

if (empty($presentation_id) || empty($rating_value)) {
    $response['message'] = "Presentation ID and rating value are required.";
    echo json_encode($response);
    exit();
}

// Check if the user has already rated this presentation
$sql_check = "SELECT id FROM ratings WHERE rater_user_id = ? AND presentation_id = ?";
$stmt_check = $conn->prepare($sql_check);
if ($stmt_check) {
    $stmt_check->bind_param("ii", $rater_user_id, $presentation_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // User has already rated, update their rating
        $sql_update = "UPDATE ratings SET rating_value = ?, comment = ? WHERE rater_user_id = ? AND presentation_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param("isii", $rating_value, $comment, $rater_user_id, $presentation_id);
            if ($stmt_update->execute()) {
                $response['status'] = "success";
                $response['message'] = "Your rating has been updated successfully.";
            } else {
                $response['message'] = "Error updating rating: " . $stmt_update->error;
            }
            $stmt_update->close();
        }
    } else {
        // User has not rated, insert a new rating
        $sql_insert = "INSERT INTO ratings (rater_user_id, presentation_id, rating_value, comment) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        if ($stmt_insert) {
            $stmt_insert->bind_param("iiis", $rater_user_id, $presentation_id, $rating_value, $comment);
            if ($stmt_insert->execute()) {
                $response['status'] = "success";
                $response['message'] = "Your rating has been submitted successfully.";
            } else {
                $response['message'] = "Error submitting rating: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
    }
    $stmt_check->close();
}

$conn->close();
echo json_encode($response);
