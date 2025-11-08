<?php
session_start();
include 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get form values
$first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
$last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
$year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
$course = mysqli_real_escape_string($conn, $_POST['course']);
$contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
$address = mysqli_real_escape_string($conn, $_POST['address']);

// Handle photo upload
$photo_path = null; // default
if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $photo = $_FILES['photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    
    if (!in_array($photo['type'], $allowed_types)) {
        $_SESSION['error'] = "Invalid image format. Allowed: jpg, jpeg, png, gif.";
        header("Location: ../complete_profile.php");
        exit();
    }

    // Move uploaded file
    $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
    $new_name = 'student_' . $student_id . '_' . time() . '.' . $ext;
    $upload_dir = '../uploads/';
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (!move_uploaded_file($photo['tmp_name'], $upload_dir . $new_name)) {
        $_SESSION['error'] = "Error uploading photo.";
        header("Location: ../complete_profile.php");
        exit();
    }

    $photo_path = $new_name; // save only filename in DB
}

// Build update query
if ($photo_path) {
    $stmt = $conn->prepare("UPDATE student SET first_name=?, last_name=?, year_level=?, course=?, contact_number=?, address=?, photo=? WHERE id=?");
    $stmt->bind_param("sssssssi", $first_name, $last_name, $year_level, $course, $contact_number, $address, $photo_path, $student_id);
} else {
    $stmt = $conn->prepare("UPDATE student SET first_name=?, last_name=?, year_level=?, course=?, contact_number=?, address=? WHERE id=?");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $year_level, $course, $contact_number, $address, $student_id);
}

// Execute
if ($stmt->execute()) {
    $_SESSION['success'] = "Profile updated successfully!";
    unset($_SESSION['student_id']); // remove temporary session
    
    // ✅ FIXED: Keep the original user_id, role, and email from login
    // Don't overwrite $_SESSION['user_id'] - it's already set from login!
    // The user_id should remain the one from the 'users' table, not the student table
    
    header("Location: ../dashboard/student_dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Error updating profile: " . $conn->error;
    header("Location: ../complete_profile.php");
    exit();
}
?>