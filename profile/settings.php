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

// --- Start of POST form processing section ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and filter input data
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
    $biography = filter_input(INPUT_POST, 'biography', FILTER_SANITIZE_STRING);

    $password_new = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $password_confirm = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

    $updateFields = [];
    $bindParams = '';
    $bindValues = [];

    // Add text fields to the list for UPDATE
    if (isset($_POST['first_name'])) {
        $updateFields[] = "name = ?";
        $bindParams .= "s";
        $bindValues[] = $name;
    }
    if (isset($_POST['last_name'])) {
        $updateFields[] = "family = ?";
        $bindParams .= "s";
        $bindValues[] = $family;
    }
    if (isset($_POST['university'])) {
        $updateFields[] = "university = ?";
        $bindParams .= "s";
        $bindValues[] = $university;
    }
    if (isset($_POST['birthdate'])) {
        $updateFields[] = "birthdate = ?";
        $bindParams .= "s";
        $bindValues[] = $birthdate;
    }
    if (isset($_POST['education'])) {
        $updateFields[] = "education = ?";
        $bindParams .= "s";
        $bindValues[] = $education;
    }
    if (isset($_POST['workplace'])) {
        $updateFields[] = "workplace = ?";
        $bindParams .= "s";
        $bindValues[] = $workplace;
    }
    if (isset($_POST['meeting_info'])) {
        $updateFields[] = "meeting_info = ?";
        $bindParams .= "s";
        $bindValues[] = $meeting_info;
    }
    if (isset($_POST['linkedin_url'])) {
        $updateFields[] = "linkedin_url = ?";
        $bindParams .= "s";
        $bindValues[] = $linkedin_url;
    }
    if (isset($_POST['x_url'])) {
        $updateFields[] = "x_url = ?";
        $bindParams .= "s";
        $bindValues[] = $x_url;
    }
    if (isset($_POST['google_scholar_url'])) {
        $updateFields[] = "google_scholar_url = ?";
        $bindParams .= "s";
        $bindValues[] = $google_scholar_url;
    }
    if (isset($_POST['github_url'])) {
        $updateFields[] = "github_url = ?";
        $bindParams .= "s";
        $bindValues[] = $github_url;
    }
    if (isset($_POST['website_url'])) {
        $updateFields[] = "website_url = ?";
        $bindParams .= "s";
        $bindValues[] = $website_url;
    }
    if (isset($_POST['biography'])) {
        $updateFields[] = "biography = ?";
        $bindParams .= "s";
        $bindValues[] = $biography;
    }

    // --- Password management ---
    if (!empty($password_new) && !empty($password_confirm)) {
        if ($password_new === $password_confirm) {
            $hashed_password = password_hash($password_new, PASSWORD_DEFAULT);
            $updateFields[] = "password = ?";
            $bindParams .= "s";
            $bindValues[] = $hashed_password;
        } else {
            $message = 'Passwords do not match.';
            $messageType = 'danger';
        }
    } elseif ((!empty($password_new) && empty($password_confirm)) || (empty($password_new) && !empty($password_confirm))) {
        $message = 'Both password fields must be filled to change your password.';
        $messageType = 'danger';
    }


    // --- Profile picture upload management ---
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $baseUploadDir = '../uploads/pics/';
        $userUploadDir = $baseUploadDir . $userId . '/';

        if (!is_dir($userUploadDir)) {
            mkdir($userUploadDir, 0775, true);
        }

        $fileName = basename($_FILES['profile_image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowedTypes)) {
            $newFileName = 'profile_pic_' . uniqid() . '.' . $fileExt;
            $uploadFilePath = $userUploadDir . $newFileName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFilePath)) {
                $dbFilePath = 'uploads/pics/' . $userId . '/' . $newFileName;
                $updateFields[] = "profile_pic = ?";
                $bindParams .= "s";
                $bindValues[] = $dbFilePath;
            } else {
                $message = 'Error uploading profile picture.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Invalid profile picture file type. Only JPG, JPEG, PNG, GIF are allowed.';
            $messageType = 'danger';
        }
    }

    // --- Cover photo upload management (new) ---
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] == UPLOAD_ERR_OK) {
        $baseUploadDir = '../uploads/covers/';
        $userUploadDir = $baseUploadDir . $userId . '/';

        if (!is_dir($userUploadDir)) {
            mkdir($userUploadDir, 0775, true);
        }

        $fileName = basename($_FILES['cover_photo']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowedTypes)) {
            $newFileName = 'cover_photo_' . uniqid() . '.' . $fileExt;
            $uploadFilePath = $userUploadDir . $newFileName;

            if (move_uploaded_file($_FILES['cover_photo']['tmp_name'], $uploadFilePath)) {
                $dbFilePath = 'uploads/covers/' . $userId . '/' . $newFileName;
                $updateFields[] = "cover_photo = ?";
                $bindParams .= "s";
                $bindValues[] = $dbFilePath;
            } else {
                $message = 'Error uploading cover photo.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Invalid cover photo file type. Only JPG, JPEG, PNG, GIF are allowed.';
            $messageType = 'danger';
        }
    }

    if (!empty($updateFields) && $messageType !== 'danger') {
        $conn_update = new mysqli($servername, $username, $password, $dbname);
        if ($conn_update->connect_error) {
            die("Connection failed: " . $conn_update->connect_error);
        }
        $conn_update->set_charset("utf8mb4");

        $sql_update = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $bindParams .= "i";
        $bindValues[] = $userId;

        $stmt_update = $conn_update->prepare($sql_update);
        if ($stmt_update) {
            $bind_names = array_merge([$bindParams], $bindValues);
            $refs = [];
            foreach ($bind_names as $key => $value) {
                $refs[$key] = &$bind_names[$key];
            }
            call_user_func_array([$stmt_update, 'bind_param'], $refs);

            if ($stmt_update->execute()) {
                $message = 'Information updated successfully!';
                $messageType = 'success';

                // --- Crucial section: full update of $_SESSION['user_data'] from the database ---
                $sql_fetch_updated_user = "SELECT id, name, family, email, profile_pic, cover_photo, university, birthdate, education, workplace, meeting_info, linkedin_url, x_url, google_scholar_url, github_url, website_url, biography, created_at, updated_at FROM users WHERE id = ?";
                $stmt_fetch = $conn_update->prepare($sql_fetch_updated_user);
                if ($stmt_fetch) {
                    $stmt_fetch->bind_param("i", $userId);
                    $stmt_fetch->execute();
                    $result_fetch = $stmt_fetch->get_result();
                    if ($result_fetch->num_rows > 0) {
                        $_SESSION['user_data'] = $result_fetch->fetch_assoc();
                    }
                    $stmt_fetch->close();
                }
                // --- End of crucial section ---

                header("Location: settings.php?status=" . urlencode($messageType) . "&msg=" . urlencode($message));
                exit();
            } else {
                $message = 'Error in updating information: ' . $stmt_update->error;
                $messageType = 'danger';
            }
            $stmt_update->close();
        } else {
            $message = 'Error in preparing update query: ' . $conn_update->error;
            $messageType = 'danger';
        }
        $conn_update->close();
    } elseif (empty($updateFields) && $messageType !== 'danger') {
        $message = 'No information was available for update.';
        $messageType = 'info';
    }
}

