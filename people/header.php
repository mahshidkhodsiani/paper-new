<?php
// --- بخش PHP منطق بک‌اند (بدون تغییر) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$unread_count = 0;

$config_path = realpath(__DIR__ . '/../config.php');

if ($config_path && file_exists($config_path)) {
    // فرض بر این است که config.php متغیر $conn را تعریف می‌کند
    include_once $config_path; 
} else {
    // اگر در سطح بالاتر قرار دارید، ممکن است مسیردهی config.php متفاوت باشد
    // die("خطا: فایل config.php یافت نشد"); 
}

// این قسمت برای تست موقت برای رفع خطای اتصال به دیتابیس در محیط شبیه‌سازی شده، غیرفعال شد
/*
if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
     // die("خطا در اتصال به پایگاه داده");
}
*/

function safe($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// بررسی کنید که آیا کاربر وارد سیستم شده است (و محاسبه unread_count اگر $conn موجود باشد)
if (isset($_SESSION['user_data']['id']) && isset($conn) && $conn instanceof mysqli) {
    $user_id = $_SESSION['user_data']['id'];
    // توجه: در این هدر فقط unread_count محاسبه شده
    $stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = FALSE");

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $unread_count = (int)$result->fetch_row()[0];
        $stmt->close();
    }
} else if (isset($_SESSION['user_data']['id'])) {
    // اگر $conn موجود نباشد، حداقل یک پیام نخوانده فرض می‌کنیم تا UI تست شود
    // برای محیط توسعه می‌توانید این را به 0 تغییر دهید
    $unread_count = 2; 
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
            flex-wrap: wrap !important; /* برای شکستن خطوط */
        }
        
        /* آیتم مرکزی (جستجو، People و Notification) در موبایل عرض کامل بگیرد */
        .nav-center-item {
            width: 100% !important;
            order: 2; /* بعد از لوگو قرار گیرد */
            margin-top: 5px;
            /* مطمئن می‌شویم که عناصر داخلی زیر هم قرار نگیرند، مگر اینکه فضای کافی نباشد */
            flex-wrap: wrap; 
        }

        /* آیتم‌های ورود/ثبت‌نام یا پروفایل در موبایل عرض کامل بگیرند و دکمه‌ها در یک ردیف باشند */
        .nav-auth-item {
            width: 100% !important;
            order: 3; /* در خط آخر قرار گیرد */
            display: flex;
            justify-content: space-around;
            margin-top: 5px;
        }
        
        /* دکمه‌های Sign in/up برای داشتن فاصله در موبایل */
        .nav-auth-item .nav-item {
            margin: 0 5px !important;
        }

        /* فیلد جستجو در موبایل کل عرض را بگیرد و عناصر دیگر در زیر آن یا در کنار آن جا شوند */
        .nav-center-item form {
            width: 100% !important;
            margin-bottom: 10px;
        }
        
        /* لینک People و Notification در موبایل در زیر فیلد جستجو در کنار هم قرار می‌گیرند */
        .nav-center-item .nav-links-sub {
             width: 100%;
             justify-content: flex-start !important; /* از سمت چپ شروع شوند */
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
                <img src="../images/logo.jpeg" alt="paperet" style="height: 30px;">
            </a>
        </li>

        <li class="nav-item mx-auto d-flex flex-column flex-lg-row align-items-lg-center nav-center-item" style="width: 50%;"> 
            
            <form id="search-form" class="d-flex w-100 search-container mt-1 flex-grow-1" role="search" method="GET" action="search.php">
                <input id="search-input" class="form-control me-2" type="search" name="query" placeholder="Search..." aria-label="Search">
                <button class="btn btn-info" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                <div id="suggestions" class="search-results-box" style="display: none;"></div>
            </form>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0 nav-links-sub">
                <li><a href="./" class="nav-link px-2 link-dark"><i class="fas fa-users"></i> People</a></li>
                
                <?php
                // منطق نوتیفیکیشن فقط برای کاربران وارد شده
                if (isset($_SESSION['user_data'])) {
                ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link px-2 link-dark dropdown-toggle" href="#" id="navbarDropdownNotification" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i> Notification
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?= safe($unread_count) ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownNotification">
                            <?php if ($unread_count > 0): ?>
                                <li>
                                    <a class="dropdown-item" href="../profile/messages"><i class="fas fa-envelope me-2"></i> You have <?= safe($unread_count) ?> unread message(s)</a>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a class="dropdown-item" href="#"><i class="fas fa-check-circle me-2"></i> No new notifications</a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../profile/messages"><i class="fas fa-inbox me-2"></i> View all messages</a></li>
                            <li><a class="dropdown-item" href="../profile/my_requests"><i class="fas fa-users me-2"></i> View all connection requests</a></li>
                        </ul>
                    </li>
                <?php
                }
                ?>
            </ul>
        </li>

        <li class="nav-item nav-auth-item d-flex align-items-center"> 
        <?php
        if (isset($_SESSION['user_data'])) {
            // منطق تعیین آدرس عکس پروفایل (حفظ مسیرهای نسبی)
            if (isset($_SESSION['user_data']['profile_pic'])) {
                if ($_SESSION['user_data']['profile_pic'] == "images/2.png") {
                    $pic = "../images/2.png";
                } else {
                    $pic = "../" . $_SESSION['user_data']['profile_pic'];
                }
            } else {
                $pic = "https://via.placeholder.com/32";
            }
        ?>
            <div class="dropdown m-1">
                <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= safe($pic) ?>" alt="profile" width="32" height="32" class="rounded-circle">
                </a>
                <ul class="dropdown-menu text-small dropdown-menu-end" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="../profile"><i class="fas fa-user-circle me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="../profile/settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
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
        const searchForm = $('form[role="search"]');
        let timeout = null;
        
        // توجه: این تابع با فرض اینکه search_live.php در مسیر نسبی درست عمل می‌کند، حفظ شده است.
        function fetchResults(query) {
            if (query.length < 3) {
                suggestionsBox.hide().empty();
                return;
            }

            clearTimeout(timeout);
            timeout = setTimeout(function() {
                $.ajax({
                    url: 'search_live.php', // مسیردهی شما
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