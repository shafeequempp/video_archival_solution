<?php
include '../layouts/header.php';
include '../connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$name = $email = $role = "";
$message = isset($_GET['message']) ? $_GET['message'] : "";
$error = isset($_GET['error']) ? $_GET['error'] : "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('phpMailer/Exception.php');
    require_once('phpMailer/PHPMailer.php');
    require_once('phpMailer/SMTP.php');

    $name  = $_POST['name'];
    $email = $_POST['email'];
    $role  = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $password_hint = $_POST['password'];

    // Validate domain
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@mpp\.co\.in$/', $email)) {
        $error = "Only mpp.co.in emails are allowed!";
    } else {
        // Check if email exists (not deleted)
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? AND is_deleted = 0");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Check if deleted
            $checkDeleted = $conn->prepare("SELECT id FROM admin WHERE email = ? AND is_deleted = 1");
            $checkDeleted->bind_param("s", $email);
            $checkDeleted->execute();
            $checkDeleted->store_result();

            if ($checkDeleted->num_rows > 0) {
                $checkDeleted->bind_result($deleted_id);
                $checkDeleted->fetch();
                $checkDeleted->close();

                $update = $conn->prepare("UPDATE admin SET name = ?, password = ?, password_hint = ?, role = ?, is_deleted = 0 WHERE id = ?");
                $update->bind_param("ssssi", $name, $password, $password_hint, $role, $deleted_id);

                if ($update->execute()) {
                    $message = "Deleted user reactivated successfully.";
                    $name = $email = $role = "";
                } else {
                    $error = "Error updating deleted user: " . $update->error;
                }
                $update->close();
            } else {
                // New user insert
                $insert = $conn->prepare("INSERT INTO admin (name, email, password, password_hint, role, is_deleted, created_at)
                          VALUES (?, ?, ?, ?, ?, 0, NOW())");
                $insert->bind_param("sssss", $name, $email, $password, $password_hint, $role);

                if ($insert->execute()) {
                    $message = "User added successfully.";
                    $mail = new PHPMailer();
                    $mail->isSMTP();
                    $mail->isHTML(true);
                    $mail->SMTPAuth   = true;
                    $mail->SMTPSecure = 'tls';
                    $mail->SMTPDebug  = 0;

                    $mail->Username = 'AKIARV2ZBJK4MMNB47H6';
                    $mail->Password = 'BFN62AC026GmLDjKmk3GW8AoBGNd7pdIDTbW2PUKlVHS';
                    $mail->Host = 'email-smtp.ap-south-1.amazonaws.com';        
                    $mail->Port = 587;
                    $mail->setFrom('no-reply@mathrubhumi.com', 'Mathrubhumi.com');
                    $mail->addAddress($email, $name);
                    $mail->Subject  = "Access Granted to Dashboard";
                    $mail->Body = "
                        <p>Dear {$name},</p>
                        <p>You have been granted access to the Dashboard Console.</p>
                        <p><strong>Role:</strong> {$role}</p>
                        <p><a href='" . BASE_URL . "'>Click here to login</a></p>
                        <p>Regards,<br>Admin Team</p>
                    ";
                    $mail->send();
                    $name = $email = $role = "";
                } else {
                    $error = "Error: " . $insert->error;
                }
                $insert->close();
            }
        }
        $stmt->close();
    }
}

$users = $conn->query("SELECT * FROM admin WHERE is_deleted = 0 ORDER BY id DESC");
?>

<?php if ($message): ?>
  <div class="alert alert-success auto-hide"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger auto-hide"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="forms-sample">
  <div class="row">
    <div class="col-md-6 grid-margin">
      <div class="card">
        <div class="card-body row">
          <h4 class="card-title">Create User</h4>

          <div class="form-group col-sm-12">
            <label>Full Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($name) ?>" required>
          </div>

          <div class="form-group col-sm-12">
            <label>Email <small class="text-muted">(only mpp.co.in allowed)</small></label>
            <input type="email" class="form-control" name="email"
              pattern="^[a-zA-Z0-9._%+-]+@mpp\.co\.in$"
              value="<?= htmlspecialchars($email) ?>" required>
          </div>
          
          <div class="form-group col-sm-12">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required minlength="6" autocomplete="off">
          </div>

          <div class="form-group col-sm-12">
            <label>Role</label>
            <select class="form-select" name="role" required>
              <option value="">Select Role</option>
              <option value="ADMIN" <?= $role === 'ADMIN' ? 'selected' : '' ?>>Admin</option>
              <option value="USER" <?= $role === 'USER' ? 'selected' : '' ?>>User</option>
            </select>
          </div>

          <div class="form-group col-12 text-end">
            <button type="submit" class="btn btn-primary">Create User</button>
          </div>
        </div>
      </div>
    </div>

    <!-- USER LIST -->
    <div class="col-md-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">User List</h4>
          <input type="text" id="userSearch" class="form-control mb-3" placeholder="Search users...">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              <?php $i = 0; while ($user = $users->fetch_assoc()): ?>
                <tr>
                  <td><?= ++$i ?></td>
                  <td><?= htmlspecialchars($user['name']) ?></td>
                  <td><?= htmlspecialchars($user['email']) ?></td>
                  <td><?= htmlspecialchars($user['role']) ?></td>
                  <td>
                    <a href="edit?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger delete-btn" data-id="<?= $user['id'] ?>">Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Delete button
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      Swal.fire({
        title: 'Are you sure?',
        text: "This will delete the user!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'delete?id=' + id;
        }
      });
    });
  });

  // User search filter
  document.getElementById('userSearch').addEventListener('keyup', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
});
</script>

<?php include '../layouts/footer.php'; ?>
