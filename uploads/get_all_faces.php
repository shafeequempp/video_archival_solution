<?php
include '../connect.php';

$allTags = [];

$res = $conn->query("SELECT faces FROM video_metadata WHERE deleted_at IS NULL");

while ($row = $res->fetch_assoc()) {
    $faces = json_decode($row['faces'], true);
    if (is_array($faces)) {
        foreach ($faces as $face) {
            $val = strtolower(trim($face['value'] ?? ''));
            if ($val) $allfaces[$val] = true;
        }
    }
}

echo json_encode(array_keys($allfaces));
