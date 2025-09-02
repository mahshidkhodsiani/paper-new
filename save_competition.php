<?php
session_start();
include "config.php";

// بررسی اینکه کاربر لاگین کرده است
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// دریافت اطلاعات کاربر
$user_data = $_SESSION['user_data'];
$user_id = $user_data['id'];

// اتصال به دیتابیس
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی خطای اتصال
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// بررسی نوع درخواست
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        // ایجاد مسابقه جدید
        $org_name = $conn->real_escape_string($_POST['org_name'] ?? '');
        $org_email = $conn->real_escape_string($_POST['org_email'] ?? '');
        $comp_title = $conn->real_escape_string($_POST['comp_title'] ?? '');
        $comp_description = $conn->real_escape_string($_POST['comp_description'] ?? '');
        $start_date = $conn->real_escape_string($_POST['start_date'] ?? '');
        $end_date = $conn->real_escape_string($_POST['end_date'] ?? '');
        $timezone = $conn->real_escape_string($_POST['timezone'] ?? 'UTC');
        $category = $conn->real_escape_string($_POST['category'] ?? 'other');
        $access_mode = $conn->real_escape_string($_POST['access_mode'] ?? 'open');
        $visibility = $conn->real_escape_string($_POST['visibility'] ?? 'public');
        $voting = $conn->real_escape_string($_POST['voting'] ?? 'judges');
        $max_votes = intval($_POST['max_votes'] ?? 3);
        $result_visibility = $conn->real_escape_string($_POST['result_visibility'] ?? 'after_closing');
        $submission_type = $conn->real_escape_string($_POST['submission_type'] ?? 'file');
        $max_file_size = intval($_POST['max_file_size'] ?? 10);
        $submission_guidelines = $conn->real_escape_string($_POST['submission_guidelines'] ?? '');
        $custom_fields = $conn->real_escape_string($_POST['custom_fields'] ?? '[]');
        $scoring_rubric = $conn->real_escape_string($_POST['scoring_rubric'] ?? '[]');
        $weighting_system = $conn->real_escape_string($_POST['weighting_system'] ?? 'none');
        $custom_css = $conn->real_escape_string($_POST['custom_css'] ?? '');
        $redirect_url = $conn->real_escape_string($_POST['redirect_url'] ?? '');
        $status = 'upcoming'; // وضعیت پیش فرض

        // پردازش آپلود لوگو
        $logo_path = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/logos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $file_path)) {
                $logo_path = $file_path;
            }
        }

        // درج اطلاعات در دیتابیس
        $sql = "INSERT INTO competitions (
            user_id, org_name, org_email, comp_title, comp_description, 
            start_date, end_date, timezone, category, access_mode, 
            visibility, voting, max_votes, result_visibility, submission_type, 
            max_file_size, submission_guidelines, custom_fields, scoring_rubric, 
            weighting_system, custom_css, redirect_url, status, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, ?, NOW(), NOW()
        )";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssssssssisissssssss",
            $user_id,
            $org_name,
            $org_email,
            $comp_title,
            $comp_description,
            $start_date,
            $end_date,
            $timezone,
            $category,
            $access_mode,
            $visibility,
            $voting,
            $max_votes,
            $result_visibility,
            $submission_type,
            $max_file_size,
            $submission_guidelines,
            $custom_fields,
            $scoring_rubric,
            $weighting_system,
            $custom_css,
            $redirect_url,
            $status
        );

        if ($stmt->execute()) {
            $competition_id = $stmt->insert_id;

            // پردازش اطلاعات اضافی (جوایز، داوران، شرکت کنندگان و غیره)
            // این بخش نیاز به پیاده سازی کامل دارد

            echo json_encode(['success' => true, 'message' => 'Competition created successfully!', 'id' => $competition_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating competition: ' . $stmt->error]);
        }

        $stmt->close();
    } elseif ($action === 'update') {
        // به روزرسانی مسابقه موجود
        $id = intval($_POST['id'] ?? 0);
        $comp_title = $conn->real_escape_string($_POST['comp_title'] ?? '');
        $comp_description = $conn->real_escape_string($_POST['comp_description'] ?? '');
        $org_name = $conn->real_escape_string($_POST['org_name'] ?? '');
        $org_email = $conn->real_escape_string($_POST['org_email'] ?? '');

        // بررسی مالکیت مسابقه
        $check_sql = "SELECT user_id FROM competitions WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Competition not found']);
            exit();
        }

        $competition = $check_result->fetch_assoc();
        if ($competition['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to edit this competition']);
            exit();
        }

        $check_stmt->close();

        // به روزرسانی اطلاعات
        $sql = "UPDATE competitions SET 
                comp_title = ?, comp_description = ?, org_name = ?, org_email = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $comp_title, $comp_description, $org_name, $org_email, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Competition updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating competition: ' . $stmt->error]);
        }

        $stmt->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
