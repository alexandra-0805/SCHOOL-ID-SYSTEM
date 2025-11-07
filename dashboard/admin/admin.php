<?php
session_start();
require_once("../../includes/db_connect.php");

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

// Handle actions: Approve, Verify, Delete
if (isset($_GET['action']) && isset($_GET['id'])) {
  $user_id = intval($_GET['id']);
  $action = $_GET['action'];
  $admin_id = $_SESSION['user_id'];

  if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE users SET status='approved' WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->query("INSERT INTO activity_logs (admin_id, action, target_user) VALUES ($admin_id, 'Approved user ID', $user_id)");
  } 
  elseif ($action === 'verify') {
    // ✅ FIXED: Changed 'verified' to 'is_verified'
    $stmt = $conn->prepare("UPDATE users SET is_verified=1 WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->query("INSERT INTO activity_logs (admin_id, action, target_user) VALUES ($admin_id, 'Verified user email', $user_id)");
  } 
  elseif ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->query("INSERT INTO activity_logs (admin_id, action, target_user) VALUES ($admin_id, 'Deleted user account', $user_id)");
  }

  elseif ($action === 'unapprove') {
    $stmt = $conn->prepare("UPDATE users SET status='pending' WHERE user_id=?");
    $stmt->bind_param("i", $user_id); $stmt->execute(); $stmt->close();
    $conn->query("INSERT INTO activity_logs (admin_id, action, target_user) VALUES ($admin_id, 'Unapproved user ID', $user_id)");
}
elseif ($action === 'unverify') {
    $stmt = $conn->prepare("UPDATE users SET is_verified=0 WHERE user_id=?");
    $stmt->bind_param("i", $user_id); $stmt->execute(); $stmt->close();
    $conn->query("INSERT INTO activity_logs (admin_id, action, target_user) VALUES ($admin_id, 'Unverified user email', $user_id)");
}

  header("Location: admin.php");
  exit();
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY user_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | School ID System</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Admin Dashboard</h2>
      <a href="../../logout.php" class="btn btn-secondary btn-sm">Logout</a>
    </div>
    
    <div class="card shadow">
      <div class="card-header bg-success text-white">User Management</div>
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>User ID</th>
              <th>Email</th>
              <th>Role</th>
              <th>Verified</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <div class="mb-2">
  <input type="text" id="searchBox" class="form-control form-control-sm w-25"
         placeholder="Search e-mail…">
</div>

<tbody id="userTable">
<?php while ($row = $users->fetch_assoc()): ?>
  <tr data-email="<?= strtolower($row['email']) ?>">
    <td><?= $row['user_id'] ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td><?= $row['role'] ?></td>
    <td><?= $row['is_verified'] ? '✅' : '❌' ?></td>
    <td><?= $row['status'] ?></td>
    <td class="text-nowrap">

      <?php if ($row['status'] === 'approved'): ?>
        <a href="?action=unapprove&id=<?= $row['user_id'] ?>" class="btn btn-warning btn-sm">Unapprove</a>
      <?php else: ?>
        <a href="?action=approve&id=<?= $row['user_id'] ?>" class="btn btn-success btn-sm">Approve</a>
      <?php endif; ?>

      <?php if ($row['is_verified']): ?>
        <a href="?action=unverify&id=<?= $row['user_id'] ?>" class="btn btn-outline-info btn-sm">Unverify</a>
      <?php else: ?>
        <a href="?action=verify&id=<?= $row['user_id'] ?>" class="btn btn-info btn-sm">Verify</a>
      <?php endif; ?>

      <a href="?action=delete&id=<?= $row['user_id'] ?>"
         class="btn btn-danger btn-sm"
         onclick="return confirm('Are you sure?');">Delete</a>
    </td>
  </tr>
<?php endwhile; ?>
</tbody>

<script>
/* ➋ 3-line live filter */
document.getElementById('searchBox').addEventListener('input', e => {
  const q = e.target.value.toLowerCase();
  document.querySelectorAll('#userTable tr').forEach(tr =>
    tr.style.display = tr.dataset.email.includes(q) ? '' : 'none'
  );
});
</script>
        </table>
      </div>
    </div>

    <div class="card mt-4 shadow">
      <div class="card-header bg-dark text-white">Recent Activity Logs</div>
      <div class="card-body">
        <?php
        $logs = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10");
        if ($logs->num_rows > 0):
        ?>
          <ul class="list-group">
            <?php while ($log = $logs->fetch_assoc()): ?>
              <li class="list-group-item">
                [<?= $log['created_at'] ?>] Admin #<?= $log['admin_id'] ?> — <?= htmlspecialchars($log['action']) ?> (User #<?= $log['target_user'] ?>)
              </li>
            <?php endwhile; ?>
          </ul>
        <?php else: ?>
          <p>No activity logs yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>