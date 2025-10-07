<?php

$servername = "localhost";
$username = "root"; 
$password = ''; 
$dbname = "video_archival_solution";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// print_r($conn);
?>