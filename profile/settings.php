<?php

session_start();
if (!isset($_SESSION['user_data']) || !isset($_SESSION['user_data']['id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_data']['id'];

include "../config.php";

$message = '';
$messageType = '';

// --- شروع بخش پردازش فرم POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // گرفتن و فیلتر کردن داده های ورودی
    $name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $family = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $university = filter_input(INPUT_POST, 'university', FILTER_SANITIZE_STRING);
    $birthdate = filter_input(INPUT_POST, 'birthdate', FILTER_SANITIZE_STRING);
    $education = filter_input(INPUT_POST, 'education', FILTER_SANITIZE_STRING);
    $workplace = filter_input(INPUT_POST, 'workplace', FILTER_SANITIZE_STRING);
    $meeting_info = filter_input(INPUT_POST, 'meeting_info', FILTER_SANITIZE_STRING);
    $linkedin_url = filter_input(INPUT_POST, 'linkedin_url', FILTER_SANITIZE_URL);
    $x_url = filter_input(INPUT_POST, 'x_url', FILTER_SANITIZE_URL);
    $google_scholar_url = filter_input(INPUT_POST, 'google_scholar_url', FILTER_SANITIZE_URL);
    $github_url = filter_input(INPUT_POST, 'github_url', FILTER_SANITIZE_URL);
    $website_url = filter_input(INPUT_POST, 'website_url', FILTER_SANITIZE_URL);

    $password_new = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $password_confirm = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

    $updateFields = [];
    $bindParams = '';
    $bindValues = [];

    // اضافه کردن فیلدهای متنی به لیست برای UPDATE
    if ($name !== null) {
        $updateFields[] = "name = ?";
        $bindParams .= "s";
        $bindValues[] = $name;
    }
    if ($family !== null) {
        $updateFields[] = "family = ?";
        $bindParams .= "s";
        $bindValues[] = $family;
    }
    if ($university !== null) {
        $updateFields[] = "university = ?";
        $bindParams .= "s";
        $bindValues[] = $university;
    }
    if ($birthdate !== null) {
        $updateFields[] = "birthdate = ?";
        $bindParams .= "s";
        $bindValues[] = $birthdate;
    }
    if ($education !== null) {
        $updateFields[] = "education = ?";
        $bindParams .= "s";
        $bindValues[] = $education;
    }
    if ($workplace !== null) {
        $updateFields[] = "workplace = ?";
        $bindParams .= "s";
        $bindValues[] = $workplace;
    }
    if ($meeting_info !== null) {
        $updateFields[] = "meeting_info = ?";
        $bindParams .= "s";
        $bindValues[] = $meeting_info;
    }
    if ($linkedin_url !== null) {
        $updateFields[] = "linkedin_url = ?";
        $bindParams .= "s";
        $bindValues[] = $linkedin_url;
    }
    if ($x_url !== null) {
        $updateFields[] = "x_url = ?";
        $bindParams .= "s";
        $bindValues[] = $x_url;
    }
    if ($google_scholar_url !== null) {
        $updateFields[] = "google_scholar_url = ?";
        $bindParams .= "s";
        $bindValues[] = $google_scholar_url;
    }
    if ($github_url !== null) {
        $updateFields[] = "github_url = ?";
        $bindParams .= "s";
        $bindValues[] = $github_url;
    }
    if ($website_url !== null) {
        $updateFields[] = "website_url = ?";
        $bindParams .= "s";
        $bindValues[] = $website_url;
    }

    // --- مدیریت رمز عبور ---
    if (!empty($password_new) && !empty($password_confirm)) {
        if ($password_new === $password_confirm) {
            $hashed_password = password_hash($password_new, PASSWORD_DEFAULT);
            $updateFields[] = "password = ?";
            $bindParams .= "s";
            $bindValues[] = $hashed_password;
        } else {
            $message = 'رمزهای عبور با یکدیگر مطابقت ندارند.';
            $messageType = 'danger';
        }
    } elseif (!empty($password_new) && empty($password_confirm)) {
        $message = 'لطفاً رمز عبور جدید را تأیید کنید.';
        $messageType = 'danger';
    }

    // --- مدیریت آپلود تصویر پروفایل ---
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        // مسیر روت پروژه (یک سطح بالاتر از پوشه 'profile')
        $baseUploadDir = '../uploads/pics/';
        $userUploadDir = $baseUploadDir . $userId . '/'; // پوشه اختصاصی کاربر

        // ایجاد پوشه کاربر اگر وجود ندارد
        if (!is_dir($userUploadDir)) {
            mkdir($userUploadDir, 0775, true); // 0775 permissions, true برای ایجاد پوشه های تو در تو
        }

        $fileName = basename($_FILES['profile_image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']; // افزودن gif در صورت نیاز

        if (in_array($fileExt, $allowedTypes)) {
            // نام عکس پروفایل: نام منحصر به فرد + پسوند
            $newFileName = 'profile_pic_' . uniqid() . '.' . $fileExt;
            $uploadFilePath = $userUploadDir . $newFileName;


            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFilePath)) {
                // آدرس نسبی که در دیتابیس ذخیره می شود
                // فرض بر این است که فایل settings.php در profile/ قرار دارد
                // و uploads/pics/user_id/pic_name.jpg از روت قابل دسترسی است
                $dbFilePath = 'uploads/pics/' . $userId . '/' . $newFileName;

                $updateFields[] = "profile_pic = ?";
                $bindParams .= "s";
                $bindValues[] = $dbFilePath;
                $message = 'تصویر پروفایل با موفقیت آپلود شد. ';
            } else {
                $message = 'خطا در آپلود تصویر پروفایل. ';
                $messageType = 'danger';
            }
        } else {
            $message = 'نوع فایل تصویر پروفایل نامعتبر است. فقط JPG, JPEG, PNG, GIF مجاز هستند. ';
            $messageType = 'danger';
        }
    }


    if (!empty($updateFields) && $messageType !== 'danger') {
        $sql_update = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $bindParams .= "i";
        $bindValues[] = $userId;

        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update) {
            call_user_func_array([$stmt_update, 'bind_param'], array_merge([$bindParams], $bindValues));

            if ($stmt_update->execute()) {
                $message = 'اطلاعات با موفقیت به روزرسانی شد!';
                $messageType = 'success';
                // بعد از بروزرسانی موفقیت آمیز، اطلاعات session را نیز بروز کنید
                // و صفحه را رفرش کنید تا تغییرات در فرم منعکس شوند
                // (مهم: فقط فیلدهایی که در سشن نگه می دارید را بروز کنید)
                $_SESSION['user_data']['name'] = $name;
                $_SESSION['user_data']['family'] = $family;
                // اگر profile_pic هم در سشن ذخیره می کنید، آن را نیز بروز کنید
                if (isset($dbFilePath)) {
                    $_SESSION['user_data']['profile_pic'] = $dbFilePath;
                }

                header("Location: settings.php?status=success&msg=" . urlencode($message));
                exit();
            } else {
                $message = 'خطا در به روزرسانی اطلاعات: ' . $stmt_update->error;
                $messageType = 'danger';
            }
            $stmt_update->close();
        } else {
            $message = 'خطا در آماده سازی کوئری به روزرسانی: ' . $conn->error;
            $messageType = 'danger';
        }
    } elseif (empty($updateFields) && $messageType !== 'danger') {
        $message = 'هیچ اطلاعاتی برای به روزرسانی وجود نداشت.';
        $messageType = 'info';
    }

    // اگر پیام از طریق ریدایرکت ارسال شده باشد
    // این قسمت باید قبل از کد لود اطلاعات کاربر باشد
    if (isset($_GET['status']) && isset($_GET['msg'])) {
        $messageType = $_GET['status'];
        $message = urldecode($_GET['msg']);
    }
}

