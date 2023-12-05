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
    echo "${label}: <input type=\"checkbox\" name=\"${key}\" ${val}><br>";
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