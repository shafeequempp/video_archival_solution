<?php
include '../connect.php';
include '../config.php';

$search = $_GET['search'] ?? '';
$tag    = $_GET['tag'] ?? '';
$where  = "1=1";

// Search filter (event, location, cameraman, description)
if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $where .= " AND (
        vm.event LIKE '%$safeSearch%' OR
        vm.location LIKE '%$safeSearch%' OR
        vm.cameraman LIKE '%$safeSearch%' OR
        vm.description LIKE '%$safeSearch%' OR
        vm.filename LIKE '%$safeSearch%'
    )";
}

// Tag (faces) filter
if ($tag !== '') {
    $tags = explode(',', $tag);
    foreach ($tags as $t) {
        $t = trim($conn->real_escape_string($t));
        if ($t !== '') {
            $where .= " AND vm.faces LIKE '%$t%'";
        }
    }
}

// Query videos
$sql = "
    SELECT vm.*
    FROM video_metadata vm
    WHERE $where AND deleted_at IS NULL
    ORDER BY vm.id DESC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
<tr>
    <td><?= htmlspecialchars($row['event']) ?></td>
    <td>
        <?php
            $faceJson = json_decode($row['faces'], true);
            if (is_array($faceJson)) {
                foreach ($faceJson as $faceObj) {
                $faceValue = htmlspecialchars(trim($faceObj['value'] ?? ''));
                if ($faceValue !== '') {
                    echo '<span class="badge bg-secondary text-dark me-1" style="cursor:pointer;">' . $faceValue . '</span>';
                }
                }
            }
        ?>
    </td>
    <td><?= htmlspecialchars($row['location']) ?></td>
    <td><?= htmlspecialchars($row['cameraman']) ?></td>
    <td><?= date('d-m-Y',strtotime($row['date'])) ?></td>
    <td><?= htmlspecialchars($row['filename']) ?></td>
    <td>
        <button class="btn btn-sm btn-primary preview-btn" 
            data-duration="<?= $row['duration'] ?>" 
            data-resolution="<?= $row['resolution'] ?>" 
            data-ogvideo="<?php echo BASE_URL; ?><?= $row['filename'] ?>" 
            data-preview="<?php echo BASE_URL; ?><?= htmlspecialchars($row['preview']) ?>">
        <i class="fa fa-play"></i> Preview </button>
        
        <?php if ($_SESSION['role'] == 'ADMIN') { ?>
        <a href="edit?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i> Edit</a>
        <button onclick="softDelete(<?= $row['id'] ?>)" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Delete</button>
        <?php } ?>
    </td>
</tr>
<?php
    endwhile;
else:
?>
<tr>
    <td colspan="<?= ($_SESSION['role'] == 'ADMIN') ? 7 : 6 ?>" class="text-center text-muted">
        No results found
    </td>
</tr>
<?php
endif;

$conn->close();
?>
