<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function safe($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// دریافت کوئری جستجو برای نمایش در فیلد (در صورت وجود)
$search_query = $_GET['query'] ?? '';

// تعیین مسیر عکس پروفایل (منطق از قبل در هدر شما بود و اینجا اصلاح شده)
$pic = '';
if (isset($_SESSION['user_data']['profile_pic'])) {
    $pic = safe($_SESSION['user_data']['profile_pic']);
    // افزودن یک عکس پیش‌فرض در صورت خالی بودن $pic
    if (empty($pic)) {
        $pic = 'images/default_profile.png'; 
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
    
    /* کلاس‌های سفارشی برای موبایل */
    /* مطمئن می‌شویم که در ابعاد کوچک (تا 991.98px)، آیتم‌ها زیر هم قرار گیرند */
    @media (max-width: 991.98px) {
        .nav.nav-tabs {
            flex-wrap: wrap !important; /* برای شکستن خطوط */
        }
        
        /* آیتم مرکزی (جستجو و People) در موبایل عرض کامل بگیرد و ترتیب آن حفظ شود */
        .nav-center-item {
            width: 100% !important;
            order: 2; /* بعد از لوگو قرار گیرد */
            margin-top: 5px;
        }

        /* آیتم‌های ورود/ثبت‌نام در موبایل عرض کامل بگیرند و دکمه‌ها در یک ردیف باشند */
        .nav-auth-item {
            width: 100% !important;
            order: 3; /* در خط آخر قرار گیرد */
            display: flex;
            justify-content: space-around; /* دکمه‌ها با فاصله از هم قرار گیرند */
            margin-top: 5px;
        }
        
        /* دکمه‌های ورود/ثبت‌نام برای داشتن فاصله در موبایل */
        .nav-auth-item .nav-item {
            margin: 0 5px !important;
        }

        /* لینک People که در داخل باکس جستجو بود، در موبایل پنهان شود */
        .nav-center-item .nav {
             display: none !important;
        }
        
        /* لوگو (آیتم اول) */
        .nav-item:first-child {
            order: 1;
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
            <a class="nav-link" href="./">
                <img src="images/logo.png" alt="paperet" style="height: 30px;">
            </a>
        </li>

        <li class="nav-item mx-auto d-flex align-items-center nav-center-item" style="width: 50%;"> 
            
            <form id="search-form" class="d-flex w-100 search-container mt-1" role="search" method="GET" action="search.php">
                <input id="search-input" class="form-control me-2" type="search" name="query" placeholder="Search" aria-label="Search" value="<?= safe($search_query) ?>">
                <button class="btn btn-info" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                <div id="suggestions" class="search-results-box" style="display: none;"></div>
            </form>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0 d-none d-lg-flex">
                <li><a href="people" class="nav-link px-2 link-dark"><i class="fas fa-users"></i> People</a></li>
                <li></li>
            </ul>
        </li>

        <li class="nav-item nav-auth-item d-flex align-items-center"> 
        <?php
        if (isset($_SESSION['user_data'])) {
        ?>
            <div class="dropdown m-1">
                <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= safe($pic) ?>" alt="profile" width="32" height="32" class="rounded-circle">
                </a>
                <ul class="dropdown-menu text-small dropdown-menu-end" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="profile"><i class="fas fa-user-circle me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="profile/settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> log out</a></li>
                </ul>
            </div>
        <?php
        } else {
        ?>
            <li class="nav-item m-1">
                <a class="btn btn-info" href="login">Sign in</a>
            </li>
            <li class="nav-item m-1">
                <a class="btn btn-info" href="register">Sign up</a>
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
        const searchInput = $('#search-input');
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
            if (suggestionsBox.html().trim() !== '' && searchInput.val().length >= 3) {
                suggestionsBox.show();
            }
        });
    });
</script>

