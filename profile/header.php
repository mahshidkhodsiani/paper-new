<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$unread_message_count = 0;
$pending_requests_count = 0;

$config_path = realpath(__DIR__ . '/../config.php');

if ($config_path && file_exists($config_path)) {
    include_once $config_path;
} else {
    die("خطا: فایل config.php یافت نشد");
}

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
    die("خطا در اتصال به پایگاه داده");
}

function safe($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if (isset($_SESSION['user_data']['id'])) {
    $user_id = $_SESSION['user_data']['id'];

    $stmt_messages = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = FALSE");
    if ($stmt_messages) {
        $stmt_messages->bind_param("i", $user_id);
        $stmt_messages->execute();
        $result_messages = $stmt_messages->get_result();
        $unread_message_count = (int)$result_messages->fetch_row()[0];
        $stmt_messages->close();
    }

    $stmt_connections = $conn->prepare("SELECT COUNT(*) FROM connections WHERE receiver_id = ? AND status = 'pending'");
    if ($stmt_connections) {
        $stmt_connections->bind_param("i", $user_id);
        $stmt_connections->execute();
        $result_connections = $stmt_connections->get_result();
        $pending_requests_count = (int)$result_connections->fetch_row()[0];
        $stmt_connections->close();
    }
}
?>
<style>
    .search-container {
        position: relative;
    }

    .search-results-box {
        position: absolute;
        width: 100%;
        top: 100%;
        z-index: 1000;
        background-color: white;
        border: 1px solid #ccc;
        border-top: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
        max-height: 200px;
        overflow-y: auto;
    }
</style>

<header class="p-3 mb-3 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <a href="./" class="d-flex align-items-center mb-2 mb-lg-0 text-dark text-decoration-none">
                <img src="../images/logo.png" class="img-fluid" height="80" width="80" alt="Logo">
            </a>

            <form id="search-form" class="col-12 col-lg-5 mb-3 mb-lg-0 me-lg-3 mr-2 search-container" style="margin-left: 5px;" role="search" method="GET" action="search.php">
                <input class="form-control" type="search" name="query" placeholder="Search..." aria-label="Search">
                <div id="suggestions" class="search-results-box" style="display: none;"></div>
            </form>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li><a href="#" class="nav-link px-2 link-secondary"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                <li><a href="../people" class="nav-link px-2 link-dark"><i class="fas fa-users"></i> People</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link px-2 link-dark dropdown-toggle" href="#" id="navbarDropdownNotification" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i> Notification
                        <?php if ($unread_message_count > 0 || $pending_requests_count > 0): ?>
                            <span class="badge bg-danger rounded-pill">
                                <?= safe($unread_message_count + $pending_requests_count) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownNotification">
                        <?php if ($unread_message_count > 0): ?>
                            <li><a class="dropdown-item" href="messages.php"><i class="fas fa-envelope me-2"></i> You have <?= safe($unread_message_count) ?> unread message(s)</a></li>
                        <?php endif; ?>
                        <?php if ($pending_requests_count > 0): ?>
                            <li><a class="dropdown-item" href="my_requests.php"><i class="fas fa-user-plus me-2"></i> You have <?= safe($pending_requests_count) ?> new connection request(s)</a></li>
                        <?php endif; ?>
                        <?php if ($unread_message_count == 0 && $pending_requests_count == 0): ?>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-check-circle me-2"></i> No new notifications</a></li>
                        <?php endif; ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="messages.php"><i class="fas fa-inbox me-2"></i> View all messages</a></li>
                        <li><a class="dropdown-item" href="my_requests.php"><i class="fas fa-users me-2"></i> View all connection requests</a></li>
                    </ul>
                </li>
                <li><a href="" class="nav-link px-2 link-dark"><i class="fas fa-flask"></i> Labs</a></li>
            </ul>

            <?php
            if ($_SESSION['user_data']['profile_pic'] == "images/2.png") {
                $pic = "../images/2.png";
            } else {
                $pic = "../" . $_SESSION['user_data']['profile_pic'];
            }
            ?>

            <div class="dropdown text-end">
                <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= $pic ?>" alt="profile" width="32" height="32" class="rounded-circle">
                </a>
                <ul class="dropdown-menu text-small" aria-labelledby="dropdownUser1">
                    <li>
                        <a class="dropdown-item" href="./"><i class="fas fa-user-circle me-2"></i> Profile</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> log out</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        const searchInput = $('input[name="query"]');
        const suggestionsBox = $('#suggestions');
        const searchForm = $('form[role="search"]'); // استفاده از انتخابگر attribute برای پیدا کردن فرم
        let timeout = null;

        function fetchResults(query) {
            if (query.length < 3) {
                suggestionsBox.hide().empty();
                return;
            }

            clearTimeout(timeout);
            timeout = setTimeout(function() {
                $.ajax({
                    url: 'search_live.php', // مسیردهی صحیح به فایل search_live.php
                    type: 'GET',
                    data: {
                        query: query
                    },
                    success: function(data) {
                        suggestionsBox.html(data).show();
                    },
                    error: function() {
                        suggestionsBox.html('<div class="list-group-item">خطا در بارگذاری نتایج.</div>').show();
                    }
                });
            }, 300);
        }

        searchInput.on('keyup', function(e) {
            if (e.key === "Enter" || e.keyCode === 13) {
                searchForm.submit();
            } else {
                fetchResults($(this).val());
            }
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-container').length) {
                suggestionsBox.hide();
            }
        });

        searchInput.on('focus', function() {
            if (suggestionsBox.html().trim() !== '') {
                suggestionsBox.show();
            }
        });
    });
</script>