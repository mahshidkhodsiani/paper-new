<?php
session_start();
include "../config.php"; // مطمئن شوید $conn فعال است

header('Content-Type: application/json');

if (!isset($_SESSION['user_data'])) {
    echo json_encode([]); // Or an error message
    exit();
}

$current_user_id = $_SESSION['user_data']['id'];
$other_user_id = isset($_GET['other_user_id']) ? (int)$_GET['other_user_id'] : 0;

if (empty($other_user_id)) {
    echo json_encode([]);
    exit();
}

try {
    // Calculate the conversation_id_key based on the two user IDs
    $user1_conv = min($current_user_id, $other_user_id);
    $user2_conv = max($current_user_id, $other_user_id);
    $conversation_id_key = $user1_conv . '_' . $user2_conv;

    // Fetch all messages belonging to this conversation_id
    $stmt = $conn->prepare("
        SELECT id, sender_id, receiver_id, subject, content, sent_at, is_read 
        FROM messages 
        WHERE conversation_id = ? 
        ORDER BY sent_at ASC
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("s", $conversation_id_key);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Optionally, mark messages as read for the current user (if they are the receiver)
    // This should ideally happen when the user opens the specific conversation view
    // if (!empty($messages)) {
    //     $update_stmt = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE receiver_id = ? AND conversation_id = ? AND is_read = FALSE");
    //     $update_stmt->bind_param("is", $current_user_id, $conversation_id_key);
    //     $update_stmt->execute();
    //     $update_stmt->close();
    // }

    echo json_encode($messages);
} catch (Exception $e) {
    error_log("Get conversation error: " . $e->getMessage());
    echo json_encode([]);
} finally {
    // $conn->close(); // Again, only close if this is a standalone script
}
