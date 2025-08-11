<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "paperet";

// ساخت اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// $servername = "185.94.99.249";
// $username = "ithrbuau_admin";
// $password = ").F*v(WcMj57";
// $dbname = "ithrbuau_testy";



// $conn = mysqli_connect($servername, $username, $password, $dbname);


// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// $conn->set_charset("utf8");




define('GOOGLE_CLIENT_ID', '629465661814-hnhubojf35ee3clsj04tsatqpuuqiq69.apps.googleusercontent.com'); // مطمئن شو دقیقاً همین باشه
define('GOOGLE_CLIENT_SECRET', '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'); // این رو هم چک کن
define('GOOGLE_REDIRECT_URI', 'https://localhost/paper-new/google_login_callback.php'); // این باید دقیقاً با Authorized redirect URIs در کنسول گوگل یکی باشه

// define('GOOGLE_REDIRECT_URI', 'https://moonshid.ir/paper/google_login_callback.php');