<?php

// --- Adding the following code to set the session lifetime to 2 hours (7200 seconds) ---
$lifetime = 7200; // 2 hours in seconds
session_set_cookie_params($lifetime);
// ------------------------------------------------------------------

session_start();
include "config.php";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for database connection error
if ($conn->connect_error) {
    die("Database connection error: " . $conn->connect_error);
}

// Check if form data was submitted via POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Use Prepared Statement to prevent SQL Injection
        $sql = "SELECT id, name, family, email, password, status, profile_pic FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stored_hashed_password = $row['password'];

            // Use password_verify() to confirm the password
            if (password_verify($password, $stored_hashed_password)) {
                // Password is correct.
                if ($row['status'] == 0) {
                    $sql_reactivate = "UPDATE users SET status = 1 WHERE id = ?";
                    $stmt_reactivate = $conn->prepare($sql_reactivate);
                    $stmt_reactivate->bind_param("i", $row['id']);
                    $stmt_reactivate->execute();
                    $stmt_reactivate->close();
                    $_SESSION['login_message'] = "Your account has been reactivated successfully!";
                    $_SESSION['login_message_type'] = "success";
                }

                unset($row['password']);
                $_SESSION['user_data'] = $row;
                $_SESSION['logged_in'] = true;

                if (isset($_SESSION['redirect_to'])) {
                    $redirect_url = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                    header("Location: " . $redirect_url);
                    exit();
                }
                // Redirect user to the profile page
                header("Location: profile");
                exit();
            } else {
                // Incorrect password.
                $_SESSION['login_error'] = "Incorrect email or password.";
            }
        } else {
            // Email not found in the database.
            $_SESSION['login_error'] = "Incorrect email or password.";
        }
    } else {
        // Data is incomplete.
        $_SESSION['login_error'] = "Please fill in all fields.";
    }
} else {
    // If the request was not sent with the POST method
    $_SESSION['login_error'] = "Invalid request.";
}

// Redirect back to the login page in case of any error
header("Location: login.php");
exit();