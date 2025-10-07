<?php

$servername = "10.2.153.58";
$username = "user_videoarchival"; 
$password = 'Fgt$%7896JlljP(*7rchj#'; 
$dbname = "mpp_videoarchival";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// print_r($conn);
?>