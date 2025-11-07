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
    $token = bin2hex(random_bytes(32)); // email verification token

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Email already registered.";
        header("Location: ../register.php");
        exit();
    }

    $insert = mysqli_query($conn, "INSERT INTO users (full_name, email, password_hash, verification_token, role, is_verified)
                                   VALUES ('$full_name', '$email', '$password', '$token', 'student', 0)");
    if ($insert) {
        $_SESSION['success'] = "Account created! Please verify your email before logging in.";
        // TODO: send email with token link
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

        if (!$user['is_verified']) {
            $_SESSION['error'] = "Please verify your email first.";
            header("Location: ../index.php");
            exit();
        }

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            // redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: ../dashboard/admin.php");
            } elseif ($user['role'] == 'teacher') {
                header("Location: ../dashboard/teacher.php");
            } else {
                header("Location: ../dashboard/student.php");
            }
            exit();
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
