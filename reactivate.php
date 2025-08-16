<?php
session_start();
include "config.php";

// بررسی می‌کنیم که آیا ایمیل کاربر در سشن موقت ذخیره شده است یا خیر.
if (!isset($_SESSION['deactivated_email'])) {
    header("Location: login.php");
    exit();
}

$email_to_reactivate = $_SESSION['deactivated_email'];
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reactivate'])) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // کوئری برای فعال کردن حساب کاربری
    $sql = "UPDATE users SET status = 1 WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email_to_reactivate);
        if ($stmt->execute()) {
            // حساب با موفقیت فعال شد.

            // سشن موقت را پاک می‌کنیم.
            unset($_SESSION['deactivated_email']);

            // کاربر را به صفحه لاگین هدایت می‌کنیم تا مجدداً وارد شود.
            header("Location: login.php?status=success&msg=" . urlencode("Your account has been successfully reactivated. Please log in again."));
            exit();
        } else {
            $message = "Error reactivating account.";
            $messageType = "danger";
        }
        $stmt->close();
    } else {
        $message = "Error preparing the query.";
        $messageType = "danger";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivate Account</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .reactivate-container {
            max-width: 500px;
            margin-top: 100px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
    </style>
</head>

<body>
    <div class="container reactivate-container">
        <h2 class="text-center">Account Deactivated</h2>
        <p class="text-center">Your account is currently deactivated. Would you like to reactivate it?</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> text-center">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="reactivate.php" method="post">
            <div class="text-center mt-4">
                <button type="submit" name="reactivate" class="btn btn-primary">Reactivate My Account</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <a href="login.php">Cancel and go back to login</a>
        </div>
    </div>
</body>

</html>