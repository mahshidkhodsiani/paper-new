<?php
// ** نمایش کامل خطاها برای Debugging **
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

use Google\Client;
use Google\Service\Oauth2;

session_start();

// مسیر فایل‌ها را به دقت بررسی کنید
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

// ** بررسی اولیه اتصال به دیتابیس **
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    ob_end_clean();
    $_SESSION['login_error'] = 'خطا در اتصال به دیتابیس: ' . $conn->connect_error;
    header('Location: login.php');
    exit();
}

$client = new Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope('email');
$client->addScope('profile');

/**
 * تابعی برای مدیریت فرآیند ورود/ثبت نام کاربر
 *
 * @param mysqli $conn اتصال به دیتابیس
 * @param array $user_info اطلاعات کاربر از گوگل
 */
function handle_user_login($conn, $user_info)
{
    $google_id = $user_info['sub'] ?? null;
    $email = $user_info['email'] ?? null;
    if (empty($email)) {
        $_SESSION['login_error'] = 'اطلاعات ایمیل از گوگل دریافت نشد.';
        return;
    }

    $first_name = $user_info['given_name'] ?? '';
    $last_name = $user_info['family_name'] ?? '';
    $profile_pic = $user_info['picture'] ?? '';
    $is_google_user = 1;

    // بررسی وجود کاربر در دیتابیس
    $stmt = $conn->prepare("SELECT id, name, family, email, password, profile_pic, google_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $user_data = null;
    if ($result->num_rows > 0) {
        // کاربر وجود دارد
        $user = $result->fetch_assoc();
        // اگر کاربر قبلی بدون Google ID بود، آن را به روزرسانی می‌کنیم
        if (empty($user['google_id'])) {
            $update_stmt = $conn->prepare("UPDATE users SET google_id = ?, is_google_user = 1 WHERE id = ?");
            $update_stmt->bind_param("si", $google_id, $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
            $user_data = $user;
        } else {
            $user_data = $user;
        }
    } else {
        // کاربر جدید است، ثبت نام می‌کنیم
        $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $stmt_insert = $conn->prepare("INSERT INTO users (google_id, name, family, email, password, profile_pic, is_google_user) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt_insert) {
            $stmt_insert->bind_param("ssssssi", $google_id, $first_name, $last_name, $email, $password_hash, $profile_pic, $is_google_user);
            if ($stmt_insert->execute()) {
                $user_data = [
                    'id' => $conn->insert_id,
                    'google_id' => $google_id,
                    'name' => $first_name,
                    'family' => $last_name,
                    'email' => $email,
                    'profile_pic' => $profile_pic,
                    'is_google_user' => 1
                ];
            } else {
                $_SESSION['login_error'] = 'خطا در ثبت‌نام کاربر جدید: ' . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            $_SESSION['login_error'] = 'خطا در آماده‌سازی کوئری ثبت‌نام: ' . $conn->error;
        }
    }

    $stmt->close();
    $conn->close();

    // تنظیم سشن و هدایت به صفحه پروفایل
    if (!empty($user_data) && !isset($_SESSION['login_error'])) {
        unset($user_data['password']);
        $_SESSION['user_data'] = $user_data;
        $_SESSION['logged_in'] = true;
        ob_end_clean();
        header('Location: profile');
        exit();
    }
}

try {
    if (isset($_POST['credential'])) {
        $id_token = $_POST['credential'];
        $payload = $client->verifyIdToken($id_token);
        if (!$payload) {
            $_SESSION['login_error'] = 'خطا در تأیید توکن گوگل.';
        } else {
            handle_user_login($conn, $payload);
        }
    } elseif (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            $_SESSION['login_error'] = 'خطا در دریافت توکن: ' . ($token['error_description'] ?? 'ناشناس');
        } else {
            $client->setAccessToken($token);
            $oauth2 = new Oauth2($client);
            $userInfo = $oauth2->userinfo->get();
            $user_info_array = [
                'id' => $userInfo->id,
                'email' => $userInfo->email,
                'given_name' => $userInfo->givenName,
                'family_name' => $userInfo->familyName,
                'picture' => $userInfo->picture
            ];
            handle_user_login($conn, $user_info_array);
        }
    } else {
        $_SESSION['login_error'] = 'عملیات ورود لغو شد.';
    }
} catch (Exception $e) {
    $_SESSION['login_error'] = 'خطا در احراز هویت گوگل: ' . $e->getMessage();
}

// اگر هیچکدام از موارد بالا به ریدایرکت نرسید، به صفحه لاگین برگردان
if (!isset($_SESSION['logged_in'])) {
    ob_end_clean();
    header('Location: login.php');
    exit();
}
