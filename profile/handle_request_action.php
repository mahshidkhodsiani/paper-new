<?php
session_start();
include(__DIR__ . '/../config.php'); // فرض بر این است که config.php یک سطح بالاتر قرار دارد.

header('Content-Type: application/json'); // پاسخ به صورت JSON خواهد بود.

$response = ['success' => false, 'message' => ''];

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_data']['id'])) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit();
}

$current_user_id = $_SESSION['user_data']['id'];
$connection_id = $_POST['connection_id'] ?? null;
$action = $_POST['action'] ?? null; // 'accept' or 'decline'

// اعتبارسنجی ورودی‌ها
if (!$connection_id || !is_numeric($connection_id)) {
    $response['message'] = 'Invalid connection ID.';
    echo json_encode($response);
    exit();
}

if (!in_array($action, ['accept', 'decline'])) {
    $response['message'] = 'Invalid action.';
    echo json_encode($response);
    exit();
}

// تعیین وضعیت جدید بر اساس اکشن
$new_status = ($action === 'accept') ? 'accepted' : 'declined';

try {
    // بررسی اینکه آیا درخواست اتصال واقعاً برای این کاربر و در وضعیت 'pending' است.
    // این کار برای امنیت ضروری است تا کاربر نتواند درخواست‌های دیگران را دستکاری کند.
    $stmt = $conn->prepare("SELECT receiver_id, status FROM connections WHERE id = ?");
    $stmt->bind_param("i", $connection_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $connection_data = $result->fetch_assoc();
    $stmt->close();

    if (!$connection_data) {
        $response['message'] = 'Connection request not found.';
        echo json_encode($response);
        exit();
    }

    if ($connection_data['receiver_id'] != $current_user_id) {
        $response['message'] = 'You are not authorized to perform this action on this request.';
        echo json_encode($response);
        exit();
    }

    if ($connection_data['status'] !== 'pending') {
        $response['message'] = 'This request is no longer pending.';
        echo json_encode($response);
        exit();
    }

    // به‌روزرسانی وضعیت درخواست در دیتابیس
    $update_stmt = $conn->prepare("UPDATE connections SET status = ?, updated_at = NOW() WHERE id = ? AND receiver_id = ?");
    $update_stmt->bind_param("sii", $new_status, $connection_id, $current_user_id);

    if ($update_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Connection request ' . $new_status . ' successfully.';
    } else {
        $response['message'] = 'Failed to update connection status: ' . $update_stmt->error;
    }
    $update_stmt->close();
} catch (Exception $e) {
    error_log("Error in handle_request_action.php: " . $e->getMessage());
    $response['message'] = 'Server error: ' . $e->getMessage();
} finally {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}

echo json_encode($response);
