<?php
/**
 * Main Author: Joel Herzog
 * Co-Author: Andrew Nguyen
 * Co-Author: Eric Love 
 * Co-Author: Andrew Nguyen 
 * Co-Author: Mateo Ruiz
 */

session_start();

require 'connect.php';

$id = $_SESSION['user_id'];

$userkeys = array('user_id' => true, 'email' => true, 'f_name' => true, 'l_name' => true, 'm_initial' => true, 'phone' => true, 'password' => true, 'is_admin' => true);

$userUpdates = "";
$stuUpdates = "";
$userValues = [];
$stuValues = [];
foreach ($_POST as $key => $value) {

    if ($key == "deactivate") {

        $tables = [
            'applications',
            'attendedevents',
            'programenrollments',
            'studentcerts',
            'studentinternships',
            'takencourses',
            'user_documents',
            'students',
            'users'
        ];

        try {
            $conn->begin_transaction();

            foreach ($tables as $table) {
                $sql = "DELETE FROM $table WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to delete records from $table");
                }
            }

            $conn->commit();
            echo "User records deleted successfully";
        } catch (Exception $e) {
            $conn->rollBack();
            echo "Error: " . $e->getMessage();
        }
        header("Location: logout.php");
        return;
    }

    if ($key != "submit") {
        if (@$userkeys[$key]) {
            $userUpdates .= "$key = ?, ";
            array_push($userValues, $value);
        } else {
            $stuUpdates .= "$key = ?, ";
            array_push($stuValues, $value);
        }
    }
}
$userUpdates = substr($userUpdates, 0, -2);
$stuUpdates = substr($stuUpdates, 0, -2);

$sql = "UPDATE users SET " . $userUpdates . " WHERE user_id = $id;";
$sql2 = "UPDATE students SET " . $stuUpdates . " WHERE user_id = $id";


echo $sql, "<br>";
echo $sql2;

// header("Location: ../student/info.php");
if ($conn->execute_query($sql, $userValues) === TRUE && $conn->execute_query($sql2, $stuValues) === TRUE) {
    echo "Record updated successfully";
    $_SESSION['update_success'] = true;
} else {
    $_SESSION['update_success'] = false;
    echo "Error updating record: " . $conn->error;
}

header("Location: /student/info.php");

$conn->close();
?>