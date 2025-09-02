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

// بررسی وجود پارامتر id
if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Competition ID is required']);
    exit();
}

$competition_id = intval($_GET['id']);

// اتصال به دیتابیس
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی خطای اتصال
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// دریافت اطلاعات مسابقه
$sql = "SELECT * FROM competitions WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $competition_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Competition not found or access denied']);
    exit();
}

$competition = $result->fetch_assoc();

// بازگرداندن اطلاعات به صورت JSON
header('Content-Type: application/json');
echo json_encode($competition);

$stmt->close();
$conn->close();
