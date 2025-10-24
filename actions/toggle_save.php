<?php
session_start();
header('Content-Type: application/json');

include "../config.php"; 
$response = ['status' => 'error', 'message' => 'Invalid request.'];

$loggedInUserId = $_SESSION['user_id'] ?? ($_SESSION['user_data']['id'] ?? null);

if ($loggedInUserId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $presentationId = filter_input(INPUT_POST, 'presentation_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING); // 'save' or 'unsave'

    // بررسی وجود پرزنتیشن و معتبر بودن اکشن
    if ($presentationId && ($action == 'save' || $action == 'unsave')) {
        
        if ($action == 'save') {
            // ذخیره پرزنتیشن (INSERT IGNORE از ذخیره مجدد جلوگیری می‌کند)
            $insert_sql = "INSERT IGNORE INTO saved_presentations (user_id, presentation_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ii", $loggedInUserId, $presentationId);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Presentation saved.'];
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            // حذف پرزنتیشن از لیست ذخیره شده‌ها
            $delete_sql = "DELETE FROM saved_presentations WHERE user_id = ? AND presentation_id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param("ii", $loggedInUserId, $presentationId);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Presentation unsaved.'];
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $response['message'] = 'Invalid action or presentation ID.';
    }
} else {
    $response['message'] = 'User not logged in.';
}

echo json_encode($response);
$conn->close();
?>