// --- Message handling after redirect (GET) ---
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $messageType = $_GET['status'];
    $message = urldecode($_GET['msg']);
}


// --- Load user data for display in the form (if no POST request or after redirect) ---
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$user = [];
$sql = "SELECT id, name, family, email, profile_pic, cover_photo, university, birthdate, education, workplace, meeting_info, linkedin_url, x_url, google_scholar_url, github_url, website_url, biography FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    header("Location: ../login.php");
    exit();
}

$stmt->close();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>

    <?php include "../includes.php"; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css">

    <style>
        .cover-photo-container {
            width: 100%;
            height: 250px;
            background-color: #eee;
            background-size: cover;
            background-position: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 20px;
            position: relative;
        }

        .cover-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }
    </style>

</head>

<body>

    <?php include "header.php"; ?>

    <div class="container">
        <div class="row">

            <?php include "sidebar.php"; ?>

            <div class="col-md-6">
                <div class="main-content shadow-lg p-3 mb-5 bg-white rounded">

                    <div class="cover-photo-container">
                        <?php if (!empty($user['cover_photo'])): ?>
                            <img src="../<?= htmlspecialchars($user['cover_photo']); ?>" alt="Cover Photo">
                        <?php else: ?>
                            <img src="../images/11.jpg" alt="Default Cover Photo">
                        <?php endif; ?>
                    </div>

                    <h4 class="mb-4">Account Settings</h4>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post" enctype="multipart/form-data">

                        <div class="mb-4">
                            <label for="coverPhoto" class="form-label">Cover Photo</label>
                            <input type="file" class="form-control" id="coverPhoto" name="cover_photo" accept="image/png, image/jpeg, image/gif">
                            <small class="form-text text-muted">Choose a cover image for your profile (max 2MB)</small>
                        </div>

                        <div class="mb-4">
                            <label for="profileImage" class="form-label">Profile Image</label>
                            <div class="d-flex align-items-center">
                                <img src="../<?php echo htmlspecialchars($user['profile_pic'] ?? '../images/2.png'); ?>" alt="Profile Picture" class="img-thumbnail rounded-circle me-3" style="width: 100px; height: 100px; object-fit: cover;">
                                <div>
                                    <input type="file" class="form-control" id="profileImage" name="profile_image" accept="image/png, image/jpeg, image/gif">
                                    <small class="form-text text-muted">PNG, JPG or GIF file (max 2MB)</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? $user['name'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? $user['family'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye-slash" id="toggleIcon"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Leave blank if you don't want to change it.</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="bi bi-eye-slash" id="toggleConfirmIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="university" class="form-label">University</label>
                            <input type="text" class="form-control" id="university" name="university" value="<?php echo htmlspecialchars($_POST['university'] ?? $user['university'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="birthdate" class="form-label">Birthdate</label>
                            <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($_POST['birthdate'] ?? $user['birthdate'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="education" class="form-label">Education</label>
                            <input type="text" class="form-control" id="education" name="education" value="<?php echo htmlspecialchars($_POST['education'] ?? $user['education'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="workplace" class="form-label">Workplace</label>
                            <input type="text" class="form-control" id="workplace" name="workplace" value="<?php echo htmlspecialchars($_POST['workplace'] ?? $user['workplace'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="meetingInfo" class="form-label">Meeting Info</label>
                            <textarea class="form-control" id="meetingInfo" name="meeting_info" rows="4"><?php echo htmlspecialchars($_POST['meeting_info'] ?? $user['meeting_info'] ?? 'Mon - Fri: 9:00 AM - 5:00 PM (CST)'); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="aboutMe" class="form-label">About Me (Bio)</label>
                            <textarea class="form-control" id="aboutMe" name="biography" rows="6" placeholder="Tell us a bit about yourself..."><?php echo htmlspecialchars($_POST['biography'] ?? $user['biography'] ?? ''); ?></textarea>
                            <small class="form-text text-muted">Share a brief biography or description about yourself.</small>
                        </div>
                        <div class="mb-3">
                            <label for="linkedin" class="form-label">LinkedIn Profile Link</label>
                            <input type="url" class="form-control" id="linkedin" name="linkedin_url" value="<?php echo htmlspecialchars($_POST['linkedin_url'] ?? $user['linkedin_url'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="xProfile" class="form-label">X Profile Link</label>
                            <input type="url" class="form-control" id="xProfile" name="x_url" value="<?php echo htmlspecialchars($_POST['x_url'] ?? $user['x_url'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="googleScholar" class="form-label">Google Scholar Profile Link</label>
                            <input type="url" class="form-control" id="googleScholar" name="google_scholar_url" value="<?php echo htmlspecialchars($_POST['google_scholar_url'] ?? $user['google_scholar_url'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="github" class="form-label">Github Profile Link</label>
                            <input type="url" class="form-control" id="github" name="github_url" value="<?php echo htmlspecialchars($_POST['github_url'] ?? $user['github_url'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="websiteLink" class="form-label">Website Link</label>
                            <input type="url" class="form-control" id="websiteLink" name="website_url" value="<?php echo htmlspecialchars($_POST['website_url'] ?? $user['website_url'] ?? ''); ?>">
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

            // منطق نمایش/پنهان کردن پسورد
            const passwordInput = document.getElementById('password');
            const togglePasswordButton = document.getElementById('togglePassword');
            const togglePasswordIcon = document.getElementById('toggleIcon');

            if (togglePasswordButton) {
                togglePasswordButton.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    togglePasswordIcon.classList.toggle('bi-eye');
                    togglePasswordIcon.classList.toggle('bi-eye-slash');
                });
            }

            const confirmPasswordInput = document.getElementById('confirmPassword');
            const toggleConfirmPasswordButton = document.getElementById('toggleConfirmPassword');
            const toggleConfirmPasswordIcon = document.getElementById('toggleConfirmIcon');

            if (toggleConfirmPasswordButton) {
                toggleConfirmPasswordButton.addEventListener('click', function() {
                    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPasswordInput.setAttribute('type', type);
                    toggleConfirmPasswordIcon.classList.toggle('bi-eye');
                    toggleConfirmPasswordIcon.classList.toggle('bi-eye-slash');
                });
            }


            // --- Unified Logic for Handling Alerts and Cleaning URL ---
            const allAlerts = document.querySelectorAll('.alert');
            if (allAlerts.length > 0) {
                setTimeout(() => {
                    allAlerts.forEach(alert => {
                        const bootstrapAlert = bootstrap.Alert.getOrCreateInstance(alert);
                        if (bootstrapAlert) {
                            bootstrapAlert.close();
                        } else {
                            // Fallback if Bootstrap's JS isn't fully loaded or instance not found
                            alert.remove();
                        }
                    });

                    // Clean the URL if it contained status/msg parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('status') || urlParams.has('msg')) {
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({}, document.title, cleanUrl);
                    }
                }, 3000); // 3000 milliseconds = 3 seconds
            }
        });
    </script>


    <?php include "footer.php"; ?>

</body>

</html>