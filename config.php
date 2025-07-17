<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "paperet";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}




// $servername = 'localhost'; 
// $username = 'u477456209_mahshid'; 
// $password = '123456789M@hshid'; 
// $dbname = 'u477456209_paper'; 

// // $cfg['Lang'] = 'fa';
// $cfg['Charset'] = 'utf8mb4';


// $conn = mysqli_connect($servername, $username, $password, $dbname);


// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// $conn->set_charset("utf8");
