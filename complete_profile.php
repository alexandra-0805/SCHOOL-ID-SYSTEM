<?php
session_start();
include 'includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['student_id'])) {
  header("Location: index.php");
  exit;
}

$student_id = $_SESSION['student_id'];

// Fetch existing student info
$result = $conn->query("SELECT * FROM student WHERE id='$student_id' LIMIT 1");
$student = $result->fetch_assoc();

// Check if profile is already complete
// Redirect to appropriate dashboard if all required fields are filled
if (!empty($student['first_name']) && 
    !empty($student['last_name']) && 
    !empty($student['year_level']) && 
    !empty($student['course']) && 
    !empty($student['contact_number']) && 
    !empty($student['address'])) {
  
  // Set proper session variables
  unset($_SESSION['student_id']); // remove temporary session
  $_SESSION['user_id'] = $student_id;
  $_SESSION['role'] = 'student';
  
  // Redirect to student dashboard
  header("Location: dashboard/student.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Complete Your Profile | School ID System</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

  <div class="card shadow p-4" style="width: 30rem;">
    <h4 class="text-center mb-3">Complete Your Information</h4>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <form action="includes/update_profile.php" method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" class="form-control" value="<?= htmlspecialchars($student['email']); ?>" readonly>
      </div>

      <div class="mb-3">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" 
               value="<?= htmlspecialchars($student['first_name'] ?? ''); ?>" required>
      </div>

      <div class="mb-3">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" 
               value="<?= htmlspecialchars($student['last_name'] ?? ''); ?>" required>
      </div>

      <!-- Year Level Dropdown -->
      <div class="mb-3">
        <label>Year Level</label>
        <select name="year_level" class="form-control" required>
          <option value="">Select Year Level</option>
          <option value="1st Year" <?= ($student['year_level'] ?? '') == '1st Year' ? 'selected' : ''; ?>>1st Year</option>
          <option value="2nd Year" <?= ($student['year_level'] ?? '') == '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
          <option value="3rd Year" <?= ($student['year_level'] ?? '') == '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
          <option value="4th Year" <?= ($student['year_level'] ?? '') == '4th Year' ? 'selected' : ''; ?>>4th Year</option>
        </select>
      </div>

      <!-- Course Dropdown -->
      <div class="mb-3">
        <label>Course</label>
        <select name="course" class="form-control" required>
          <option value="">Select Course</option>
          <option value="BS Information System" <?= ($student['course'] ?? '') == 'BS Information System' ? 'selected' : ''; ?>>BS Information System</option>
          <option value="BS Psychology" <?= ($student['course'] ?? '') == 'BS Psychology' ? 'selected' : ''; ?>>BS Psychology</option>
          <option value="BS Nursing" <?= ($student['course'] ?? '') == 'BS Nursing' ? 'selected' : ''; ?>>BS Nursing</option>
          <option value="BS Engineering" <?= ($student['course'] ?? '') == 'BS Engineering' ? 'selected' : ''; ?>>BS Engineering</option>
          <option value="BS Life Science" <?= ($student['course'] ?? '') == 'BS Life Science' ? 'selected' : ''; ?>>BS Life Science</option>
          <option value="BS Midwifery" <?= ($student['course'] ?? '') == 'BS Midwifery' ? 'selected' : ''; ?>>BS Midwifery</option>
          <option value="BS Computer Science" <?= ($student['course'] ?? '') == 'BS Computer Science' ? 'selected' : ''; ?>>BS Computer Science</option>
        </select>
      </div>

      <div class="mb-3">
        <label>Contact Number</label>
        <input type="text" name="contact_number" class="form-control" 
               value="<?= htmlspecialchars($student['contact_number'] ?? ''); ?>" required>
      </div>

      <div class="mb-3">
        <label>Address</label>
        <input type="text" name="address" class="form-control" 
               value="<?= htmlspecialchars($student['address'] ?? ''); ?>" required>
      </div>

      <div class="mb-3">
        <label>Photo (Optional)</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
        <small class="text-muted">You can upload your photo later if needed.</small>
      </div>

      <button type="submit" class="btn btn-success w-100">Save Information</button>
    </form>
  </div>

</body>
</html>