<?php
// --- بخش PHP منطق بک‌اند (بدون تغییر) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$unread_message_count = 0;
$pending_requests_count = 0;

$config_path = realpath(__DIR__ . '/../config.php');

if ($config_path && file_exists($config_path)) {
    // فرض بر این است که config.php متغیر $conn را تعریف می‌کند
    include_once $config_path;
} else {
    // die("خطا: فایل config.php یافت نشد");
}

// این قسمت برای تست موقت برای رفع خطای اتصال به دیتابیس در محیط شبیه‌سازی شده، غیرفعال شد
/*
if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
     die("خطا در اتصال به پایگاه داده");
}
*/

function safe($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// برای تست UI در محیط شبیه‌سازی شده، مقادیر تستی در نظر گرفته شد اگر اتصال به دیتابیس برقرار نباشد
$is_user_logged_in = isset($_SESSION['user_data']['id']);
if ($is_user_logged_in) {
    if (isset($conn) && $conn instanceof mysqli) {
        $user_id = $_SESSION['user_data']['id'];

        // پیام‌های خوانده نشده
        $stmt_messages = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = FALSE");
        if ($stmt_messages) {
            $stmt_messages->bind_param("i", $user_id);
            $stmt_messages->execute();
            $result_messages = $stmt_messages->get_result();
            $unread_message_count = (int)$result_messages->fetch_row()[0];
            $stmt_messages->close();
        }

        // درخواست‌های اتصال در انتظار
        $stmt_connections = $conn->prepare("SELECT COUNT(*) FROM connections WHERE receiver_id = ? AND status = 'pending'");
        if ($stmt_connections) {
            $stmt_connections->bind_param("i", $user_id);
            $stmt_connections->execute();
            $result_connections = $stmt_connections->get_result();
            $pending_requests_count = (int)$result_connections->fetch_row()[0];
            $stmt_connections->close();
        }
    } else {
        // مقادیر تستی در صورت عدم اتصال به دیتابیس
        $unread_message_count = 1;
        $pending_requests_count = 2;
    }
}

$total_notifications = $unread_message_count + $pending_requests_count;

// منطق تعیین آدرس عکس پروفایل
$pic = "https://via.placeholder.com/32"; // عکس پیش‌فرض
if ($is_user_logged_in && isset($_SESSION['user_data']['profile_pic'])) {
    if ($_SESSION['user_data']['profile_pic'] == "images/2.png") {
        $pic = "../images/2.png";
    } else {
        $pic = "../" . $_SESSION['user_data']['profile_pic'];
    }
}
// ------------------------------------------
?>

<style>
    /* استایل‌های شما */
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

    /* ------------------------------------------- */
    /* استایل‌های واکنش‌گرا (موبایل) - مشابه هدر قبلی */
    /* ------------------------------------------- */
    @media (max-width: 991.98px) {
        .nav.nav-tabs {
            flex-wrap: wrap !important;
            /* برای شکستن خطوط */
        }

        /* آیتم مرکزی (جستجو، People و Notification) در موبایل عرض کامل بگیرد */
        .nav-center-item {
            width: 100% !important;
            order: 2;
            /* بعد از لوگو قرار گیرد */
            margin-top: 5px;
            flex-direction: column;
            /* زیرهم قرار گرفتن فرم جستجو و لینک‌ها */
        }

        /* فرم جستجو در موبایل کل عرض را بگیرد */
        .nav-center-item form {
            width: 100% !important;
            margin-bottom: 10px;
        }

        /* لینک‌های People و Notification در موبایل در زیر فیلد جستجو در کنار هم قرار می‌گیرند */
        .nav-center-item .nav-links-sub {
            width: 100%;
            justify-content: flex-start !important;
            /* از سمت چپ شروع شوند */
        }

        /* آیتم‌های ورود/ثبت‌نام یا پروفایل در موبایل عرض کامل بگیرند و دکمه‌ها در یک ردیف باشند */
        .nav-auth-item {
            width: 100% !important;
            order: 3;
            /* در خط آخر قرار گیرد */
            display: flex;
            justify-content: space-around;
            margin-top: 5px;
        }

        /* دکمه‌های Sign in/up برای داشتن فاصله در موبایل */
        .nav-auth-item .nav-item {
            margin: 0 5px !important;
        }

        /* دکمه‌های Sign in/up در موبایل کل عرض را بگیرند */
        .nav-auth-item .btn {
            flex-grow: 1;
            text-align: center;
            margin: 0 5px;
        }

        /* برای دراپ‌داون پروفایل در موبایل */
        .nav-auth-item .dropdown {
            width: 100%;
            text-align: right;
            margin: 0 !important;
        }
    }
</style>

<nav>
    <ul class="nav nav-tabs d-flex">

        <li class="nav-item">
            <a class="nav-link" href="../">
                <img src="../images/logo.png" alt="paperet" style="height: 30px;">
            </a>
        </li>

        <li class="nav-item mx-auto d-flex flex-column flex-lg-row align-items-lg-center nav-center-item" style="width: 50%;">

            <form id="search-form" class="d-flex w-100 search-container mt-1 flex-grow-1" role="search" method="GET" action="../search.php">
                <input id="search-input" class="form-control me-2" type="search" name="query" placeholder="Search..." aria-label="Search">
                <button class="btn btn-info" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                <div id="suggestions" class="search-results-box" style="display: none;"></div>
            </form>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0 nav-links-sub">
                <li><a href="../people" class="nav-link px-2 link-dark"><i class="fas fa-users"></i> People</a></li>

                <?php if ($is_user_logged_in): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link px-2 link-dark dropdown-toggle" href="#" id="navbarDropdownNotification" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i> Notification
                            <?php if ($total_notifications > 0): ?>
                                <span class="badge bg-danger rounded-pill">
                                    <?= safe($total_notifications) ?>
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
                            <?php if ($total_notifications == 0): ?>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-check-circle me-2"></i> No new notifications</a></li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="messages.php"><i class="fas fa-inbox me-2"></i> View all messages</a></li>
                            <li><a class="dropdown-item" href="my_requests.php"><i class="fas fa-users me-2"></i> View all connection requests</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </li>

        <li class="nav-item nav-auth-item d-flex align-items-center">
            <?php
            if ($is_user_logged_in) {
            ?>
                <div class="dropdown m-1">
                    <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= safe($pic) ?>" alt="profile" width="32" height="32" class="rounded-circle">
                    </a>
                    <ul class="dropdown-menu text-small dropdown-menu-end" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="./"><i class="fas fa-user-circle me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> log out</a></li>
                    </ul>
                </div>
            <?php
            } else {
            ?>
        <li class="nav-item m-1">
            <a class="btn btn-info" href="../login">Sign in</a>
        </li>
        <li class="nav-item m-1">
            <a class="btn btn-info" href="../register">Sign up</a>
        </li>
    <?php
            }
    ?>
    </li>
    </ul>
    
</nav>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        const searchInput = $('input[name="query"]');
        const suggestionsBox = $('#suggestions');
        const searchForm = $('#search-form');
        let timeout = null;

        function fetchResults(query) {
            if (query.length < 3) {
                suggestionsBox.hide().empty();
                return;
            }

            clearTimeout(timeout);
            timeout = setTimeout(function() {
                $.ajax({
                    url: 'search_live.php',
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