<?php
// این کد را در search.php قرار دهید
include '../config.php';
include '../includes.php';
session_start();

// تابع برای بررسی ذخیره‌سازی ارائه
function isPresentationSaved($conn, $user_id, $presentation_id)
{
    if (!$user_id || !$presentation_id) {
        return false;
    }
    $sql = "SELECT 1 FROM `saved_presentations` WHERE `user_id` = ? AND `presentation_id` = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("ii", $user_id, $presentation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_saved = $result->num_rows > 0;
    $stmt->close();
    return $is_saved;
}


// --- بخش مدیریت درخواست‌های AJAX ---
$user_id = isset($_SESSION['user_data']['id']) ? $_SESSION['user_data']['id'] : null;
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
$is_post_request = $_SERVER['REQUEST_METHOD'] === 'POST';

// اگر درخواست از نوع POST و AJAX بود (برای ذخیره/حذف)
if ($is_post_request && $is_ajax_request) {
    ob_end_clean();
    header('Content-Type: application/json');

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
        exit;
    }

    try {
        $presentation_id = isset($_POST['presentation_id']) ? intval($_POST['presentation_id']) : 0;
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($presentation_id <= 0 || !in_array($action, ['save', 'unsave'])) {
            throw new Exception('Invalid request.');
        }

        if ($action === 'save') {
            $sql = "INSERT INTO `saved_presentations` (`user_id`, `presentation_id`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `saved_at` = CURRENT_TIMESTAMP";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ii", $user_id, $presentation_id);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Presentation saved.']);
            $stmt->close();
        } elseif ($action === 'unsave') {
            $sql = "DELETE FROM `saved_presentations` WHERE `user_id` = ? AND `presentation_id` = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ii", $user_id, $presentation_id);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Presentation unsaved.']);
            $stmt->close();
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// اگر درخواست AJAX از نوع GET بود (برای جستجوی زنده)
if ($is_ajax_request && !$is_post_request) {
    ob_end_clean();
    header('Content-Type: text/html');

    $search_query = isset($_GET['query']) ? $_GET['query'] : '';
    if (!empty($search_query)) {
        $sql = "SELECT `id`, `title`, `file_path`, `description` FROM `presentations` WHERE `title` LIKE ? OR `keywords` LIKE ? ORDER BY `created_at` DESC";
        $stmt = $conn->prepare($sql);
        $search_param = "%" . $search_query . "%";
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<ul class="list-group">';
            while ($row = $result->fetch_assoc()) {
                echo '<a href="..//' . htmlspecialchars($row['file_path']) . '" class="list-group-item list-group-item-action" target="_blank">';
                echo '<h5>' . htmlspecialchars($row['title']) . '</h5>';
                echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                echo '</a>';
            }
            echo '</ul>';
        } else {
            echo '<div class="list-group-item">No results found</div>';
        }
        $stmt->close();
    }
    exit;
}


// --- نمایش صفحه کامل جستجو ---
// این بخش فقط برای درخواست‌های GET معمولی است که یک صفحه کامل را می‌خواهند
include 'header.php';

$search_query = isset($_GET['query']) ? $_GET['query'] : '';
$all_results = [];
if (!empty($search_query)) {
    $sql = "SELECT * FROM `presentations` WHERE `title` LIKE ? OR `keywords` LIKE ? ORDER BY `created_at` DESC";
    $stmt = $conn->prepare($sql);
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['is_saved'] = isPresentationSaved($conn, $user_id, $row['id']);
            $all_results[] = $row;
        }
    }
    $stmt->close();
}
?>

<div class="container mt-5">
    <h2>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
    <hr>
    <?php if (!empty($all_results)): ?>
        <div class="list-group">
            <?php foreach ($all_results as $result): ?>
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <a href="<?php echo "..//" . htmlspecialchars($result['file_path']); ?>" target="_blank">
                            <h5 class="mb-1"><?php echo htmlspecialchars($result['title']); ?></h5>
                            <p class="mb-1"><?php echo htmlspecialchars($result['description']); ?></p>
                            <small class="text-muted">Keywords: <?php echo htmlspecialchars($result['keywords']); ?></small>
                        </a>
                    </div>
                    <?php if ($user_id): ?>
                        <?php if ($result['is_saved']): ?>
                            <button class="btn btn-sm btn-danger btn-unsave" data-presentation-id="<?php echo $result['id']; ?>">Unsave</button>
                        <?php else: ?>
                            <button class="btn btn-sm btn-success btn-save" data-presentation-id="<?php echo $result['id']; ?>">Save</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            No results found for "<?php echo htmlspecialchars($search_query); ?>".
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-save, .btn-unsave', function() {
            var button = $(this);
            var presentationId = button.data('presentation-id');
            var action = button.hasClass('btn-save') ? 'save' : 'unsave';

            $.ajax({
                url: 'search.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: action,
                    presentation_id: presentationId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        if (action === 'save') {
                            button.removeClass('btn-success btn-save').addClass('btn-danger btn-unsave').text('Unsave');
                        } else {
                            button.removeClass('btn-danger btn-unsave').addClass('btn-success btn-save').text('Save');
                        }
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                    console.log(xhr.responseText);
                }
            });
        });
    });
</script>

<?php
include 'footer.php';
?>