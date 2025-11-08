<?php
session_start();
require_once("../../includes/db_connect.php");

// Check if logged in (works for all roles)
if (!isset($_SESSION['user_id'])) {
  header("Location: ../../index.php");
  exit();
}

$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password | School ID System</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">

  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Change Password</h2>
      <a href="student_dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
      <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif(isset($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-header bg-warning text-dark">
            <i class="bi bi-shield-lock"></i> Update Your Password
          </div>
          <div class="card-body">
            <form action="../../includes/process_change_password.php" method="POST" id="passwordForm">
              
              <div class="mb-3">
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" minlength="6" required>
                <small class="text-muted">Minimum 6 characters</small>
              </div>

              <div class="mb-3">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="6" required>
              </div>

              <div id="passwordError" class="alert alert-danger d-none">Passwords do not match!</div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning">Change Password</button>
                <a href="student_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
              </div>
            </form>
          </div>
        </div>

        <!-- Password Requirements -->
        <div class="card mt-3 shadow-sm">
          <div class="card-body">
            <h6>Password Requirements:</h6>
            <ul class="small mb-0">
              <li>At least 6 characters long</li>
              <li>Make it unique and hard to guess</li>
              <li>Don't reuse old passwords</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../../assets/js/bootstrap.bundle.min.js"></script>
  <script>
    // Client-side password match validation
    const form = document.getElementById('passwordForm');
    const newPass = document.getElementById('new_password');
    const confirmPass = document.getElementById('confirm_password');
    const errorDiv = document.getElementById('passwordError');

    form.addEventListener('submit', function(e) {
      if (newPass.value !== confirmPass.value) {
        e.preventDefault();
        errorDiv.classList.remove('d-none');
        confirmPass.focus();
      } else {
        errorDiv.classList.add('d-none');
      }
    });

    // Hide error when user starts typing
    confirmPass.addEventListener('input', function() {
      if (newPass.value === confirmPass.value) {
        errorDiv.classList.add('d-none');
      }
    });
  </script>
</body>
</html>