<?php
session_start();
include '../connect.php'; // your DB connection

// Make sure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

// Create folder structure: uploads/YYYY/MM/videos
$year = date("Y");
$month = date("m");
$uploadDir ="../upload/$year/$month/";
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(["status" => "error", "message" => "Failed to create upload directory"]);
        exit;
    }
}

// File upload handling
function uploadErrorMessage($errorCode) {
    $errors = [
        UPLOAD_ERR_OK         => 'There is no error, the file uploaded successfully.',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
    ];
    return $errors[$errorCode] ?? 'Unknown upload error.';
}

// File upload handling
$videoPath = "";
if (isset($_FILES['video_upload'])) {
    if ($_FILES['video_upload']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['video_upload']['tmp_name'];
        $originalName = $_FILES['video_upload']['name'];

        // Get extension
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);

        // Generate random string (e.g. 16 chars)
        $randomString = bin2hex(random_bytes(8)); // 16 hex characters

        // Final safe filename
        $fileName = $randomString . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        // $fileName = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $_FILES['video_upload']['name']);
        // $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $videoPath = "upload/$year/$month/" . $fileName;
        } else {
            echo json_encode(["status" => "error", "message" => "Video upload failed during move."]);
            exit;
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => uploadErrorMessage($_FILES['video_upload']['error'])
        ]);
        exit;
    }
} else {
    echo json_encode(["status" => "error", "message" => "No file field named video_upload was found in the request."]);
    exit;
}

$preview_path = "upload/previews/" . $fileName;
$ffprobe = "ffprobe"; // path to ffprobe, adjust if needed
$resolution = '';
$codec = '';
$duration = '';
$cameraType = 'Unknown'; 

if (file_exists($targetFile)) {
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
}

// Collect form data
$event        = $_POST['event'] ?? '';
$faces        = trim($_POST['faces']);
$location     = $_POST['location'] ?? '';
$cameraman    = $_POST['cameraman'] ?? '';
$description  = $_POST['description'] ?? '';
$date         = !empty($_POST['date']) ? $_POST['date'] : null;
$usage_rights = $_POST['usage_rights'] ?? '';
$embargo_date = !empty($_POST['embargo_date']) ? $_POST['embargo_date'] : null;

// Save to DB
$stmt = $conn->prepare("
    INSERT INTO video_metadata 
    (event, faces, location, cameraman, description, date, usage_rights, embargo_date, filename, resolution, codec, camera_type, duration, preview, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param(
    "ssssssssssssss", 
    $event, 
    $faces, 
    $location, 
    $cameraman, 
    $description, 
    $date, 
    $usage_rights, 
    $embargo_date, 
    $videoPath,
    $resolution,
    $codec,
    $cameraType,
    $duration,
    $preview_path
);

if ($stmt->execute()) {
    $videoId = $stmt->insert_id;

    // Add log entry
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        $logStmt = $conn->prepare("INSERT INTO video_logs (video_id, action, action_by) VALUES (?, 'created', ?)");
        $logStmt->bind_param("ii", $videoId, $userId);
        if (!$logStmt->execute()) {
            error_log("Log insert error: " . $logStmt->error);
        }
        $logStmt->close();
    }

    echo json_encode(["status" => "success", "message" => "Video uploaded and metadata saved"]);
} else {
    error_log("Main insert error: " . $stmt->error); // log the actual error
    echo json_encode(["status" => "error", "message" => "Database insert failed", "error" => $stmt->error]);
}


$stmt->close();
$conn->close();

header("Location: view.php");
?>
