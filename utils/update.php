<?php
session_start();

require 'connect.php';


$id = $_SESSION['user_id']; 
// TODO: Add validation and sanitization

$userkeys = array('user_id' => true, 'email' => true, 'f_name' => true, 'l_name' => true, 'm_initial'=> true, 'phone' => true, 'password' => true, 'is_admin' => true);

$userUpdates = "";
$stuUpdates = "";
$userValues = [];
$stuValues = [];
foreach($_POST as $key => $value) {
    if(@$userkeys[$key]) {
        $userUpdates .= "$key = ?, ";
        array_push($userValues, $value);
    } else {
        $stuUpdates .= "$key = ?, ";
        array_push($stuValues, $value);
    }
}
$userUpdates = substr($userUpdates,0,-2);
$stuUpdates = substr($stuUpdates,0,-2);


$sql = "UPDATE users SET " . $userUpdates . " WHERE user_id = '$id';";
$sql2 = "UPDATE students SET " . $stuUpdates . " WHERE user_id = '$id'";

header("Location: ../student.php");
if ($conn->execute_query($sql, $userValues) === TRUE && $conn->execute_query($sql2, $stuValues) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}
$conn->close();
?>