<?php
// این خط باید اولین خط فایل باشد
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ob_start();

use Google\Client;
use Google\Service\Oauth2;

session_start();

require_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/config.php';

// بررسی اتصال به دیتابیس
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
$client->setRedirectUri(GOOGLE_REDIRECT_URI); // این خط در اینجا هم لازم است
$client->addScope('email');
$client->addScope('profile');

// دریافت توکن از فرم مخفی
if (isset($_POST['credential'])) {
    try {
        $id_token = $_POST['credential'];

        // **مهم‌ترین تغییر:** استفاده از payload برای دریافت اطلاعات
        $payload = $client->verifyIdToken($id_token);

        if (!$payload) {
            ob_end_clean();
            $_SESSION['login_error'] = 'خطا در تأیید توکن گوگل.';
            header('Location: login.php');
            exit();
        }

        // استخراج اطلاعات کاربر از payload
        $google_id = $payload['sub'];
        $email = $payload['email'];
        $first_name = $payload['given_name'] ?? '';
        $last_name = $payload['family_name'] ?? '';
        $profile_pic = $payload['picture'] ?? '';
        $is_google_user = 1;

        // بررسی وجود کاربر در دیتابیس
        $stmt = $conn->prepare("SELECT id, name, family, email, profile_pic, google_id FROM users WHERE email = ? OR google_id = ?");
        $stmt->bind_param("ss", $email, $google_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $user_data = [];

        if ($result->num_rows > 0) {
            // کاربر وجود دارد
            $user = $result->fetch_assoc();

            if (empty($user['google_id'])) {
                $update_stmt = $conn->prepare("UPDATE users SET google_id = ?, is_google_user = 1 WHERE id = ?");
                $update_stmt->bind_param("si", $google_id, $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
                // اطلاعات کاربر را به‌روزرسانی می‌کنیم
                $user['google_id'] = $google_id;
                $user['is_google_user'] = 1;
            }

            $user_data = $user;
        } else {
            // کاربر جدید است، ثبت‌نام می‌کنیم
            $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO users (google_id, name, family, email, password, profile_pic, is_google_user) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssssssi", $google_id, $first_name, $last_name, $email, $password_hash, $profile_pic, $is_google_user);

            if ($stmt_insert->execute()) {
                // ثبت‌نام موفق، اطلاعات سشن را تنظیم می‌کنیم
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
                ob_end_clean();
                $_SESSION['login_error'] = 'خطا در ثبت‌نام کاربر جدید: ' . $stmt_insert->error;
                header('Location: login.php');
                exit();
            }
            $stmt_insert->close();
        }

        $stmt->close();
        $conn->close();

        // تنظیم سشن به فرمت استاندارد کد لاگین معمولی شما
        if (!empty($user_data)) {
            unset($user_data['password']); // برای امنیت، رمز عبور را حذف کنید
            $_SESSION['user_data'] = $user_data;
            $_SESSION['logged_in'] = true;
            ob_end_clean();
            header('Location: profile');
            exit();
        } else {
            ob_end_clean();
            $_SESSION['login_error'] = 'خطای ناشناخته در ورود با گوگل.';
            header('Location: login.php');
            exit();
        }
    } catch (Exception $e) {
        ob_end_clean();
        $_SESSION['login_error'] = 'خطا در احراز هویت گوگل: ' . $e->getMessage();
        header('Location: login.php');
        exit();
    }
} else {
    // این بخش برای زمانی است که کاربر مستقیماً به این صفحه وارد می‌شود یا از طریق Google Redirect URI
    if (isset($_GET['code'])) {
        try {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            if (isset($token['error'])) {
                ob_end_clean();
                $_SESSION['login_error'] = 'خطا در دریافت توکن: ' . $token['error_description'];
                header('Location: login.php');
                exit();
            }
            $client->setAccessToken($token);
            $oauth2 = new Oauth2($client);
            $userInfo = $oauth2->userinfo->get();

            $google_id = $userInfo->id;
            $email = $userInfo->email;
            $first_name = $userInfo->givenName ?? '';
            $last_name = $userInfo->familyName ?? '';
            $profile_pic = $userInfo->picture ?? '';
            $is_google_user = 1;

            // اینجا همان منطق ثبت نام یا ورود را دوباره قرار دهید
            // ... (همان منطق بخش POST)
            $stmt = $conn->prepare("SELECT id, name, family, email, profile_pic, google_id FROM users WHERE email = ? OR google_id = ?");
            $stmt->bind_param("ss", $email, $google_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $user_data = [];

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (empty($user['google_id'])) {
                    $update_stmt = $conn->prepare("UPDATE users SET google_id = ?, is_google_user = 1 WHERE id = ?");
                    $update_stmt->bind_param("si", $google_id, $user['id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                    $user['google_id'] = $google_id;
                    $user['is_google_user'] = 1;
                }
                $user_data = $user;
            } else {
                $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                $stmt_insert = $conn->prepare("INSERT INTO users (google_id, name, family, email, password, profile_pic, is_google_user) VALUES (?, ?, ?, ?, ?, ?, ?)");
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
                    ob_end_clean();
                    $_SESSION['login_error'] = 'خطا در ثبت‌نام کاربر جدید: ' . $stmt_insert->error;
                    header('Location: login.php');
                    exit();
                }
                $stmt_insert->close();
            }
            $stmt->close();
            $conn->close();

            if (!empty($user_data)) {
                unset($user_data['password']);
                $_SESSION['user_data'] = $user_data;
                $_SESSION['logged_in'] = true;
                ob_end_clean();
                header('Location: profile');
                exit();
            } else {
                ob_end_clean();
                $_SESSION['login_error'] = 'خطای ناشناخته در ورود با گوگل.';
                header('Location: login.php');
                exit();
            }
        } catch (Exception $e) {
            ob_end_clean();
            $_SESSION['login_error'] = 'خطا در احراز هویت گوگل: ' . $e->getMessage();
            header('Location: login.php');
            exit();
        }
    } else {
        ob_end_clean();
        $_SESSION['login_error'] = 'عملیات ورود لغو شد.';
        header('Location: login.php');
        exit();
    }
}
