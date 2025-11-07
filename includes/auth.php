<?php
session_start();
include 'db_connect.php';

/* -------------------
   REGISTER LOGIC
------------------- */
if (isset($_POST['register'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']); // 🧩 Now includes admin, teacher, student
    $token = bin2hex(random_bytes(32)); // email verification token

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Email already registered.";
        header("Location: ../register.php");
        exit();
    }

    // Insert into users table
    $insert = mysqli_query($conn, "INSERT INTO users (full_name, email, password_hash, verification_token, role, is_verified)
                                   VALUES ('$full_name', '$email', '$password', '$token', '$role', 0)");
    if ($insert) {
        // Create linked record for specific roles
        if ($role == 'student') {
            mysqli_query($conn, "INSERT INTO student (email, password) VALUES ('$email', '$password')");
        } elseif ($role == 'teacher') {
            mysqli_query($conn, "INSERT INTO teacher (email, password) VALUES ('$email', '$password')");
        }

        $_SESSION['success'] = "Account created! Please verify your email before logging in.";
        // TODO: send email verification with $token
    } else {
        $_SESSION['error'] = "Error creating account: " . mysqli_error($conn);
    }

    header("Location: ../register.php");
    exit();
}

/* -------------------
   LOGIN LOGIC
------------------- */
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($query) == 1) {
        $user = mysqli_fetch_assoc($query);

        // Check verification
        if (!$user['is_verified']) {
            $_SESSION['error'] = "Please verify your email first.";
            header("Location: ../index.php");
            exit();
        }

        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];

            /* -------------------
               ROLE-BASED REDIRECTS
            ------------------- */
            if ($user['role'] == 'admin') {
                // 🔑 Admin dashboard
                header("Location: ../dashboard/admin.php");
                exit();
            } elseif ($user['role'] == 'teacher') {
                // 👩‍🏫 Teacher dashboard
                header("Location: ../dashboard/teacher.php");
                exit();
            } else {
                // 🧩 Student check: if profile incomplete, redirect to form
                $check_student = mysqli_query($conn, "SELECT * FROM student WHERE email='$email' LIMIT 1");
                if ($check_student && mysqli_num_rows($check_student) > 0) {
                    $student = mysqli_fetch_assoc($check_student);

                    if (empty($student['first_name']) || empty($student['last_name']) ||
                        empty($student['grade_level']) || empty($student['strand']) ||
                        empty($student['contact_number']) || empty($student['address']) ||
                        empty($student['photo'])) {

                        $_SESSION['student_id'] = $student['id'];
                        header("Location: ../complete_profile.php");
                        exit();
                    }
                }

                // Otherwise go to dashboard
                header("Location: ../dashboard/student.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Incorrect password.";
        }
    } else {
        $_SESSION['error'] = "No account found with that email.";
    }

    header("Location: ../index.php");
    exit();
}
?>
