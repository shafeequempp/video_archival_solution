<?php
include 'layouts/header.php';
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error   = "";

// Handle password change submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password     = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_id = $_SESSION['user_id'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirmation do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long!";
    } else {
        // Fetch current hashed password
        $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current_password, $hashed_password)) {
            $error = "Current password is incorrect!";
        } else {
            // Update password
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update = $conn->prepare("UPDATE admin SET password = ?,password_hint = ? WHERE id = ?");
            $update->bind_param("ssi", $new_hashed_password, $new_password, $user_id);

            if ($update->execute()) {
                $message = "Password changed successfully!";
            } else {
                $error = "Failed to update password: " . $update->error;
            }
            $update->close();
        }
    }
}
?>

<?php if ($message): ?>
  <div class="alert alert-success auto-hide"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger auto-hide"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-center align-items-center" style="height: 80vh;">
    <div class="card shadow-lg p-4" style="width: 400px; border-radius: 20px;">
        <h4 class="text-center mb-4">🔒 Change Password</h4>
        <form method="POST">
            <div class="mb-3">
                <label for="current_password" class="form-label fw-bold">Current Password</label>
                <input type="password" class="form-control rounded-pill" id="current_password" name="current_password" placeholder="Enter current password" required>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label fw-bold">New Password</label>
                <input type="password" class="form-control rounded-pill" id="new_password" name="new_password" placeholder="Enter new password" required>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                <input type="password" class="form-control rounded-pill" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill">Update Password</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Auto-hide success or error messages
  setTimeout(() => {
    document.querySelectorAll('.auto-hide').forEach(alert => alert.remove());
  }, 4000);
});
</script>

<?php include 'layouts/footer.php'; ?>
