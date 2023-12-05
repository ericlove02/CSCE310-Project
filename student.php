<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must log in first";
    exit;
}

require 'utils/connect.php';

$id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE user_id = '$id'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Information</title>
</head>

<body>
    <form method="post" action="utils/update.php">
        <label for="f_name">First Name:</label><br>
        <input type="text" id="f_name" name="f_name" value="<?php echo $row['f_name']; ?>"><br>

        <label for="l_name">Last Name:</label><br>
        <input type="text" id="l_name" name="l_name" value="<?php echo $row['l_name']; ?>"><br>

        <label for="email">Email:</label><br>
        <input type="text" id="email" name="email" value="<?php echo $row['email']; ?>"><br>

        
        <!-- Add more fields as needed -->
        <input type="submit" value="Save">
    </form>

<!-- Fetch and display the student's taken courses -->
<?php
$sql = "SELECT courses.cour_id, courses.cour_name FROM takencourses 
        INNER JOIN courses ON takencourses.cour_id = courses.cour_id 
        WHERE takencourses.user_id = '$id'";
$result = $conn->query($sql);
?>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    th {
        background-color: #500000;
        color: white;
    }
</style>

<table>
    <tr>
        <th>Course ID</th>
        <th>Course Name</th>
        <!-- Add more headers as needed -->
    </tr>
    <?php
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["cour_id"]. "</td><td>" . $row["cour_name"]. "</td></tr>";
            // Add more columns as needed
        }
    } else {
        echo "<tr><td colspan='2'>No results</td></tr>";
    }
    ?>
</table>

    

<!-- Fetch and display the student's internships -->
<?php
$sql = "SELECT internships.* FROM internships 
        INNER JOIN studentinternships ON internships.intshp_id = studentinternships.intshp_id 
        WHERE studentinternships.user_id = '$id'";
$result = $conn->query($sql);
?>


<table>
    <tr>
        <th>Internship ID</th>
        <th>Internship Name</th>
        <th>Internship Year</th>
        <!-- Add more headers as needed -->
    </tr>
    <?php
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["intshp_id"]. "</td><td>" . $row["intshp_name"]. "</td><td>" . $row["intshp_year"]. "</td></tr>";
            // Add more columns as needed
        }
    } else {
        echo "<tr><td colspan='2'>No results</td></tr>";
    }
    ?>
</table>

    <!-- Repeat the above for internships, events, trainings, and certifications -->
</body>

</html>