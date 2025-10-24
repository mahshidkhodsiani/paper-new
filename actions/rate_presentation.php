<?php
session_start();
// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json');

// فرض بر این است که config.php یک سطح بالاتر از پوشه actions قرار دارد
include "../config.php"; 

$response = ['status' => 'error', 'message' => 'Invalid request or user not logged in.'];

// چک کردن لاگین بودن کاربر
$loggedInUserId = $_SESSION['user_id'] ?? ($_SESSION['user_data']['id'] ?? null);

if ($loggedInUserId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // تمیز کردن ورودی‌ها
    $presentationId = filter_input(INPUT_POST, 'presentation_id', FILTER_VALIDATE_INT);
    $ratingValue = filter_input(INPUT_POST, 'rating_value', FILTER_VALIDATE_INT);
    // filter_SANITIZE_STRING برای کامنت مناسب است، یا از FILTER_DEFAULT برای حفظ کاراکترها استفاده کنید
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING); 
    
    // چک کردن اعتبار داده‌های ضروری
    if ($presentationId && $ratingValue >= 1 && $ratingValue <= 5) {
        
        // 1. ابتدا بررسی کنید که آیا کاربر قبلاً امتیاز داده است یا خیر
        // ما از جدول ratings که شما دارید استفاده می‌کنیم.
        $check_sql = "SELECT id FROM ratings WHERE rater_user_id = ? AND presentation_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $loggedInUserId, $presentationId);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // 2. اگر قبلاً امتیاز داده، آن را به‌روزرسانی کنید (Update)
            $update_sql = "UPDATE ratings SET rating_value = ?, comment = ?, created_at = NOW() WHERE rater_user_id = ? AND presentation_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            // i=integer, s=string. (rating_value, comment, rater_user_id, presentation_id)
            $update_stmt->bind_param("isii", $ratingValue, $comment, $loggedInUserId, $presentationId);
            
            if ($update_stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Rating updated successfully.'];
            } else {
                $response['message'] = 'Database update failed: ' . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            // 3. اگر قبلاً امتیاز نداده، یک سطر جدید ایجاد کنید (Insert)
            $insert_sql = "INSERT INTO ratings (rater_user_id, presentation_id, rating_value, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            // iiis (rater_user_id, presentation_id, rating_value, comment)
            $insert_stmt->bind_param("iiis", $loggedInUserId, $presentationId, $ratingValue, $comment);
            
            if ($insert_stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Rating submitted successfully.'];
            } else {
                $response['message'] = 'Database insertion failed: ' . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
    } else {
        $response['message'] = 'Invalid presentation ID or rating value.';
    }
} else {
    // اگر کاربر لاگین نباشد یا متد POST نباشد
    $response['message'] = 'Please log in to submit a rating.';
}

echo json_encode($response);
$conn->close();
?>