<?php
session_start();

// Display PHP errors for debugging in development environment.
// In a production environment, disable or remove these lines.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the Composer autoloader is included.
// The path 'vendor/autoload.php' must be correct relative to this file.
require_once __DIR__ . '/vendor/autoload.php';

// Include your database and Google Client ID/Secret configuration file.
// The path 'config.php' must be correct relative to this file.
include_once __DIR__ . '/config.php';

// Check if the database connection ($conn) is properly established.
if (!isset($conn) || $conn->connect_error) {
    $_SESSION['login_error'] = 'Database connection error.';
    header('Location: login.php'); // Or an appropriate error page
    exit();
}

// Create a new Google Client instance
$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope('email');
$client->addScope('profile'); // To retrieve name, family name, and profile picture

// Check if the authentication code has been received from Google
if (isset($_GET['code'])) {
    try {
        // Exchange the authentication code for an access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        // Check for potential errors in token retrieval
        if (isset($token['error'])) {
            $_SESSION['login_error'] = 'Error fetching Google token: ' . $token['error_description'];
            header('Location: login.php');
            exit();
        }

        $client->setAccessToken($token);

        // If the token has expired (unlikely for a newly exchanged token, but good practice), refresh it.
        if ($client->isAccessTokenExpired()) {
            // Ensure a refresh token exists
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // If no refresh token, the user must re-authenticate
                $_SESSION['login_error'] = 'Access token expired and cannot be refreshed. Please log in again.';
                header('Location: login.php');
                exit();
            }
        }

        // Retrieve user information from Google
        $oauth2 = new Google_Service_Oauth2($client);
        $userInfo = $oauth2->userinfo->get();

        // Extract required information
        $google_id = $userInfo->id;
        $email = $userInfo->email;
        $first_name = isset($userInfo->givenName) ? $userInfo->givenName : '';
        $last_name = isset($userInfo->familyName) ? $userInfo->familyName : '';
        $profile_pic = isset($userInfo->picture) ? $userInfo->picture : 'images/default_profile.png';

        // ----------------------------------------------------------------------
        // Logic for checking and storing the user in the database
        // Prepared Statements are used to prevent SQL Injection.
        // ----------------------------------------------------------------------

        // Check if the user already exists in your database (based on email or google_id)
        // Using OR to cover both traditional users who registered with email and users who came via Google.
        $stmt = $conn->prepare("SELECT id, name, family, email, profile_pic, google_id, is_google_user FROM users WHERE email = ? OR google_id = ?");
        if ($stmt === false) {
            $_SESSION['login_error'] = "Error preparing SELECT query: " . $conn->error;
            header('Location: login.php');
            exit();
        }
        $stmt->bind_param("ss", $email, $google_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, log them in
            $user = $result->fetch_assoc();

            // If it was a traditional user (empty google_id) and now logged in with Google, update google_id
            if (empty($user['google_id']) || $user['is_google_user'] == 0) {
                $update_stmt = $conn->prepare("UPDATE users SET google_id = ?, is_google_user = 1, profile_pic = ?, name = ?, family = ? WHERE id = ?");
                if ($update_stmt === false) {
                    $_SESSION['login_error'] = "Error preparing UPDATE query: " . $conn->error;
                    header('Location: login.php');
                    exit();
                }
                $update_stmt->bind_param("ssssi", $google_id, $profile_pic, $first_name, $last_name, $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
            }

            // Set user session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = !empty($user['name']) ? $user['name'] : $first_name; // If no previous name in DB, take from Google
            $_SESSION['user_family'] = !empty($user['family']) ? $user['family'] : $last_name;
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['profile_pic'] = !empty($user['profile_pic']) ? $user['profile_pic'] : $profile_pic;
            $_SESSION['logged_in'] = true;

            $_SESSION['login_success'] = 'Welcome!';
            header('Location: profile/index.php'); // Or to the main profile page
            exit();
        } else {
            // User does not exist, register them
            // For Google users, generate a random and secure password
            // (Not strictly necessary for Google authentication, but for database field compatibility)
            $generated_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $is_google_user = 1; // Flag to indicate a Google user

            $stmt_insert = $conn->prepare("INSERT INTO users (google_id, name, family, email, password, profile_pic, is_google_user) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt_insert === false) {
                $_SESSION['registration_error'] = "Error preparing INSERT query: " . $conn->error;
                header('Location: register.php');
                exit();
            }
            $stmt_insert->bind_param("ssssssi", $google_id, $first_name, $last_name, $email, $generated_password, $profile_pic, $is_google_user);

            if ($stmt_insert->execute()) {
                // Successful registration, log the user in
                $_SESSION['user_id'] = $conn->insert_id; // ID of the last inserted user
                $_SESSION['user_name'] = $first_name;
                $_SESSION['user_family'] = $last_name;
                $_SESSION['user_email'] = $email;
                $_SESSION['profile_pic'] = $profile_pic;
                $_SESSION['logged_in'] = true;

                $_SESSION['registration_success'] = 'Registered with Google successfully! Welcome!';
                header('Location: profile/index.php'); // Or to the main profile page
                exit();
            } else {
                $_SESSION['registration_error'] = "Error registering with Google: " . $stmt_insert->error;
                header('Location: register.php');
                exit();
            }
            $stmt_insert->close();
        }
        $stmt->close();
    } catch (Exception $e) {
        // Handle errors during token verification, data retrieval, or other exceptions
        $_SESSION['login_error'] = 'Google authentication error: ' . $e->getMessage();
        header('Location: login.php');
        exit();
    }
} else {
    // If no authentication code was received from Google (e.g., user navigated directly or authentication was canceled)
    // This message is passed to login.php.
    $_SESSION['login_error'] = 'Google login operation cancelled or authentication code not received.';
    header('Location: login.php');
    exit();
}
