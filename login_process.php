<?php

session_start();
include "config.php";

// If user is already logged in, redirect to profile
if (isset($_SESSION['user_data'])) {
    header("Location: profile");
    exit();
}

if (isset($_POST['enter'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prevent SQL injection (basic)
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_data'] = $row;
        header("Location: profile");
        exit();
    } else {
        $_SESSION['login_error'] = "Incorrect email or password.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}