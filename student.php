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

$stu_row =  $conn->query("SELECT * FROM students WHERE user_id = '$id'")->fetch_assoc();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Information</title>
</head>

<body>
    <h2>Student Information</h2>
    <form method="post" action="utils/update.php">
        <?php

        
function createInput($row, $key, $label)
{
    $val = $row[$key];
    echo "${label}: <input type=\"text\" name=\"${key}\" value=\"${val}\"><br>";
}

function createCheckbox($row, $key, $label)
{
    $val = $row[$key] == 1 ? 'checked' : '';
    echo "${label}:   <input type='hidden' value='0' name='${key}'>    <input type=\"checkbox\" name=\"${key}\" value='1' {$val}><br>";
}

        createInput($row,"email", "Email");
        createInput($row,"f_name", "First Name");
        createInput($row,"l_name", "Last Name");
        createInput($row,"m_initial", "Middle Initial");
        createInput($row,"phone", "Phone");
        createInput($row,"password", "Password");
        // is_admin
        
        // student stuff
        createInput($stu_row,"stu_uin", "UIN"); // new

        createInput($stu_row,"stu_gender", "Gender");
        createCheckbox($stu_row,"stu_hisp_latino", "Hispanic?");
        createCheckbox($stu_row,"stu_uscitizen", "US Citizen?");
        createCheckbox($stu_row,"stu_firstgen", "First generation college student?");

        createInput($stu_row,"stu_dob", "Date of Birth");
        createInput($stu_row,"stu_discord", "Discord username");
        createInput($stu_row,"stu_school", "School");
        createInput($stu_row,"stu_classification", "Student classification");
        createInput($stu_row,"stu_grad_expect", "Expected graduation date");
        createInput($stu_row,"stu_major", "Student major");
        createInput($stu_row,"stu_major2", "Student major 2");
        createInput($stu_row,"stu_minor", "Student minor");
        createInput($stu_row,"stu_minor2", "Student minor 2"); // new

        createInput($stu_row,"stu_gpa", "Student GPA"); // new
        createCheckbox($stu_row,"stu_in_rotc", "In ROTC?"); // new 
        createCheckbox($stu_row,"stu_in_corp", "In Corps of Cadets?"); // new
        createCheckbox($stu_row,"stu_in_cyber_club", "In Cybersecurity Club?"); // new
        createCheckbox($stu_row,"stu_in_women_cyber", "In Women in Cybersecurity?"); // new
        ?>
        
        <!-- Add more fields as needed -->
        <input type="submit" value="Update">
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

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['insert'])) {
            // Add your insert logic here
            // $sql = "INSERT INTO applications (app_date_applied, app_purpose, app_resume, app_type) VALUES (?, ?, ?, ?)";
            $sql = "INSERT INTO applications (app_date_applied, app_purpose, app_resume, app_type) VALUES ('" . $_POST['app_date_applied'] . "', '" . $_POST['app_purpose'] . "', '" . $_POST['app_resume'] . "', '" . $_POST['app_type'] . "')";
        } elseif (isset($_POST['update'])) {
            $app_id = $_POST['app_id'];
            // Add your update logic here
            // $sql = "UPDATE applications SET ... WHERE app_id = $app_id";
        } elseif (isset($_POST['delete'])) {
            $app_id = $_POST['app_id'];
            // Add your delete logic here
            // $sql = "DELETE FROM applications WHERE app_id = $app_id";
        }
    }
    //bruh
    $sql = "SELECT * FROM applications WHERE user_id = '$id'";
    $result = $conn->query($sql);

    echo "<table>";
    echo "<tr><th>Application ID</th><th>Date Applied</th><th>Purpose Statement</th><th>Resume</th><th>Type</th><th>Actions</th></tr>";

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["app_id"]. "</td><td>" . $row["app_date_applied"]. "</td><td>" . $row["app_purpose"]. "</td><td>" . $row["app_resume"]. "</td><td>" . $row["app_type"]. "</td>";
            echo "<td>
                    <form method='post'>
                        <input type='hidden' name='app_id' value='" . $row["app_id"] . "'>
                        <input type='submit' name='update' value='Update'>
                        <input type='submit' name='delete' value='Delete'>
                    </form>
                </td></tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No results</td></tr>";
    }

    echo "</table>";
    ?>

    <!-- Insert Application Form -->
    <form method="post">
        <label for="app_date_applied">Date Applied:</label><br>
        <input type="date" id="app_date_applied" name="app_date_applied"><br>
        <label for="app_purpose">Purpose Statement:</label><br>
        <input type="text" id="app_purpose" name="app_purpose"><br>
        <label for="app_resume">Resume:</label><br>
        <input type="text" id="app_resume" name="app_resume"><br>
        <label for="app_type">Type:</label><br>
        <input type="text" id="app_type" name="app_type"><br>
        <input type="submit" name="insert" value="Insert">
    </form>
</body>


</html>
