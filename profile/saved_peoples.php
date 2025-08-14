<?php
session_start(); // Start the session at the very beginning

// Include database configuration
// فرض بر این است که config.php یک سطح بالاتر از saved-peoples.php قرار دارد.
include(__DIR__ . '/../config.php');

// Check for valid database connection
if (!($conn instanceof mysqli) || $conn->connect_error) {
    die("<div class='alert alert-danger container mt-5'>System temporarily unavailable. Please try again later. (DB Connection Error)</div>");
}

// Check if user is logged in
$current_user_id = null;
if (isset($_SESSION['user_data']['id'])) {
    $current_user_id = $_SESSION['user_data']['id'];
} else {
    // If not logged in, redirect to login page
    header("Location: ../login.php"); // فرض می‌کنیم login.php در یک سطح بالاتر قرار دارد
    exit();
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Connections</title>
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
            position: relative;
        }

        .cover-photo {
            width: 100%;
            height: 120px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .profile-pic-container {
            position: absolute;
            top: 60px;
            /* Half of cover height */
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            width: 100px;
            height: 100px;
        }

        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
        }

        .card-body-custom {
            padding: 15px;
            padding-top: 55px;
            /* Adjust top padding to account for the profile picture */
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
    </style>
</head>

<body>

    <?php
    // Include header.php
    // فرض بر این است که header.php در همان سطح saved-peoples.php قرار دارد.
    include 'header.php';
    ?>

    <div class="container mt-4">
        <div class="row">
            <?php
            // Include sidebar.php
            // فرض بر این است که sidebar.php در همان سطح saved-peoples.php قرار دارد.
            include 'sidebar.php';
            ?>

            <div class="col-md-9">
                <h3 class="mb-4">My Connections</h3>
                <div class="row">
                    <?php
                    try {
                        // Query to fetch accepted connections for the current user
                        $sql = "SELECT 
                                    CASE 
                                        WHEN c.sender_id = ? THEN c.receiver_id 
                                        ELSE c.sender_id 
                                    END AS connected_user_id
                                FROM connections c
                                WHERE (c.sender_id = ? OR c.receiver_id = ?) 
                                AND c.status = 'accepted'";

                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $connected_user_ids = [];
                        while ($row = $result->fetch_assoc()) {
                            $connected_user_ids[] = $row['connected_user_id'];
                        }
                        $stmt->close();

                        if (!empty($connected_user_ids)) {
                            // Convert array of IDs to a comma-separated string for IN clause
                            $ids_string = implode(',', array_map('intval', $connected_user_ids));

                            // Fetch details of connected users
                            $sql_users = "SELECT id, name, family, education, university, profile_pic, cover_photo FROM users WHERE id IN ($ids_string)";
                            $result_users = $conn->query($sql_users);

                            if ($result_users && $result_users->num_rows > 0) {
                                while ($row_user = $result_users->fetch_assoc()) {
                                    $target_user_id = $row_user["id"];
                                    $fullName = htmlspecialchars($row_user["name"] . " " . $row_user["family"]);
                                    $education = htmlspecialchars($row_user["education"] ?? 'نامشخص');
                                    $university = htmlspecialchars($row_user["university"] ?? 'نامشخص');
                                    $profilePic = htmlspecialchars($row_user["profile_pic"] ?? 'https://via.placeholder.com/100');
                                    $coverPic = htmlspecialchars($row_user["cover_photo"]);
                                    $profileLink = "../people/profile.php?id=" . (int)$target_user_id;
                    ?>
                                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                        <div class="user-card">
                                            <div class="cover-photo" style="background-image: url('../<?= $coverPic ?>');"></div>

                                            <div class="profile-pic-container">
                                                <img src="../<?= $profilePic ?>" class="profile-picture" alt="Profile Picture">
                                            </div>
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
                                                <a href="<?= $profileLink ?>" class="btn btn-primary btn-sm btn-custom"><i class="fas fa-user-graduate"></i> View Profile</a>
                                            </div>
                                        </div>
                                    </div>
                    <?php
                                }
                            } else {
                                echo "<div class='col-12 text-center'><p>No user details found for your connections.</p></div>";
                            }
                        } else {
                            echo "<div class='col-12 text-center'><p>You don't have any accepted connections yet.</p></div>";
                        }
                    } catch (Exception $e) {
                        error_log("Query error in saved-peoples.php: " . $e->getMessage());
                        echo "<div class='col-12 alert alert-danger'>Error loading connections. Please try again later.</div>";
                    } finally {
                        if ($conn instanceof mysqli) {
                            $conn->close();
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // دریافت مسیر فعلی صفحه
            var currentPath = window.location.pathname;
            var fileName = currentPath.split('/').pop(); // فقط نام فایل رو استخراج می کنیم

            // حذف کلاس active از تمام لینک ها
            $('.list-group-item-action').removeClass('active');

            // اضافه کردن کلاس active به لینکی که آدرسش با نام فایل فعلی مطابقت دارد
            $('.list-group-item-action').each(function() {
                var linkHref = $(this).attr('href');
                var linkFileName = linkHref.split('/').pop();

                if (linkFileName === fileName) {
                    $(this).addClass('active');
                }
            });
        });
    </script>


    <?php include "footer.php"; ?>

</body>

</html>