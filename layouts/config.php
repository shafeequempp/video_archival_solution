<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define("BASE_URL", "https://videos.mpp.in/");

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /video_archival_solution/login");
    exit();
}
?>  