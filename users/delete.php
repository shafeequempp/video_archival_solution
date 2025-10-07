<?php
include '../connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Soft delete: set is_deleted to 1
    $conn->query("UPDATE admin SET is_deleted = 1 WHERE id = $id");
}

header("Location: index");
exit;