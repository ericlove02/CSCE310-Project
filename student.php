<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo "You must log in first";
    exit;
}

require 'utils/connect.php';

$email = $_SESSION['email'];

$sql = "SELECT * FROM students WHERE email = '$email'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Information</title>
</head>

<body>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Program</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["name"] . "</td><td>" . $row["email"] . "</td><td>" . $row["program"] . "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No results</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>

</html>