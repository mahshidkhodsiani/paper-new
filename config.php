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




define('GOOGLE_CLIENT_ID', '420518057369-1v9f8r5vgad3nc9s8r686qckba3bb95i.apps.googleusercontent.com'); // مطمئن شو دقیقاً همین باشه
define('GOOGLE_CLIENT_SECRET', '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'); // این رو هم چک کن
define('GOOGLE_REDIRECT_URI', 'https://localhost/paper-new/google_login_callback.php'); // این باید دقیقاً با Authorized redirect URIs در کنسول گوگل یکی باشه
