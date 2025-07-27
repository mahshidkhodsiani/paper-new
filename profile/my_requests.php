<?php
session_start(); // Start the session at the very beginning

// Include database configuration
// فرض بر این است که config.php یک سطح بالاتر از my_requests.php قرار دارد.
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
    <title>My Connection Requests</title>
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
    </style>
</head>

<body>

    <?php
    // Include header.php
    // فرض بر این است که header.php در همان سطح my_requests.php قرار دارد.
    include 'header.php';
    ?>

    <div class="container mt-3" id="message-container" style="display: none;">
        <div class="alert" role="alert" id="message-alert"></div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <?php
            // Include sidebar.php
            // فرض بر این است که sidebar.php در همان سطح my_requests.php قرار دارد.
            include 'sidebar.php';
            ?>

            <div class="col-md-9">
                <h3 class="mb-4">Incoming Connection Requests</h3>
                <div class="row">
                    <?php
                    try {
                        // Query to fetch pending connection requests where current user is the receiver
                        $sql = "SELECT c.id AS connection_id, u.id AS sender_user_id, u.name, u.family, u.education, u.university, u.profile_pic 
                                FROM connections c
                                JOIN users u ON c.sender_id = u.id
                                WHERE c.receiver_id = ? AND c.status = 'pending'";

                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $current_user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $connection_id = $row["connection_id"];
                                $sender_user_id = $row["sender_user_id"];
                                $fullName = htmlspecialchars($row["name"] . " " . $row["family"]);
                                $education = htmlspecialchars($row["education"] ?? 'نامشخص');
                                $university = htmlspecialchars($row["university"] ?? 'نامشخص');
                                $profilePic = htmlspecialchars($row["profile_pic"] ?? 'https://via.placeholder.com/100');
                                $coverPic = 'https://via.placeholder.com/800x450'; // Default cover image
                                $profileLink = "../people/profile.php?id=" . (int)$sender_user_id;
                    ?>
                                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
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
                                            <a href="<?= $profileLink ?>" class="btn btn-outline-info btn-sm btn-custom mb-2"><i class="fas fa-eye"></i> View Profile</a>
                                            <button type="button" class="btn btn-success btn-sm btn-custom action-btn" data-connection-id="<?= $connection_id ?>" data-action="accept">
                                                <i class="fas fa-check"></i> Accept
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btn-custom action-btn" data-connection-id="<?= $connection_id ?>" data-action="decline">
                                                <i class="fas fa-times"></i> Decline
                                            </button>
                                        </div>
                                    </div>
                        <?php
                            }
                        } else {
                            echo "<div class='col-12 text-center'><p>You have no pending connection requests.</p></div>";
                        }
                        $stmt->close();
                    } catch (Exception $e) {
                        error_log("Query error in my_requests.php: " . $e->getMessage());
                        echo "<div class='col-12 alert alert-danger'>Error loading requests. Please try again later.</div>";
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
                // Function to display messages (replaces alert)
                function showMessage(message, type = 'info') {
                    const msgContainer = document.getElementById('message-container');
                    const msgAlert = document.getElementById('message-alert');
                    msgAlert.textContent = message;
                    msgAlert.className = 'alert alert-' + type; // e.g., alert-info, alert-success, alert-danger
                    msgContainer.style.display = 'block';
                    // Optionally hide after a few seconds
                    setTimeout(() => {
                        msgContainer.style.display = 'none';
                    }, 5000);
                }

                // Sidebar active link logic
                var currentPath = window.location.pathname;
                var fileName = currentPath.split('/').pop();
                $('.list-group-item-action').removeClass('active');
                $('.list-group-item-action').each(function() {
                    var linkHref = $(this).attr('href');
                    var linkFileName = linkHref.split('/').pop();
                    if (linkFileName === fileName) {
                        $(this).addClass('active');
                    }
                });

                // Handle Accept/Decline button clicks
                $('.action-btn').on('click', function() {
                    const connectionId = $(this).data('connection-id');
                    const action = $(this).data('action'); // 'accept' or 'decline'
                    const clickedButton = $(this);
                    const cardElement = clickedButton.closest('.user-card'); // Get the parent card to remove it

                    // Disable buttons to prevent multiple clicks
                    cardElement.find('.action-btn').prop('disabled', true);
                    clickedButton.html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                    fetch('handle_request_action.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'connection_id=' + connectionId + '&action=' + action
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showMessage(data.message, 'success');
                                // Remove the card from display after successful action
                                cardElement.fadeOut(500, function() {
                                    $(this).remove();
                                    // If no more requests, show "No pending requests" message
                                    if ($('.user-card').length === 0) {
                                        $('.row').append('<div class="col-12 text-center"><p>You have no pending connection requests.</p></div>');
                                    }
                                });
                            } else {
                                showMessage('Error: ' + data.message, 'danger');
                                // Re-enable buttons on error
                                cardElement.find('.action-btn').prop('disabled', false);
                                clickedButton.html('<i class="fas fa-times"></i> Try Again'); // Or original text
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            showMessage('Network error or server issue. Please try again.', 'danger');
                            // Re-enable buttons on network error
                            cardElement.find('.action-btn').prop('disabled', false);
                            clickedButton.html('<i class="fas fa-times"></i> Try Again'); // Or original text
                        });
                });
            });
        </script>
</body>

</html>