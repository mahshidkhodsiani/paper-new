<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$response = [];

if (!isset($_SESSION['user_data']) || !isset($_SESSION['user_data']['id'])) {
    echo json_encode([]);
    exit();
}

$current_user_id = $_SESSION['user_data']['id'];
$other_user_id = filter_input(INPUT_GET, 'other_user_id', FILTER_VALIDATE_INT);

if (!$other_user_id || $other_user_id <= 0) {
    echo json_encode([]);
    exit();
}

try {
    // کوئری برای گرفتن پیام‌های بین دو کاربر
    // ORDER BY sent_at ASC برای گرفتن از قدیمی‌ترین به جدیدترین
    $stmt = $conn->prepare("
        SELECT m.id, m.sender_id, m.receiver_id, m.content, m.sent_at, u.name, u.family, u.profile_pic
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.sent_at ASC
    ");
    $stmt->bind_param("iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Optionally, mark messages as read when loaded
    $stmt_mark_read = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE receiver_id = ? AND sender_id = ? AND is_read = FALSE");
    $stmt_mark_read->bind_param("ii", $current_user_id, $other_user_id);
    $stmt_mark_read->execute();
    $stmt_mark_read->close();

    echo json_encode($messages);
} catch (Exception $e) {
    error_log("Error loading conversation: " . $e->getMessage());
    echo json_encode([]); // Return empty array on error
}
