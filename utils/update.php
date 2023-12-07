<?php
session_start();

require 'connect.php';

$id = $_SESSION['user_id'];

if (@$_POST['submit'] == 'Deactivate account') {
    $sql = "DELETE FROM users WHERE user_id = $id";
    $sql2 = "DELETE FROM students WHERE user_id = $id";
    if ($conn->execute_query($sql) && $conn->execute_query($sql2)) {
        echo "Account deactivated successfully";
    } else {
        echo "Failed to deactivate account";
    }
    session_abort();
    header("Location: /index.php");

    return;
}

$userkeys = array('user_id' => true, 'email' => true, 'f_name' => true, 'l_name' => true, 'm_initial' => true, 'phone' => true, 'password' => true, 'is_admin' => true);

$userUpdates = "";
$stuUpdates = "";
$userValues = [];
$stuValues = [];
foreach ($_POST as $key => $value) {
    if ($key == "submit")
        continue;
    if (@$userkeys[$key]) {
        $userUpdates .= "$key = ?, ";
        array_push($userValues, $value);
    } else {
        $stuUpdates .= "$key = ?, ";
        array_push($stuValues, $value);
    }
}
$userUpdates = substr($userUpdates, 0, -2);
$stuUpdates = substr($stuUpdates, 0, -2);


$sql = "UPDATE users SET " . $userUpdates . " WHERE user_id = $id;";
$sql2 = "UPDATE students SET " . $stuUpdates . " WHERE user_id = $id";

header("Location: ../student/info.php");
if ($conn->execute_query($sql, $userValues) === TRUE && $conn->execute_query($sql2, $stuValues) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>