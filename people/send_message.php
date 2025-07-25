<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_data'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to send messages']);
    exit();
}

$sender_id = $_SESSION['user_data']['id'];
$receiver_id = $_POST['receiver_id'];
$subject = trim($_POST['subject']);
$content = trim($_POST['content']);

// اعتبارسنجی داده‌ها
if (empty($subject) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Subject and message content are required']);
    exit();
}

// بررسی وجود کاربر دریافت کننده
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Recipient not found']);
    exit();
}

// ذخیره پیام در دیتابیس
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content, sent_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiss", $sender_id, $receiver_id, $subject, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}

$stmt->close();
$conn->close();
