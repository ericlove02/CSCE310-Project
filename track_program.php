<?php
require_once "utils/connect.php";

session_start();

// Assuming you have a session started and the user's ID is stored in $_SESSION['user_id']
$userId = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    echo "You must log in first";
    exit;
}

$id = $_SESSION['user_id'];

// Fetch courses
$sqlCourses = "SELECT c.cour_name FROM courses c JOIN takencourses tc ON c.cour_id = tc.cour_id WHERE tc.user_id = $userId";
$resultCourses = $conn->query($sqlCourses);
$courses = $resultCourses->fetch_all(MYSQLI_ASSOC);

// Fetch certificates
$sqlCertificates = "SELECT c.cert_name FROM certifications c JOIN studentcerts sc ON c.cert_id = sc.cert_id WHERE sc.user_id = $userId";
$resultCertificates = $conn->query($sqlCertificates);
$certificates = $resultCertificates->fetch_all(MYSQLI_ASSOC);

// Display courses
echo "Courses:<br>";
foreach ($courses as $course) {
    echo $course['cour_name'] . "<br>";
}

// Display certificates
echo "Certificates:<br>";
foreach ($certificates as $certificate) {
    echo $certificate['cert_name'] . "<br>";
}
?>