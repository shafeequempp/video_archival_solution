<?php
// config
$ffmpeg = 'ffmpeg'; // or 'C:\\ffmpeg\\bin\\ffmpeg.exe' on Windows
$input  = '../upload/2025/08/9sv0lhPA-33943525.mp4';  // your 1GB source
$outDir = __DIR__ . '/upload/previews';
if (!is_dir($outDir)) mkdir($outDir, 0775, true);

$preview  = $outDir . '/preview_360p.mp4';  // full low-res
$teaser   = $outDir . '/teaser_240p.mp4';   // 10-second teaser

// helper
function run($cmd) {
    $out = [];
    $ret = 0;
    exec($cmd . ' 2>&1', $out, $ret);
    return ['code' => $ret, 'out' => implode("\n", $out)];
}

$in  = escapeshellarg($input);
$te  = escapeshellarg($teaser);
$pr  = escapeshellarg($preview);
$th  = escapeshellarg($thumb);

$cmdTeaser = "$ffmpeg -y -ss 0 -t 180 -i $in -vf scale=-2:240 -c:v libx264 -preset veryfast -crf 30 -c:a aac -b:a 96k -movflags +faststart $te";
$r1 = run($cmdTeaser);
//$cmdPreview = "$ffmpeg -y -i $in -vf scale=-2:360 -c:v libx264 -preset veryfast -crf 28 -c:a aac -b:a 96k -movflags +faststart $pr";

//echo $cmdPreview;


$ffprobe = "ffprobe"; // path to ffprobe, adjust if needed
$metadata = shell_exec("$ffprobe -v quiet -print_format json -show_format -show_streams " . escapeshellarg($input));
$metadata = json_decode($metadata, true);

$resolution = '';
$codec = '';
$duration = '';
$cameraType = 'Unknown'; // Optional field if not embedded in metadata

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

// Camera model if metadata exists (some devices embed this)
if (!empty($metadata['format']['tags']['com.apple.quicktime.make'])) {
    $cameraType = $metadata['format']['tags']['com.apple.quicktime.make'] . ' ' . ($metadata['format']['tags']['com.apple.quicktime.model'] ?? '');
} elseif (!empty($metadata['format']['tags']['model'])) {
    $cameraType = $metadata['format']['tags']['model'];
}
//$r2 = run($cmdPreview);

// simple output
header('Content-Type: text/plain');
echo $resolution;
echo $codec;
echo $cameraType;
echo $duration;
echo "Teaser code: {$r1['code']}\n{$r1['out']}\n\n";
//echo "Preview code: {$r2['code']}\n{$r2['out']}\n\n";
echo "Done.\n";

?>