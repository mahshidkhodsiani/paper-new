<?php
session_start();
include(__DIR__ . '/../config.php'); // فرض بر این است که config.php یک سطح بالاتر از people/ قرار دارد.

header('Content-Type: application/json'); // پاسخ به صورت JSON خواهد بود.

$response = ['success' => false, 'message' => ''];

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_data']['id'])) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit();
}

$sender_id = $_SESSION['user_data']['id'];
$receiver_id = $_POST['receiver_id'] ?? null;

// اعتبارسنجی ID گیرنده
if (!$receiver_id || !is_numeric($receiver_id)) {
    $response['message'] = 'Invalid receiver ID.';
    echo json_encode($response);
    exit();
}

// جلوگیری از ارسال درخواست اتصال به خود کاربر
if ($sender_id == $receiver_id) {
    $response['message'] = 'Cannot connect to yourself.';
    echo json_encode($response);
    exit();
}

// بررسی اینکه آیا اتصال از قبل وجود دارد یا در حالت انتظار است
$stmt = $conn->prepare("SELECT status FROM connections WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['status'] == 'pending') {
        $response['message'] = 'Connection request already pending.';
    } elseif ($row['status'] == 'accepted') {
        $response['message'] = 'Already connected.';
    } else {
        // اگر وضعیت declined یا هر وضعیت دیگری بود، اجازه ارسال مجدد درخواست را می‌دهیم
        // یا می‌توانیم منطق به‌روزرسانی وضعیت را اینجا اضافه کنیم.
        // برای سادگی فعلاً پیام می‌دهیم که وضعیت خاصی وجود دارد.
        $response['message'] = 'Connection already exists or is in a specific state.';
    }
    echo json_encode($response);
    exit();
}

// اگر اتصالی وجود نداشت، یک درخواست اتصال جدید با وضعیت 'pending' درج می‌کنیم
$stmt = $conn->prepare("INSERT INTO connections (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $sender_id, $receiver_id);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Connection request sent.';
} else {
    $response['message'] = 'Failed to send connection request: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
