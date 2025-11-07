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

    <form action="includes/update_profile.php" method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" class="form-control" value="<?= htmlspecialchars($student['email']); ?>" readonly>
      </div>

      <div class="mb-3">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" required>
      </div>

      <!-- Year Level Dropdown -->
      <div class="mb-3">
        <label>Year Level</label>
        <select name="year_level" class="form-control" required>
          <option value="">Select Year Level</option>
          <option value="1st Year">1st Year</option>
          <option value="2nd Year">2nd Year</option>
          <option value="3rd Year">3rd Year</option>
          <option value="4th Year">4th Year</option>
        </select>
      </div>

      <!-- Course Dropdown -->
      <div class="mb-3">
        <label>Course</label>
        <select name="course" class="form-control" required>
          <option value="">Select Course</option>
          <option value="BS Information System">BS Information System</option>
          <option value="BS Psychology">BS Psychology</option>
          <option value="BS Nursing">BS Nursing</option>
          <option value="BS Engineering">BS Engineering</option>
          <option value="BS Life Science">BS Life Science</option>
          <option value="BS Midwifery">BS Midwifery</option>
          <option value="BS Computer Science">BS Computer Science</option>
        </select>
      </div>

      <div class="mb-3">
        <label>Contact Number</label>
        <input type="text" name="contact_number" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Address</label>
        <input type="text" name="address" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Photo</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
      </div>

      <button type="submit" class="btn btn-success w-100">Save Information</button>
    </form>
  </div>

</body>
</html>
