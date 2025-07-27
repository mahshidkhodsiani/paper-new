<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); // Display all errors during development

require_once __DIR__ . '/../config.php'; // Ensure this path is correct

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// 1. Session Check
if (!isset($_SESSION['user_data']) || !isset($_SESSION['user_data']['id'])) {
    $response['message'] = 'User not logged in. Please log in to send messages.';
    echo json_encode($response);
    exit();
}

$sender_id = $_SESSION['user_data']['id'];

// 2. Request Method Check
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // The 'recipient' field from autocomplete is usually for display/lookup, not direct use for receiver_id here
    // If receiver_id is empty, we would use 'recipient_name_for_autocomplete' to find it.
    $recipient_name_for_autocomplete = filter_input(INPUT_POST, 'recipient', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // 3. Input Validation
    if ($receiver_id === false || $receiver_id <= 0) {
        // If receiver_id is not set or invalid, try to find it from recipient name (for new conversations)
        if (!empty($recipient_name_for_autocomplete)) {
            $name_parts = explode(' ', $recipient_name_for_autocomplete, 2);
            $search_name = $name_parts[0];
            $search_family = isset($name_parts[1]) ? $name_parts[1] : '';

            try {
                // Search for the user by name and family
                $stmt_find_receiver = $conn->prepare("SELECT id FROM users WHERE (name = ? AND family = ?) OR (name = ? AND family = ?) LIMIT 1");
                if (!$stmt_find_receiver) {
                    throw new Exception("Recipient lookup prepare failed: " . $conn->error);
                }
                $stmt_find_receiver->bind_param("ssss", $search_name, $search_family, $search_family, $search_name); // Check both name/family orders
                $stmt_find_receiver->execute();
                $result_find_receiver = $stmt_find_receiver->get_result();

                if ($result_find_receiver->num_rows > 0) {
                    $found_user = $result_find_receiver->fetch_assoc();
                    $receiver_id = $found_user['id'];
                } else {
                    $response['message'] = 'Recipient "' . htmlspecialchars($recipient_name_for_autocomplete) . '" not found.';
                    echo json_encode($response);
                    exit();
                }
                $stmt_find_receiver->close();
            } catch (Exception $e) {
                $response['message'] = 'Database error during recipient lookup: ' . $e->getMessage();
                error_log("Recipient lookup error: " . $e->getMessage());
                echo json_encode($response);
                exit();
            }
        } else {
            $response['message'] = 'No valid recipient ID or name provided.';
            echo json_encode($response);
            exit();
        }
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

    if ((int)$sender_id === (int)$receiver_id) { // Ensure comparison is type-safe
        $response['message'] = 'You cannot send a message to yourself.';
        echo json_encode($response);
        exit();
    }

    // 4. Determine Conversation ID (CRITICAL FIX)
    // This ID should be consistent for any conversation between two users, regardless of sender/receiver.
    // By sorting the IDs, we ensure '1_5' is the same as '5_1'.
    $participant1 = min($sender_id, $receiver_id);
    $participant2 = max($sender_id, $receiver_id);
    $conversation_id = $participant1 . '_' . $participant2;

    $is_read = 0; // 0 for FALSE, new messages are unread by default for the receiver

    // 5. Insert Message into Database
    try {
        // IMPORTANT: Add 'conversation_id' to the INSERT query and its corresponding value.
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content, is_read, sent_at, conversation_id) VALUES (?, ?, ?, ?, ?, NOW(), ?)");

        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind parameters: "iisss" corresponds to (int, int, string, string, int, string)
        // sender_id (i), receiver_id (i), subject (s), content (s), is_read (i), conversation_id (s)
        $stmt->bind_param("iissis", $sender_id, $receiver_id, $subject, $content, $is_read, $conversation_id);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Message sent successfully!';
            $response['conversation_id'] = $conversation_id; // Return conversation_id for debugging/future use
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
