<?php
session_start();

require 'utils/connect.php';

$id = $_SESSION['user_id'];
$f_name = $_POST['f_name'];
$l_name = $_POST['l_name'];
$email = $_POST['email'];

// TODO: Add validation and sanitization

$sql = "UPDATE users SET f_name = '$f_name', l_name = '$l_name', email = '$email' WHERE user_id = '$id'";

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>