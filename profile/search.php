<?php
// search.php
session_start();
// اصلاح مسیردهی به فایل config.php و includes.php
include '../config.php';
include '../includes.php';

$all_results = [];
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';

// بررسی نوع درخواست (AJAX یا معمولی)
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

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

// اگر کوئری وجود داشته باشد، جستجو را انجام بده
if (!empty($search_query) && strlen($search_query) >= 3) {
    // اصلاح کوئری SQL برای join با جدول users و دریافت نام و نام خانوادگی نویسنده
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

// اگر درخواست AJAX باشد، نتایج را برای جستجوی زنده برمی‌گردانیم
if ($is_ajax_request) {
    if (!empty($all_results)) {
        echo '<ul class="list-group">';
        for ($i = 0; $i < min(5, count($all_results)); $i++) {
            $result = $all_results[$i];
            echo '<li class="list-group-item">';
            echo '<h5><a href="' . getCorrectFilePath($result['pdf_path']) . '" target="_blank">' . htmlspecialchars($result['title']) . '</a></h5>';
            echo '<p>' . htmlspecialchars($result['description']) . '</p>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="list-group-item">نتیجه‌ای یافت نشد.</div>';
    }
}
// اگر درخواست معمولی باشد (برای صفحه کامل نتایج)
else {
    // شامل کردن هدر برای لود شدن استایل‌ها و نوار ناوبری
    include 'header.php';
?>
    <style>
        .search-result-card {
            transition: transform 0.2s ease-in-out;
        }

        .search-result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
        }

        .search-result-card .card-title {
            color: #0d6efd;
            /* رنگ آبی برای عنوان */
        }
    </style>

    <div class="container mt-5">
        <h2 class="mb-4">نتایج جستجو برای "<?php echo htmlspecialchars($search_query); ?>"</h2>
        <hr>
        <?php if (!empty($all_results)): ?>
            <div class="row">
                <?php foreach ($all_results as $result): ?>
                    <?php
                    $filePath = getCorrectFilePath($result['pdf_path'] ?? '');
                    // استفاده از user_id از نتایج کوئری
                    $authorProfileLink = '../people/profile.php?id=' . urlencode($result['user_id'] ?? '');
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card search-result-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><a href="<?php echo $filePath; ?>" target="_blank"><?php echo htmlspecialchars($result['title'] ?? 'N/A'); ?></a></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($result['description'] ?? 'N/A'); ?></p>
                                <small class="text-secondary d-block mt-2">
                                    **Author:** <a href="<?php echo $authorProfileLink; ?>"><?php echo htmlspecialchars($result['full_name'] ?? 'N/A'); ?></a>
                                    <br>
                                    **keywords:** <?php echo htmlspecialchars($result['keywords'] ?? 'N/A'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                نتیجه‌ای برای "<?php echo htmlspecialchars($search_query); ?>" یافت نشد.
            </div>
        <?php endif; ?>
    </div>

<?php
    // شامل کردن فوتر
    include 'footer.php';
}
?>