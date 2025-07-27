<?php

// شروع سشن برای دسترسی به متغیرهای سشن
session_start();

// حذف تمام متغیرهای سشن
$_SESSION = array();

// اگر از کوکی‌های سشن استفاده می‌شود، کوکی سشن را نیز حذف کنید.
// این کار باعث می‌شود سشن در سمت کلاینت نیز از بین برود.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// نابود کردن سشن
session_destroy();

// هدایت کاربر به صفحه لاگین یا صفحه اصلی
// فرض می‌کنیم login.php در روت پروژه (یک سطح بالاتر) قرار دارد.
// اگر صفحه لاگین شما آدرس دیگری دارد، آن را تغییر دهید.
header("Location: login.php");
exit(); // مهم: بعد از هدایت، اجرای اسکریپت متوقف شود.
