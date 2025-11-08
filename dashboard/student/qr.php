<?php
session_start();
require_once("../../includes/db_connect.php");

// Ensure logged-in student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo "Forbidden";
    exit();
}

$email = $_SESSION['email'];

// Fetch student info by email
$stmt = $conn->prepare("SELECT id, student_id, first_name, last_name, email FROM student WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    http_response_code(404);
    echo "Student not found";
    exit();
}

// Build QR payload (concise, safe)
$payload = sprintf(
    "SID:%s|NAME:%s %s|EMAIL:%s",
    $student['student_id'] ?? $student['id'],
    $student['first_name'],
    $student['last_name'],
    $student['email']
);

// choose Google Chart QR endpoint (simple, portable)
// chld parameter controls error-correction|margin
$qr_url = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($payload) . "&chld=L|1";

// If ?download=1 -> force download as image
if (isset($_GET['download']) && ($_GET['download'] == '1' || $_GET['download'] === 'true')) {
    // Fetch image data and output as attachment
    $img = @file_get_contents($qr_url);
    if ($img === false) {
        http_response_code(502);
        echo "Unable to generate QR at the moment.";
        exit();
    }
    header('Content-Type: image/png');
    $fname = 'qr_' . ($student['student_id'] ?? $student['id']) . '.png';
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    echo $img;
    exit();
}

// Otherwise display image inline (proxy)
$img = @file_get_contents($qr_url);
if ($img === false) {
    http_response_code(502);
    echo "Unable to generate QR at the moment.";
    exit();
}
header('Content-Type: image/png');
echo $img;
exit();
