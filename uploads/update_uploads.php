<?php
session_start();
include '../connect.php'; // your DB connection
// Make sure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$videoId = intval($_POST['id'] ?? 0);
if ($videoId <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid video ID"]);
    exit;
}

// Get current file path from DB (so we can delete if replaced)
$result = $conn->prepare("SELECT filename,resolution,codec,camera_type,duration,preview FROM video_metadata WHERE id = ?");
$result->bind_param("i", $videoId);
$result->execute();
$res = $result->get_result();
$currentData = $res->fetch_assoc();
$result->close();

if (!$currentData) {
    echo json_encode(["status" => "error", "message" => "Video not found"]);
    exit;
}

$videoPath = $currentData['filename'];
$preview_path = $currentData['preview'];
$resolution = $currentData['resolution'];
$codec = $currentData['codec'];
$duration = $currentData['duration'];
$cameraType = $currentData['camera_type'];

// Create folder structure: uploads/YYYY/MM/videos
$year = date("Y");
$month = date("m");
$uploadDir = "../upload/$year/$month/";
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(["status" => "error", "message" => "Failed to create upload directory"]);
        exit;
    }
}

// If a new video file is uploaded → replace the old one
if (isset($_FILES['video_upload']) && $_FILES['video_upload']['error'] === UPLOAD_ERR_OK) {
    $fileTmp  = $_FILES['video_upload']['tmp_name'];
    $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $_FILES['video_upload']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($fileTmp, $targetFile)) {
        // Delete old file if exists
        $oldFilePath = "../" . $currentData['filename'];
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
        $videoPath = "upload/$year/$month/" . $fileName;
        $oldpreviewPath = "../" . $currentData['preview'];
        if (file_exists($oldpreviewPath)) {
            unlink($oldpreviewPath);
        }
        $preview_path = "upload/previews/" . $fileName;
        $ffprobe = "ffprobe";
        $cmd = "$ffprobe -v quiet -print_format json -show_format -show_streams " . escapeshellarg($targetFile);
        $output = shell_exec($cmd);
        $metadata = json_decode($output, true);

        if (!empty($metadata['streams'])) {
            foreach ($metadata['streams'] as $stream) {
                if ($stream['codec_type'] === 'video') {
                    $codec = $stream['codec_name'] ?? '';
                    $width = $stream['width'] ?? '';
                    $height = $stream['height'] ?? '';
                    $resolution = ($width && $height) ? "{$width}x{$height}" : '';
                }
            }
        }

        if (!empty($metadata['format']['duration'])) {
            $seconds = (int)$metadata['format']['duration'];
            $duration = gmdate("H:i:s", $seconds);
        }

        if (!empty($metadata['format']['tags']['com.apple.quicktime.make'])) {
            $cameraType = $metadata['format']['tags']['com.apple.quicktime.make'] . ' ' . ($metadata['format']['tags']['com.apple.quicktime.model'] ?? '');
        } elseif (!empty($metadata['format']['tags']['model'])) {
            $cameraType = $metadata['format']['tags']['model'];
        }

        $ffmpeg = 'ffmpeg'; 
        $outDir = '../upload/previews';
        if (!is_dir($outDir)) mkdir($outDir, 0775, true);

        $preview  = $outDir . '/preview_360p.mp4';  // full low-res
        $teaser   = $outDir . '/' . $fileName;   // 10-second teaser

        // helper
        function run($cmd) {
            $out = [];
            $ret = 0;
            exec($cmd . ' 2>&1', $out, $ret);
            return ['code' => $ret, 'out' => implode("\n", $out)];
        }

        $in  = escapeshellarg($targetFile);
        $te  = escapeshellarg($teaser);
        $pr  = escapeshellarg($preview);
        $th  = escapeshellarg($thumb);

        $cmdTeaser = "$ffmpeg -y -ss 0 -t 180 -i $in -vf scale=-2:240 -c:v libx264 -preset veryfast -crf 30 -c:a aac -b:a 96k -movflags +faststart $te";
        $r1 = run($cmdTeaser);
    } else {
        echo json_encode(["status" => "error", "message" => "Video upload failed"]);
        exit;
    }
}

// Collect form data
$event        = $_POST['event'] ?? '';
$faces        = trim($_POST['faces'] ?? '');
$location     = $_POST['location'] ?? '';
$cameraman    = $_POST['cameraman'] ?? '';
$description  = $_POST['description'] ?? '';
$date         = $_POST['date'] ?? null;
$usage_rights = $_POST['usage_rights'] ?? '';
$embargo_date = $_POST['embargo_date'] ?? null;

// Update DB
$stmt = $conn->prepare("
    UPDATE video_metadata 
    SET event=?, faces=?, location=?, cameraman=?, description=?, date=?, usage_rights=?, embargo_date=?, filename=? 
    WHERE id=?
");
$stmt->bind_param(
    "sssssssssi", 
    $event, 
    $faces, 
    $location, 
    $cameraman, 
    $description, 
    $date, 
    $usage_rights, 
    $embargo_date, 
    $videoPath,
    $videoId
);

if ($stmt->execute()) {
    // Add log entry
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        $logStmt = $conn->prepare("INSERT INTO video_logs (video_id, action, action_by) VALUES (?, 'updated', ?)");
        $logStmt->bind_param("ii", $videoId, $userId);
        $logStmt->execute();
        $logStmt->close();
    }

    echo json_encode(["status" => "success", "message" => "Video updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database update failed"]);
}

$stmt->close();
$conn->close();

header("Location: view.php");
?>
