<?php
// search.php
session_start();
// Fixing the path to config.php and includes.php
include '../config.php';
include '../includes.php';

$all_results = [];
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Check the request type (AJAX or regular)
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Helper function to correct the file path
function getCorrectFilePath($path)
{
    if (empty($path)) {
        return '#';
    }
    // If the path is already absolute, return it
    if (strpos($path, '/') === 0) {
        return $path;
    }
    // If the path is relative (like ../uploads/...), set it correctly
    if (strpos($path, '../') === 0) {
        // Remove ".." from the beginning of the path and add /paper-new/
        return '/paper-new' . substr($path, 2);
    }
    return $path;
}

// If query exists, perform the search
if (!empty($search_query) && strlen($search_query) >= 3) {
    // Correcting the SQL query to join with the users table and retrieve the author's full name
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

// If it's an AJAX request, we return the results for live search
if ($is_ajax_request) {
    if (!empty($all_results)) {
        echo '<ul class="list-group">';
        for ($i = 0; $i < min(5, count($all_results)); $i++) {
            $result = $all_results[$i];

            // Note: In AJAX response, Author name/link was missing in the original code, but description/title were available. 
            // The original structure only included title and description for AJAX results.

            echo '<li class="list-group-item">';
            echo '<h5><a href="' . getCorrectFilePath($result['pdf_path'] ?? '') . '" target="_blank">' . htmlspecialchars($result['title'] ?? 'N/A') . '</a></h5>';
            echo '<p>' . htmlspecialchars($result['description'] ?? 'N/A') . '</p>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="list-group-item">No result found.</div>'; // نتیجه‌ای یافت نشد.
    }
}
// If it's a regular request (for the full results page)
else {
    // Including the header to load styles and navigation bar
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
            /* Blue color for the title */
            // رنگ آبی برای عنوان
        }
    </style>

    <link rel="icon" type="image/x-icon" href="../images/logo.png">


    <div class="container mt-5">
        <h2 class="mb-4">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
        <hr>
        <?php if (!empty($all_results)): ?>
            <div class="row">
                <?php foreach ($all_results as $result): ?>
                    <?php
                    $filePath = getCorrectFilePath($result['pdf_path'] ?? '');
                    // Using user_id from the query results
                    $authorProfileLink = '../people/profile.php?id=' . urlencode($result['user_id'] ?? ''); // اصلاح مسیر نسبی: ../people/profile.php
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card search-result-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><a href="<?php echo $filePath; ?>" target="_blank"><?php echo htmlspecialchars($result['title'] ?? 'N/A'); ?></a></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($result['description'] ?? 'N/A'); ?></p>
                                <small class="text-secondary d-block mt-2">
                                    **Author:** <a href="<?php echo $authorProfileLink; ?>"><?php echo htmlspecialchars($result['full_name'] ?? 'N/A'); ?></a>
                                    <br>
                                    **Keywords:** <?php echo htmlspecialchars($result['keywords'] ?? 'N/A'); ?> </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No result found for "<?php echo htmlspecialchars($search_query); ?>".
            </div>
        <?php endif; ?>
    </div>

<?php
    // Including the footer
    include 'footer.php';
}
?>