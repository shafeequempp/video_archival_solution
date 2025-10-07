<?php
include '../layouts/header.php';
include '../connect.php';

$contact_id = intval($_GET['id'] ?? 0);
if ($contact_id <= 0) {
    echo "<div class='alert alert-danger'>Invalid contact ID</div>";
    include '../layouts/footer.php';
    exit;
}

$contact = $conn->query("SELECT event FROM video_metadata WHERE id = $contact_id")->fetch_assoc();

$sql = "
  SELECT l.*, a.name AS admin_name, a.email AS admin_email
  FROM video_logs l
  LEFT JOIN admin a ON l.action_by = a.id
  WHERE l.contact_id = $contact_id
  ORDER BY l.created_at DESC
";
$logs = $conn->query($sql);
?>

<div class="card">
  <div class="card-body">
    <div class="card-title d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Log for <?= htmlspecialchars($contact['event']) ?></h4>
        <div>
            <a href="view" class="btn btn-sm btn-primary"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Action</th>
            <th>Performed By</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 0; 
            if ($logs->num_rows > 0):
            while ($log = $logs->fetch_assoc()): ?>
            <tr>
              <td><?= ++$i ?></td>
              <td><span class="badge bg-<?= $log['action'] == 'deleted' ? 'danger' : ($log['action'] == 'updated' ? 'warning' : 'success') ?>">
                <?= ucfirst($log['action']) ?></span>
              </td>
              <td><?= htmlspecialchars($log['admin_name'] . ' (' . $log['admin_email'] . ')') ?></td>
              <td><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></td>
            </tr>
          <?php 
        endwhile;
        else:
        ?>
          <tr><td colspan="8" class="text-center text-muted">No Logs found</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include '../layouts/footer.php'; ?>
