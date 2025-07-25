<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['user_data']) || !isset($_SESSION['user_data']['id'])) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit();
}

$sender_id = $_SESSION['user_data']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $recipient_name_for_autocomplete = filter_input(INPUT_POST, 'recipient', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$receiver_id || $receiver_id <= 0) {
        $response['message'] = 'Invalid recipient ID provided.';
        echo json_encode($response);
        exit();
    }
    if (empty($subject)) {
        $response['message'] = 'Message subject cannot be empty.';
        echo json_encode($response);
        exit();
    }
    if (empty($content)) {
        $response['message'] = 'Message content cannot be empty.';
        echo json_encode($response);
        exit();
    }

    if ($sender_id === $receiver_id) {
        $response['message'] = 'You cannot send a message to yourself.';
        echo json_encode($response);
        exit();
    }

    try {
        if (!empty($recipient_name_for_autocomplete) && empty($_POST['receiver_id'])) {
            $name_parts = explode(' ', $recipient_name_for_autocomplete, 2);
            $search_name = $name_parts[0];
            $search_family = isset($name_parts[1]) ? $name_parts[1] : '';

            $stmt_find_receiver = $conn->prepare("SELECT id FROM users WHERE (name = ? AND family = ?) OR (name = ? AND family = ?)");
            $stmt_find_receiver->bind_param("ssss", $search_name, $search_family, $search_family, $search_name);
            $stmt_find_receiver->execute();
            $result_find_receiver = $stmt_find_receiver->get_result();
            if ($result_find_receiver->num_rows > 0) {
                $found_user = $result_find_receiver->fetch_assoc();
                $receiver_id = $found_user['id'];
            } else {
                $response['message'] = 'Recipient not found in the database.';
                echo json_encode($response);
                exit();
            }
            $stmt_find_receiver->close();
        }

        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content, is_read, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $is_read = 0;

        // بررسی موفقیت‌آمیز بودن آماده‌سازی
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // این خط صحیح و کافی است.
        $stmt->bind_param("iissi", $sender_id, $receiver_id, $subject, $content, $is_read);

        // خط زیر حذف شده است چون تکراری و نادرست بود.
        // $stmt->bind_param("iiss", $sender_id, $receiver_id, $subject, $content); 

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Message sent successfully!';
        } else {
            $response['message'] = 'Failed to send message: ' . $stmt->error;
            error_log("Failed to insert message into DB: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Send message script error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method. Only POST allowed.';
}

echo json_encode($response);
