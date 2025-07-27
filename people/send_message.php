<?php
session_start();
include "../config.php"; // مطمئن شوید $conn فعال است

header('Content-Type: application/json');

if (!isset($_SESSION['user_data'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit();
}

$sender_id = $_SESSION['user_data']['id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if (empty($receiver_id) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Recipient and message content are required.']);
    exit();
}

try {
    // 1. Determine conversation_id
    // Always use the smaller ID first to ensure a consistent conversation_id for both participants
    $user1 = min($sender_id, $receiver_id);
    $user2 = max($sender_id, $receiver_id);
    $conversation_id_key = $user1 . '_' . $user2; // e.g., "1_5"

    // 2. Insert the message
    // این خط INSERT به درستی sent_at و conversation_id را درج می‌کند
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content, conversation_id, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    // تعداد s و i باید با تعداد علامت سوال در بالا مطابقت داشته باشد (5 علامت سوال)
    $stmt->bind_param("iisss", $sender_id, $receiver_id, $subject, $content, $conversation_id_key);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Send message error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
} finally {
    // $conn->close();
}
