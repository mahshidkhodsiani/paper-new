<?php
session_start();
header('Content-Type: application/json');

include "../config.php"; 
$response = ['status' => 'error', 'message' => 'Invalid request.', 'new_count' => 0];

$loggedInUserId = $_SESSION['user_id'] ?? ($_SESSION['user_data']['id'] ?? null);

if ($loggedInUserId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $presentationId = filter_input(INPUT_POST, 'presentation_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING); // 'like' or 'unlike'

    if ($presentationId && ($action == 'like' || $action == 'unlike')) {
        
        $conn->begin_transaction(); // شروع تراکنش برای اطمینان از صحت هر دو عملیات (درج و به‌روزرسانی شمارنده)

        try {
            if ($action == 'like') {
                // اضافه کردن لایک به جدول likes (IGNORE برای جلوگیری از خطای تکراری شدن لایک)
                $insert_sql = "INSERT IGNORE INTO likes (user_id, presentation_id) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_sql);
            } else {
                // حذف لایک از جدول likes
                $delete_sql = "DELETE FROM likes WHERE user_id = ? AND presentation_id = ?";
                $stmt = $conn->prepare($delete_sql);
            }
            
            // i=integer. users.id احتمالاً int و presentations.id احتمالاً int unsigned است، اما برای bind param هر دو را i می‌گیریم.
            $stmt->bind_param("ii", $loggedInUserId, $presentationId); 
            $stmt->execute();
            $stmt->close();
            
            // به‌روزرسانی ستون likes_count در جدول presentations
            // این کوئری تعداد واقعی لایک‌ها را از جدول likes می‌خواند و در presentations ثبت می‌کند.
            $update_count_sql = "UPDATE presentations SET likes_count = (SELECT COUNT(*) FROM likes WHERE presentation_id = ?) WHERE id = ?";
            $update_stmt = $conn->prepare($update_count_sql);
            $update_stmt->bind_param("ii", $presentationId, $presentationId);
            $update_stmt->execute();
            $update_stmt->close();
            
            // خواندن تعداد لایک جدید (برای نمایش فوری به کاربر)
            $count_sql = "SELECT likes_count FROM presentations WHERE id = ?";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $presentationId);
            $count_stmt->execute();
            $result = $count_stmt->get_result()->fetch_assoc();
            $newCount = $result['likes_count'];
            $count_stmt->close();

            $conn->commit(); // ثبت تغییرات

            $response = ['status' => 'success', 'message' => 'Success', 'new_count' => $newCount];
            
        } catch (Exception $e) {
            $conn->rollback(); // لغو تغییرات در صورت بروز خطا
            $response['message'] = 'Transaction failed: ' . $e->getMessage();
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