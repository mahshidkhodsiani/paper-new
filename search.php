<?php
// این کد را در search.php یا search_live.php قرار دهید
include 'config.php';
include 'includes.php';

$all_results = [];
$search_query = isset($_GET['query']) ? $_GET['query'] : '';
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!empty($search_query)) {
    // اتصال به پایگاه داده و آماده‌سازی کوئری
    $sql = "SELECT * FROM `presentations` WHERE `title` LIKE ? OR `keywords` LIKE ? ORDER BY `created_at` DESC";
    $stmt = $conn->prepare($sql);
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    // تمام نتایج را از پایگاه داده می‌خواند
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $all_results[] = $row;
        }
    }

    $stmt->close();
    $conn->close();
}

// برای جستجوی زنده (live search) با استفاده از AJAX
if ($is_ajax_request) {
    if (!empty($all_results)) {
        echo '<ul class="list-group">';
        // این حلقه تمام نتایج را نمایش می‌دهد (بدون محدودیت 4 نتیجه‌ای)
        foreach ($all_results as $result) {
            echo '<li class="list-group-item">';
            echo '<h5><a href="paper-new/' . htmlspecialchars($result['file_path']) . '" target="_blank">' . htmlspecialchars($result['title']) . '</a></h5>';
            echo '<p>' . htmlspecialchars($result['description']) . '</p>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="list-group-item">No results found</div>';
    }
}
// برای صفحه کامل جستجو، نمایش صفحه HTML با تمام نتایج
else {
    include 'header.php'; // شامل هدر و نوار جستجو می‌شود
?>
    <div class="container mt-5">
        <h2>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
        <hr>
        <?php if (!empty($all_results)): ?>
            <div class="list-group">
                <?php foreach ($all_results as $result): ?>
                    <a href="<?php echo "paper-new/". htmlspecialchars($result['file_path']); ?>" class="list-group-item list-group-item-action" target="_blank">
                        <h5 class="mb-1"><?php echo htmlspecialchars($result['title']); ?></h5>
                        <p class="mb-1"><?php echo htmlspecialchars($result['description']); ?></p>
                        <small class="text-muted">Keywords: <?php echo htmlspecialchars($result['keywords']); ?></small>
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
    include 'footer.php';
}
?>