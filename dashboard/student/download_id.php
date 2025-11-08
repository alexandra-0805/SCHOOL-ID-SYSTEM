<?php
session_start();
require_once("../../includes/db_connect.php");

// Ensure logged-in student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
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
    echo "Student not found.";
    exit();
}

$full_name = trim($student['first_name'] . ' ' . $student['last_name']);
$student_id = $student['student_id'] ?: $student['id'];
$course = $student['course'] ?? '';
$year = $student['year_level'] ?? '';
$photo_path = $student['photo'] ? '../../uploads/' . htmlspecialchars($student['photo']) : '../../assets/img/default_user.png';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Download ID - <?= htmlspecialchars($full_name) ?></title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <style>
    /* Print-ready card styling */
    .id-card {
      width: 350px;
      height: 220px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,.15);
      padding: 12px;
      background: linear-gradient(180deg, #fff 0%, #f7f9fc 100%);
      font-family: Arial, sans-serif;
    }
    .id-card .left {
      float:left;
      width: 35%;
      text-align:center;
    }
    .id-card .left img {
      border-radius: 6px;
      width: 90px;
      height: 90px;
      object-fit: cover;
    }
    .id-card .right {
      float:right;
      width: 60%;
      padding-left: 10px;
      box-sizing: border-box;
    }
    .id-card h4 { margin:0; font-size:16px; }
    .id-card p { margin:3px 0; font-size:12px; }
    .qr {
      position:absolute;
      right:12px;
      bottom:12px;
    }

    /* Print rules: center on page */
    @media print {
      body { margin:0; }
      .no-print { display:none; }
      .print-center {
        display:flex;
        align-items:center;
        justify-content:center;
        height:100vh;
      }
    }
  </style>
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Printable ID</h4>
    <div class="no-print">
      <button onclick="window.print()" class="btn btn-primary btn-sm">Print / Save as PDF</button>
      <a class="btn btn-outline-secondary btn-sm" href="qr.php?download=1">Download QR</a>
      <a class="btn btn-secondary btn-sm" href="student_dashboard.php">Back</a>
    </div>
  </div>

  <div class="print-center">
    <div class="id-card position-relative">
      <div class="left">
        <img src="<?= $photo_path ?>" alt="Photo">
        <p style="font-size:11px;margin-top:6px;">Student</p>
      </div>
      <div class="right">
        <h4><?= htmlspecialchars($full_name) ?></h4>
        <p><strong>ID:</strong> <?= htmlspecialchars($student_id) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($course) ?></p>
        <p><strong>Year:</strong> <?= htmlspecialchars($year) ?></p>
        <p style="font-size:11px;color:#666;">Issued: <?= date('Y-m-d') ?></p>
      </div>

      <div class="qr">
        <img src="qr.php" alt="QR" width="90" height="90">
      </div>

      <div style="clear:both"></div>
    </div>
  </div>
</div>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
