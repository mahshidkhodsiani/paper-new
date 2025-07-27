<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$unread_count = 0;

$config_path = realpath(__DIR__ . '/../config.php');

if ($config_path && file_exists($config_path)) {
    include_once $config_path;
} else {
    die("خطا: فایل config.php یافت نشد");
}

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
    die("خطا در اتصال به پایگاه داده");
}

function safe($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if (isset($_SESSION['user_data']['id'])) {
    $user_id = $_SESSION['user_data']['id'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = FALSE");

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $unread_count = (int)$result->fetch_row()[0];
      
    }
}
?>

<header class="p-3 mb-3 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <a href="./" class="d-flex align-items-center mb-2 mb-lg-0 text-dark text-decoration-none">
                <img src="../images/logo.jpeg" class="img-fluid" height="80" width="80" alt="Logo">
            </a>

            <form class="col-12 col-lg-5 mb-3 mb-lg-0 me-lg-3 mr-2" style="margin-left: 5px;">
                <input type="search" class="form-control" placeholder="Search..." aria-label="Search">
            </form>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li><a href="#" class="nav-link px-2 link-secondary"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                <li><a href="./" class="nav-link px-2 link-dark"><i class="fas fa-users"></i> People</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link px-2 link-dark dropdown-toggle" href="#" id="navbarDropdownNotification" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i> Notification
                        <?php if ($unread_count > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= safe($unread_count) ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownNotification">
                        <?php if ($unread_count > 0): ?>
                            <li><a class="dropdown-item" href="messages.php"><i class="fas fa-envelope me-2"></i> You have <?= safe($unread_count) ?> unread message(s)</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-check-circle me-2"></i> No new notifications</a></li>
                        <?php endif; ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="messages.php"><i class="fas fa-inbox me-2"></i> View all messages</a></li>
                    </ul>
                </li>
            </ul>

            <div class="dropdown text-end">
                <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= isset($_SESSION['user_data']['profile_pic']) ? safe($_SESSION['user_data']['profile_pic']) : 'https://github.com/mdo.png' ?>" alt="profile" width="32" height="32" class="rounded-circle">
                </a>
                <ul class="dropdown-menu text-small" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="profile"><i class="fas fa-user-circle me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sign out</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>