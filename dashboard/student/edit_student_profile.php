<?php
session_start();
require_once("../../includes/db_connect.php");

// Check if logged in and role is student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header("Location: ../../index.php");
  exit();
}

$email = $_SESSION['email'];

// Fetch student info by email
$stmt = $conn->prepare("SELECT * FROM student WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
  $_SESSION['error'] = "Student profile not found.";
  header("Location: student_dashboard.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile | School ID System</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">

  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Edit Profile</h2>
      <a href="student_dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
      <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif(isset($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow">
      <div class="card-header bg-primary text-white">Update Your Information</div>
      <div class="card-body">
        <form action="../../includes/process_edit_profile.php" method="POST" enctype="multipart/form-data">
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label>First Name</label>
              <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label>Last Name</label>
              <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Year Level</label>
              <select name="year_level" class="form-control" required>
                <option value="">Select Year Level</option>
                <option value="1st Year" <?= $student['year_level'] == '1st Year' ? 'selected' : '' ?>>1st Year</option>
                <option value="2nd Year" <?= $student['year_level'] == '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                <option value="3rd Year" <?= $student['year_level'] == '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                <option value="4th Year" <?= $student['year_level'] == '4th Year' ? 'selected' : '' ?>>4th Year</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Course</label>
              <select name="course" class="form-control" required>
                <option value="">Select Course</option>
                <option value="BS Information System" <?= $student['course'] == 'BS Information System' ? 'selected' : '' ?>>BS Information System</option>
                <option value="BS Psychology" <?= $student['course'] == 'BS Psychology' ? 'selected' : '' ?>>BS Psychology</option>
                <option value="BS Nursing" <?= $student['course'] == 'BS Nursing' ? 'selected' : '' ?>>BS Nursing</option>
                <option value="BS Engineering" <?= $student['course'] == 'BS Engineering' ? 'selected' : '' ?>>BS Engineering</option>
                <option value="BS Life Science" <?= $student['course'] == 'BS Life Science' ? 'selected' : '' ?>>BS Life Science</option>
                <option value="BS Midwifery" <?= $student['course'] == 'BS Midwifery' ? 'selected' : '' ?>>BS Midwifery</option>
                <option value="BS Computer Science" <?= $student['course'] == 'BS Computer Science' ? 'selected' : '' ?>>BS Computer Science</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label>Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($student['contact_number']) ?>" required>
          </div>

          <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($student['address']) ?></textarea>
          </div>

          <div class="mb-3">
            <label>Current Photo</label><br>
            <?php if (!empty($student['photo'])): ?>
              <img src="../../uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Current Photo" class="rounded mb-2" width="100" height="100">
            <?php else: ?>
              <img src="../../assets/img/default_user.png" alt="No Photo" class="rounded mb-2" width="100" height="100">
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label>Upload New Photo (optional)</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
            <small class="text-muted">Leave empty to keep current photo. Accepted formats: JPG, PNG, GIF</small>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="student_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>