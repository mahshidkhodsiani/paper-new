<?php
// search_live.php
include '../config.php';

$all_results = [];

// تابع کمکی برای اصلاح مسیر فایل
function getCorrectFilePath($path)
{
    if (empty($path)) {
        return '#';
    }
    // اگر مسیر از قبل مطلق باشد، آن را برگردان
    if (strpos($path, '/') === 0) {
        return $path;
    }
    // اگر مسیر نسبی است (مانند ../uploads/...)، آن را به درستی تنظیم کن
    if (strpos($path, '../') === 0) {
        // حذف ".." از ابتدای مسیر و اضافه کردن /paper-new/
        return '/paper-new' . substr($path, 2);
    }
    return $path;
}

if (isset($_GET['query'])) {
    $search_query = $_GET['query'];

    // حداقل ۳ کاراکتر برای جستجو
    if (strlen($search_query) >= 3) {
        // جستجو در جدول presentations و join با جدول users
        $sql = "SELECT p.*, CONCAT(u.name, ' ', u.family) AS full_name, u.id AS user_id FROM `presentations` p JOIN `users` u ON p.user_id = u.id WHERE p.`title` LIKE ? OR p.`keywords` LIKE ? ORDER BY p.`created_at` DESC";
        $stmt = $conn->prepare($sql);
        $search_param = "%" . $search_query . "%";
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $all_results[] = $row;
            }
        }
        $stmt->close();
        
    }
}

if (!empty($all_results)) {
    echo '<ul class="list-group">';
    // محدود کردن نتایج به ۵ مورد
    for ($i = 0; $i < min(5, count($all_results)); $i++) {
        $result = $all_results[$i];
        $filePath = getCorrectFilePath($result['pdf_path'] ?? '');
        $authorProfileLink = './profile.php?id=' . urlencode($result['user_id'] ?? '');

        echo '<li class="list-group-item">';
        echo '<h5><a href="' . $filePath . '" target="_blank">' . htmlspecialchars($result['title'] ?? 'N/A') . '</a></h5>';
        echo '<p> Author: <a href="' . $authorProfileLink . '">' . htmlspecialchars($result['full_name'] ?? 'N/A') . '</a></p>';
        echo '<p>' . htmlspecialchars($result['description'] ?? 'N/A') . '</p>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<div class="list-group-item">نتیجه‌ای یافت نشد.</div>';
}
?>