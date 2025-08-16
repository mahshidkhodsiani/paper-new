<?php
// search.php
// شامل کردن فایل‌های اصلی
include 'config.php';
// includes.php: اگر این فایل شامل لینک‌های CSS/JS است، باید اینجا باشد
include 'includes.php';

$all_results = [];
$search_query = isset($_GET['query']) ? $_GET['query'] : '';

// بررسی نوع درخواست (AJAX یا معمولی)
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// اگر کوئری وجود داشته باشد، جستجو را انجام بده
if (!empty($search_query) && strlen($search_query) >= 3) {
    $sql = "SELECT * FROM `presentations` WHERE `title` LIKE ? OR `keywords` LIKE ? ORDER BY `created_at` DESC";
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
    $conn->close();
}

// تابع کمکی برای اصلاح مسیر فایل
function getCorrectFilePath($path)
{
    if (empty($path)) {
        return '#'; // No link if path is empty
    }
    // اگر مسیر از قبل مطلق است (با '/')
    if (strpos($path, '/') === 0) {
        return $path;
    }
    // اگر مسیر با '../' شروع می‌شود (یعنی نسبی)
    if (strpos($path, '../') === 0) {
        // Find the root directory of the project
        $root_path = '/paper-new/'; // Adjust this if your project is in a different subdirectory
        // Remove the relative part from the path
        $clean_path = str_replace('../', '', $path);
        // Combine with the root path
        return $root_path . $clean_path;
    }
    // برای موارد دیگر (غیرمحتمل)
    return $path;
}

// اگر درخواست از نوع AJAX باشد (برای جستجوی زنده)
if ($is_ajax_request) {
    if (!empty($all_results)) {
        echo '<ul class="list-group">';
        foreach ($all_results as $result) {
            $title = htmlspecialchars($result['title'] ?? 'N/A');
            $description = htmlspecialchars($result['description'] ?? 'N/A');
            $filePath = getCorrectFilePath($result['pdf_path'] ?? '');

            echo '<li class="list-group-item">';
            echo '<h5><a href="' . $filePath . '" target="_blank">' . $title . '</a></h5>';
            echo '<p>' . $description . '</p>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="list-group-item">No results found</div>';
    }
}
// اگر درخواست معمولی باشد (برای صفحه کامل نتایج)
else {
    // شامل کردن هدر برای لود شدن استایل‌ها و نوار ناوبری
    include 'header.php';
?>
    <div class="container mt-5">
        <h2>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
        <hr>
        <?php if (!empty($all_results)): ?>
            <div class="list-group">
                <?php foreach ($all_results as $result): ?>
                    <?php $filePath = getCorrectFilePath($result['pdf_path'] ?? ''); ?>
                    <a href="<?php echo $filePath; ?>" class="list-group-item list-group-item-action" target="_blank">
                        <h5 class="mb-1"><?php echo htmlspecialchars($result['title'] ?? 'N/A'); ?></h5>
                        <p class="mb-1"><?php echo htmlspecialchars($result['description'] ?? 'N/A'); ?></p>
                        <small class="text-muted">Keywords: <?php echo htmlspecialchars($result['keywords'] ?? 'N/A'); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                No results found for "<?php echo htmlspecialchars($search_query); ?>".
            </div>
        <?php endif; ?>
    </div>
<?php
    // شامل کردن فوتر برای لود شدن اسکریپت‌ها
    include 'footer.php';
}
?>