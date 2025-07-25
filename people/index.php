<?php


include(__DIR__ . '/../config.php');

// بلافاصله بعد از include کردن config.php، معتبر بودن اتصال را بررسی کنید
if (!($conn instanceof mysqli) || $conn->connect_error) {
    // اگر اتصال برقرار نشد، یک پیام خطای کاربرپسند نمایش دهید و اجرا را متوقف کنید
    die("<div class='alert alert-danger container mt-5'>System temporarily unavailable. Please try again later. (DB Connection Error)</div>");
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>People - User List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }

        .user-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            text-align: center;
        }

        .cover-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            margin-top: -50px;
            position: relative;
            z-index: 1;
        }

        .card-body-custom {
            padding: 15px;
            margin-top: -20px;
        }

        .card-title-custom {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .icon-text {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #555;
        }

        .icon-text svg {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            fill: #54595F;
        }

        .btn-custom {
            width: 80%;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .search-box {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <?php
    // فایل header.php را شامل کنید
    // فرض بر این است که header.php خود به $conn نیاز دارد و آن را include نمی‌کند.
    include 'header.php';
    ?>

    <div class="container mt-5">
        <div class="row">
            <?php
            try {
                // Query to fetch all users from the database
                $sql = "SELECT id, name, family, education, university, profile_pic FROM users";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    // Display each user in a card
                    while ($row = $result->fetch_assoc()) {
                        // استفاده از Null Coalescing Operator (??) برای جلوگیری از ارسال null به htmlspecialchars
                        $fullName = htmlspecialchars($row["name"] . " " . $row["family"]);
                        $education = htmlspecialchars($row["education"] ?? 'نامشخص'); // اگر education null بود، 'نامشخص' نمایش داده شود
                        $university = htmlspecialchars($row["university"] ?? 'نامشخص'); // اگر university null بود، 'نامشخص' نمایش داده شود
                        $profilePic = htmlspecialchars($row["profile_pic"] ?? 'https://via.placeholder.com/100'); // اگر profile_pic null بود، تصویر پیش‌فرض نمایش داده شود

                        $coverPic = 'https://via.placeholder.com/800x450'; // Default cover image

                        // اطمینان از اینکه id به عدد صحیح تبدیل می‌شود برای امنیت
                        $profileLink = "profile.php?id=" . (int)$row["id"];
            ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="user-card">
                                <img src="<?= $coverPic ?>" class="cover-image" alt="Cover Image">
                                <img src="<?= $profilePic ?>" class="profile-picture" alt="Profile Picture">
                                <div class="card-body-custom">
                                    <h5 class="card-title-custom"><?= $fullName ?></h5>
                                    <div class="icon-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#54595F" viewBox="0 0 50 50" overflow="inherit">
                                            <path d="M30 8v33h-24v-33h24m4-4h-32v42h32v-42zm-25 8h18v4h-18zm0 7h18v4h-18zm0 7h18v4h-18zm0 7h18v4h-18zm31-21h8v28h-8zm4.006-11c-2.194 0-4.006 1.765-4.006 3.937v4.063h8v-4.063c0-2.172-1.809-3.937-3.994-3.937zm-4.068 42l4.041 6.387 4.021-6.387z"></path>
                                        </svg>
                                        <span>Education: <?= $education ?></span>
                                    </div>
                                    <div class="icon-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#54595F" viewBox="0 0 50 50" overflow="inherit">
                                            <path d="M24.999 27.381c-5.406 0-9.999 1.572-12.999 4.036v4.583h26v-4.583c-3-2.464-7.594-4.036-13.001-4.036zm23.871-2.352l-23.934-11.029-23.924 11.029 3.988 1.825v2.807c-1 .207-1.003.731-1.003 1.354 0 .368.122.799.354 1.057l-1.368 2.928h4.88l-1.356-2.93c.228-.258.415-.638.415-1.006 0-.622-.922-1.197-.922-1.404v-2.337l5 2.246v-.199c3-2.609 8.271-4.265 13.998-4.265 5.729 0 11.002 1.656 14.002 4.265v.199l9.87-4.54z"></path>
                                        </svg>
                                        <span>University: <?= $university ?></span>
                                    </div>
                                    <a href="#" class="btn btn-outline-primary btn-sm btn-custom"><i class="fas fa-user-plus"></i> Connect</a>
                                    <a href="<?= $profileLink ?>" class="btn btn-primary btn-sm btn-custom"><i class="fas fa-user-graduate"></i> View Profile</a>
                                </div>
                            </div>
                        </div>
            <?php
                    }
                } else {
                    echo "<div class='col-12 text-center'><p>No users found.</p></div>";
                }
            } catch (Exception $e) {
                // ثبت خطا در لاگ سرور
                error_log("Query error in index.php: " . $e->getMessage());
                echo "<div class='col-12 alert alert-danger'>Error loading user data. Please try again later.</div>";
            } finally {
                // اطمینان از بسته شدن اتصال به دیتابیس
                if ($conn instanceof mysqli) {
                    $conn->close();
                }
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>