// این قسمت برای لود کردن اطلاعات کاربر بعد از ارسال فرم (یا بارگذاری اولیه صفحه) است
// مطمئن شوید که $conn قبل از این بخش، دوباره باز شده باشد اگر در بخش POST بسته شده بود
// اگر در config.php فقط یک بار $conn تعریف و وصل می شود و در صورت نیاز re-include می شود، مشکلی نیست
// اگر اتصال در بخش POST بسته می شود، باید اینجا دوباره آن را باز کنید یا از یک سیستم مدیریت اتصال بهتر استفاده کنید.
// به دلیل اینکه در انتهای هر دو شاخه (POST و غیر POST) اتصال بسته می شود، مشکل خاصی پیش نمی آید.

$conn = new mysqli($servername, $username, $password, $dbname); // بازگشایی مجدد اتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4"); // تنظیم charset

$user = [];
$sql = "SELECT id, name, family, email, profile_pic, university, birthdate, education, workplace, meeting_info, linkedin_url, x_url, google_scholar_url, github_url, website_url FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}

$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>

    <?php include "../includes.php"; ?>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <?php include "header.php"; ?>

    <div class="container">
        <div class="row">

            <?php include "sidebar.php"; ?>

            <div class="col-md-6">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">
                    <h4 class="mb-4">Account Settings</h4>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="profileImage" class="form-label">Profile Image</label>
                            <div class="d-flex align-items-center">
                                <img src="../<?php echo htmlspecialchars($user['profile_pic'] ?? '../images/2.png'); ?>" alt="Profile Picture" class="img-thumbnail rounded-circle me-3" style="width: 100px; height: 100px; object-fit: cover;">
                                <div>
                                    <input type="file" class="form-control" id="profileImage" name="profile_image" accept="image/png, image/jpeg, image/gif"> <small class="form-text text-muted">PNG, JPG or GIF file (max 2MB)</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($user['family'] ?? ''); ?>">
                        </div>

                        <div class="mb-3" id="passwordGroup">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" disabled>
                                <button class="btn btn-outline-secondary" type="button" id="editPasswordBtn">Edit Password</button>
                            </div>
                            <small class="form-text text-muted" id="passwordStrength">Password strength: Weak</small>
                        </div>

                        <div class="mb-3" id="confirmPasswordGroup" style="display: none;">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
                            <small class="form-text text-muted">Please re-enter your password.</small>
                            <button class="btn btn-outline-secondary mt-2" type="button" id="cancelPasswordEditBtn">Cancel</button>
                        </div>

                        <div class="mb-3">
                            <label for="university" class="form-label">University</label>
                            <input type="text" class="form-control" id="university" name="university" value="<?php echo htmlspecialchars($user['university'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="birthdate" class="form-label">Birthdate</label>
                            <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="education" class="form-label">Education</label>
                            <input type="text" class="form-control" id="education" name="education" value="<?php echo htmlspecialchars($user['education'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="workplace" class="form-label">Workplace</label>
                            <input type="text" class="form-control" id="workplace" name="workplace" value="<?php echo htmlspecialchars($user['workplace'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="meetingInfo" class="form-label">Meeting Info</label>
                            <textarea class="form-control" id="meetingInfo" name="meeting_info" rows="4"><?php echo htmlspecialchars($user['meeting_info'] ?? 'Mon - Fri: 9:00 AM - 5:00 PM (CST)'); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="linkedin" class="form-label">LinkedIn Profile Link</label>
                            <input type="url" class="form-control" id="linkedin" name="linkedin_url" value="<?php echo htmlspecialchars($user['linkedin_url'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="xProfile" class="form-label">X Profile Link</label>
                            <input type="url" class="form-control" id="xProfile" name="x_url" value="<?php echo htmlspecialchars($user['x_url'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="googleScholar" class="form-label">Google Scholar Profile Link</label>
                            <input type="url" class="form-control" id="googleScholar" name="google_scholar_url" value="<?php echo htmlspecialchars($user['google_scholar_url'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="github" class="form-label">Github Profile Link</label>
                            <input type="url" class="form-control" id="github" name="github_url" value="<?php echo htmlspecialchars($user['github_url'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="websiteLink" class="form-label">Website Link</label>
                            <input type="url" class="form-control" id="websiteLink" name="website_url" value="<?php echo htmlspecialchars($user['website_url'] ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">Update</button>
                    </form>
                </div>
            </div>

            <div class="col-md-3">
                <div class="optional-sidebar shadow-sm p-3 mb-5 bg-white rounded">
                    <h4>Optional Sidebar</h4>
                    <p>This sidebar is always visible.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar menu highlight logic
            const menuItems = document.querySelectorAll('.list-group-item-action');
            const currentPage = 'settings.php';

            menuItems.forEach(item => {
                item.classList.remove('active');
                const linkHref = item.getAttribute('href');
                const linkFileName = linkHref.split('/').pop();

                if (linkFileName === currentPage) {
                    item.classList.add('active');
                }
            });

            // Password field show/hide logic
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const editPasswordBtn = document.getElementById('editPasswordBtn');
            const cancelPasswordEditBtn = document.getElementById('cancelPasswordEditBtn');
            const confirmPasswordGroup = document.getElementById('confirmPasswordGroup');

            editPasswordBtn.addEventListener('click', function() {
                passwordInput.removeAttribute('disabled');
                passwordInput.focus();
                confirmPasswordGroup.style.display = 'block';
                editPasswordBtn.style.display = 'none';
                passwordInput.value = '';
                confirmPasswordInput.value = '';
            });

            cancelPasswordEditBtn.addEventListener('click', function() {
                passwordInput.setAttribute('disabled', 'disabled');
                confirmPasswordGroup.style.display = 'none';
                editPasswordBtn.style.display = 'inline-block';
                passwordInput.value = '';
                confirmPasswordInput.value = '';
            });
        });
    </script>
</body>

</html>