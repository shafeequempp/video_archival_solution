<?php
ob_start();
include '../layouts/header.php';
include '../connect.php';

// Get user by ID
$id = (int) $_GET['id'];
$currentUser = $conn->query("SELECT * FROM admin WHERE id = $id")->fetch_assoc();

if (!$currentUser) {
    echo "<div class='alert alert-danger'>User not found!</div>";
    include '../layouts/footer.php';
    exit;
}

// Handle form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $role  = $_POST['role'];

    // Server-side domain restriction
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@mpp\.co\.in$/', $email)) {
        echo "<div class='alert alert-danger'>Only emails ending with @mpp.co.in are allowed.</div>";
    } else {
        $stmt = $conn->prepare("UPDATE admin SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $role, $id);
        if ($stmt->execute()) {
            header("Location: index?message=User Updated Successfully");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Error updating user: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

$users = $conn->query("SELECT * FROM admin WHERE is_deleted = 0 ORDER BY id DESC");
?>

<form method="POST" class="forms-sample">
  <div class="row justify-content-center">
    <div class="col-md-6 grid-margin">
      <div class="card">
        <div class="card-body row">
          <h4 class="card-title">Edit User</h4>

          <div class="form-group col-sm-12">
            <label>Full Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($currentUser['name']) ?>" required>
          </div>

          <div class="form-group col-sm-12">
            <label>Email <small class="text-muted">(only @mpp.co.in allowed)</small></label>
            <input type="email" class="form-control" name="email"
              pattern="^[a-zA-Z0-9._%+-]+@mpp\.co\.in$"
              value="<?= htmlspecialchars($currentUser['email']) ?>" required>
          </div>

          <div class="form-group col-sm-12">
            <label>Role</label>
            <select class="form-select" name="role" required>
              <option value="">Select Role</option>
              <option value="ADMIN" <?= $currentUser['role'] === 'ADMIN' ? 'selected' : '' ?>>Admin</option>
              <option value="USER" <?= $currentUser['role'] === 'USER' ? 'selected' : '' ?>>User</option>
            </select>
          </div>

          <div class="form-group col-12 text-end">
            <a href="index" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-success">Update User</button>
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
              <?php $i = 0; while ($u = $users->fetch_assoc()): ?>
                <tr>
                  <td><?= ++$i ?></td>
                  <td><?= htmlspecialchars($u['name']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><?= htmlspecialchars($u['role']) ?></td>
                  <td>
                    <a href="edit?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger delete-btn" data-id="<?= $u['id'] ?>">Delete</a>
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
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete the user!',
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

    document.getElementById('userSearch').addEventListener('keyup', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
});
</script>

<?php include '../layouts/footer.php'; ob_end_flush(); ?>
