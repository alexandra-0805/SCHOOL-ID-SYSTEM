<?php
session_start();
include 'db_connect.php';

// Check if logged in and is student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['email'];

// Get form values
$first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
$last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
$year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
$course = mysqli_real_escape_string($conn, $_POST['course']);
$contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
$address = mysqli_real_escape_string($conn, $_POST['address']);

// Handle photo upload (optional)
$photo_path = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $photo = $_FILES['photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    
    if (!in_array($photo['type'], $allowed_types)) {
        $_SESSION['error'] = "Invalid image format. Allowed: jpg, jpeg, png, gif.";
        header("Location: ../dashboard/edit_profile.php");
        exit();
    }

    // Get student ID for filename
    $student_query = mysqli_query($conn, "SELECT id FROM student WHERE email='$email' LIMIT 1");
    $student = mysqli_fetch_assoc($student_query);

    // Move uploaded file
    $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
    $new_name = 'student_' . $student['id'] . '_' . time() . '.' . $ext;
    $upload_dir = '../uploads/';
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (!move_uploaded_file($photo['tmp_name'], $upload_dir . $new_name)) {
        $_SESSION['error'] = "Error uploading photo.";
        header("Location: ../dashboard/edit_profile.php");
        exit();
    }

    $photo_path = $new_name;
}

// Build update query
if ($photo_path) {
    // Update with new photo
    $stmt = $conn->prepare("UPDATE student SET first_name=?, last_name=?, year_level=?, course=?, contact_number=?, address=?, photo=? WHERE email=?");
    $stmt->bind_param("ssssssss", $first_name, $last_name, $year_level, $course, $contact_number, $address, $photo_path, $email);
} else {
    // Update without changing photo
    $stmt = $conn->prepare("UPDATE student SET first_name=?, last_name=?, year_level=?, course=?, contact_number=?, address=? WHERE email=?");
    $stmt->bind_param("sssssss", $first_name, $last_name, $year_level, $course, $contact_number, $address, $email);
}

// Execute
if ($stmt->execute()) {
    // Update full_name in users table too
    $full_name = $first_name . ' ' . $last_name;
    $update_users = $conn->prepare("UPDATE users SET full_name=? WHERE email=?");
    $update_users->bind_param("ss", $full_name, $email);
    $update_users->execute();
    $update_users->close();

    // Update session variable
    $_SESSION['full_name'] = $full_name;

    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: ../dashboard/student.php");
    exit();
} else {
    $_SESSION['error'] = "Error updating profile: " . $conn->error;
    header("Location: ../dashboard/edit_profile.php");
    exit();
}
?>