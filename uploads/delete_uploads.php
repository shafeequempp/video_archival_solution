<?php
include '../connect.php';
session_start();

$id = intval($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'] ?? null;

if ($id > 0) {
    // Soft delete the video metadata entry
    $stmt = $conn->prepare("UPDATE video_metadata SET deleted_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Log the deletion action (assuming you have a `video_logs` table)
    $log = $conn->prepare("INSERT INTO video_logs (video_id, action, action_by) VALUES (?, 'deleted', ?)");
    $log->bind_param("ii", $id, $userId);
    $log->execute();
    $log->close();

    echo "Deleted";
} else {
    echo "Invalid ID";
}
?>
