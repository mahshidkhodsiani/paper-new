<?php

session_start();

// برای عیب یابی: این خطوط رو موقتاً فعال کنید تا سشن رو ببینید
// error_log("save_presentation.php - SESSION: " . print_r($_SESSION, true));
// error_log("save_presentation.php - POST: " . print_r($_POST, true));


include "../config.php"; // این فایل باید اتصال به دیتابیس ($conn) را فراهم کند

// متغیرها برای پیام و نوع پیام
$message = '';
$messageType = 'danger'; // پیش فرض: خطا

// آدرس صفحه پروفایل برای ریدایرکت
// فرض می کنیم save_presentation.php در 'paper-new/profile/' است
// و profile.php در 'paper-new/people/' است.
$redirectBaseUrl = '../../people/profile.php';
$profileIdToRedirectTo = null;

// دریافت ID پروفایل از URL ارجاع دهنده (HTTP_REFERER)
// این روش امن نیست و فقط برای تست موقت مناسب است.
// روش بهتر این است که profile_id از طریق یک فیلد hidden در فرم ارسال شود.
if (isset($_SERVER['HTTP_REFERER'])) {
    $refererParts = parse_url($_SERVER['HTTP_REFERER']);
    if (isset($refererParts['query'])) {
        parse_str($refererParts['query'], $refererQuery);
        if (isset($refererQuery['id'])) {
            $profileIdToRedirectTo = $refererQuery['id'];
        }
    }
}

// تابع کمکی برای ساخت URL ریدایرکت
function buildRedirectUrl($baseUrl, $profileId, $status, $msg)
{
    $url = $baseUrl;
    $params = [];
    if ($profileId) {
        $params[] = 'id=' . urlencode($profileId);
    }
    $params[] = 'status=' . urlencode($status);
    $params[] = 'msg=' . urlencode($msg);

    if (!empty($params)) {
        $url .= '?' . implode('&', $params);
    }
    return $url;
}


// 1. بررسی ورود کاربر
// شما در saved_presentations.php از $_SESSION['user_data']['id'] استفاده می کردید.
// باید اطمینان حاصل کنید که $_SESSION['user_id'] همان چیزی است که انتظار دارید.
// اگر user_id در user_data['id'] ذخیره می شود، از آن استفاده کنید.
$loggedInUserId = $_SESSION['user_id'] ?? ($_SESSION['user_data']['id'] ?? null);

if (!$loggedInUserId) {
    $message = 'لطفاً برای ذخیره ارائه وارد شوید.';
    $messageType = 'danger';
    header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
    exit();
}

// 2. بررسی presentation_id
if (!isset($_POST['presentation_id']) || !is_numeric($_POST['presentation_id'])) {
    $message = 'شناسه ارائه نامعتبر است.';
    $messageType = 'danger';
    header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
    exit();
}

$presentationId = intval($_POST['presentation_id']);

// 3. بررسی اینکه آیا ارائه قبلاً ذخیره شده است (اختیاری اما توصیه شده)
$check_sql = "SELECT id FROM saved_presentations WHERE user_id = ? AND presentation_id = ?";
// اطمینان حاصل کنید که $conn در این نقطه در دسترس است
if (!isset($conn) || $conn->connect_error) {
    $message = 'خطا در اتصال به دیتابیس برای بررسی وضعیت ذخیره: ' . ($conn->connect_error ?? 'ناشناخته');
    $messageType = 'danger';
    header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
    exit();
}

$check_stmt = $conn->prepare($check_sql);
if ($check_stmt) {
    $check_stmt->bind_param("ii", $loggedInUserId, $presentationId);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $message = 'این ارائه قبلاً ذخیره شده است.';
        $messageType = 'warning';
        $check_stmt->close();
        header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
        exit();
    }
    $check_stmt->close();
} else {
    $message = 'خطا در آماده‌سازی کوئری بررسی ذخیره: ' . $conn->error;
    $messageType = 'danger';
    header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
    exit();
}


// 4. درج در دیتابیس
$insert_sql = "INSERT INTO saved_presentations (user_id, presentation_id) VALUES (?, ?)";
$insert_stmt = $conn->prepare($insert_sql);

if ($insert_stmt) {
    $insert_stmt->bind_param("ii", $loggedInUserId, $presentationId);
    if ($insert_stmt->execute()) {
        $message = 'ارائه با موفقیت ذخیره شد!';
        $messageType = 'success';
    } else {
        // اگر درج با خطا مواجه شد، خطای دقیق را ثبت کنید
        $message = 'خطا در ذخیره ارائه: ' . $insert_stmt->error;
        $messageType = 'danger';
    }
    $insert_stmt->close();
} else {
    // اگر آماده‌سازی کوئری درج با خطا مواجه شد
    $message = 'خطا در آماده‌سازی کوئری ذخیره: ' . $conn->error;
    $messageType = 'danger';
}

// **مهم:** اتصال به دیتابیس را در اینجا نبندید.
// باید در config.php مدیریت شود یا در انتهای اسکریپت اصلی.
// $conn->close();

// ریدایرکت نهایی به همراه پیام
header("Location: " . buildRedirectUrl($redirectBaseUrl, $profileIdToRedirectTo, $messageType, $message));
exit();
