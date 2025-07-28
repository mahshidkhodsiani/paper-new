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