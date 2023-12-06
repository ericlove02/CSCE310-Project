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

$stu_row = $conn->query("SELECT * FROM students WHERE user_id = '$id'")->fetch_assoc();

function getAllRecords($conn, $tableName, $id = null, $join_table = null, $join_on = null)
{
    if ($join_table && $join_on) {
        $sql = "SELECT * FROM $tableName
                JOIN $join_table ON $tableName.$join_on = $join_table.$join_on
                WHERE $tableName.user_id = '$id'";
    } elseif ($id) {
        $sql = "SELECT * FROM $tableName WHERE user_id = '$id'";
    } else {
        $sql = "SELECT * FROM $tableName";
    }
    // echo $sql;
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $records = array();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    return $records;
}

function updateRecord($conn, $tableName, $recordId, $recordData)
{
    $updateValues = '';
    $keyValues = '';
    foreach ($recordData as $column => $value) {
        if (strpos($column, '_id') !== false) {
            $keyValues .= "$column = '$value' AND ";
        } elseif ($value != '') {
            // jank fix for joined tables
            if ($column == "new_cour") {
                $column = "cour_id";
            }
            $updateValues .= "$column = '$value', ";
        }
    }
    $updateValues = rtrim($updateValues, ', ');
    $keyValues = rtrim($keyValues, 'AND ');

    $sql = "UPDATE $tableName SET $updateValues WHERE $keyValues";
    // echo $sql;
    if ($conn->query($sql) !== TRUE) {
        echo "Error updating record: " . $conn->error;
    } else {
        echo "Entry in $tableName updated";
    }
}

function addRecord($conn, $tableName, $recordData)
{
    $columns = implode(', ', array_keys($recordData));
    $values = "'" . implode("', '", array_values($recordData)) . "'";
    $sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
    echo $sql;
    if ($conn->query($sql) !== TRUE) {
        echo "Error adding new record: " . $conn->error;
    } else {
        echo "New entry to $tableName added";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_record'])) {
        $selectedRecordId = $_POST['selected_record_id'];
        $recordData = array();

        // handle data based on selected table
        switch ($_POST['selected_table']) {
            case 'takencourses':
                $recordData['cour_id'] = $_POST['selected_record_id'];
                $recordData['new_cour'] = $_POST['new_cour'];
                $recordData['user_id'] = $id;
                $recordData['tc_semester'] = $_POST['tc_semester'];
                $recordData['tc_is_passed'] = $_POST['tc_is_passed'];
                break;
            default:
                // default case
                echo "Invalid table selected";
                break;
        }

        // if add_new its a new record
        if ($selectedRecordId == 'add_new') {
            addRecord($conn, $_POST['selected_table'], $recordData);
        } else {
            // update existing record
            updateRecord($conn, $_POST['selected_table'], $selectedRecordId, $recordData);
        }
    } elseif (isset($_POST['delete_record'])) {
        $selectedRecordId = $_POST['selected_record_id'];
        $selectedTable = $_POST['selected_table'];
        if ($selectedRecordId == 'add_new') {
            // show an alert when trying to delete 'add new'
            echo '<script>alert("Error: Cannot delete a new record.");</script>';
        } else {
            switch ($_POST['selected_table']) {
                case 'takencourses':
                    $sql = "DELETE FROM $selectedTable WHERE cour_id = $selectedRecordId AND user_id = $id";
                    break;
                default:
                    // default case
                    echo "Invalid table selected";
                    break;
            }
            if ($conn->query($sql) !== TRUE) {
                echo "Error deleting record: " . $conn->error;
            } else {
                echo "$selectedTable record deleted";
            }
        }
    }
}

// get all record from the table for the user
$takencourses = getAllRecords($conn, 'takencourses', $id, 'courses', 'cour_id');
$courses = getAllRecords($conn, 'courses');
$applications = getAllRecords($conn, 'applications', $id);
$internships = getAllRecords($conn, 'studentinternships', $id);

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

        createInput($row, "email", "Email");
        createInput($row, "f_name", "First Name");
        createInput($row, "l_name", "Last Name");
        createInput($row, "m_initial", "Middle Initial");
        createInput($row, "phone", "Phone");
        createInput($row, "password", "Password");
        // is_admin
        
        // student stuff
        createInput($stu_row, "stu_uin", "UIN"); // new
        
        createInput($stu_row, "stu_gender", "Gender");
        createCheckbox($stu_row, "stu_hisp_latino", "Hispanic?");
        createCheckbox($stu_row, "stu_uscitizen", "US Citizen?");
        createCheckbox($stu_row, "stu_firstgen", "First generation college student?");

        createInput($stu_row, "stu_dob", "Date of Birth");
        createInput($stu_row, "stu_discord", "Discord username");
        createInput($stu_row, "stu_school", "School");
        createInput($stu_row, "stu_classification", "Student classification");
        createInput($stu_row, "stu_grad_expect", "Expected graduation date");
        createInput($stu_row, "stu_major", "Student major");
        createInput($stu_row, "stu_major2", "Student major 2");
        createInput($stu_row, "stu_minor", "Student minor");
        createInput($stu_row, "stu_minor2", "Student minor 2"); // new
        
        createInput($stu_row, "stu_gpa", "Student GPA"); // new
        createCheckbox($stu_row, "stu_in_rotc", "In ROTC?"); // new 
        createCheckbox($stu_row, "stu_in_corp", "In Corps of Cadets?"); // new
        createCheckbox($stu_row, "stu_in_cyber_club", "In Cybersecurity Club?"); // new
        createCheckbox($stu_row, "stu_in_women_cyber", "In Women in Cybersecurity?"); // new
        ?>

        <!-- Add more fields as needed -->
        <input type="submit" name="submit" value="Update">

        <input type="submit" name="submit" onclick="return confirm('Are you sure you want to deactivate your account?')"
            value="Deactivate account">
    </form>

    <!-- File modification -->

    <section>
        <table border="1">
            <tr>
                <th>User Documents</th>
            </tr>

            <?php
            $result = $conn->execute_query("SELECT file_id, user_id, filename, mimetype FROM user_documents WHERE user_id = ?", [$id]);
            if (!$result) {
                die("Query failed: " . $conn->error);
            }

            $files = [];

            while ($file = $result->fetch_assoc()) {
                $files[] = $file;
            }

            foreach ($files as $file) {
                echo "<tr>";
                echo "<td><a target='_blank' href='utils/document.php?serve={$file['file_id']}'>{$file['filename']}</span></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <form action="utils/document.php?return=/student.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="fileUpload" value="1">
            <select name="selectedFileId">
                <option value="-1">Upload new file</option>
                <?php
                foreach ($files as $file) {
                    echo "<option value='{$file['file_id']}'>{$file['filename']}</option>";
                }
                ?>
            </select> <br>
            <input type="file" name="file" id="fileToUpload"> <br />
            <input type="submit" value="Upload/Replace File" name="submit">
            <input type="submit" value="Delete File" name="submit">


        </form>
    </section>

    <section>
        <h3>Courses</h3>
        <h4>All Courses</h4>
        <table border="1">
            <tr>
                <th>Course Id</th>
                <th>Course Name</th>
            </tr>

            <?php
            foreach ($courses as $course) {
                echo "<tr>";
                echo "<td><span>{$course['cour_id']}</span></td>";
                echo "<td><span>{$course['cour_name']}</span></td>";
                echo "</tr>";
            }
            ?>

        </table>
        <h4>All Courses</h4>
        <table border="1">
            <tr>
                <th>Course Id</th>
                <th>Semester</th>
                <th>Is Passed?</th>
            </tr>

            <?php
            foreach ($takencourses as $takencourse) {
                echo "<tr>";
                echo "<td><span>{$takencourse['cour_id']}</span></td>";
                echo "<td><span>{$takencourse['tc_semester']}</span></td>";
                echo "<td><span>" . ($takencourse['tc_is_passed'] ? 'Yes' : 'No') . "</span></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <br>
        <h4>Modify Your Course</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="takencourses">

            <label>Select Course:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new taken Course</option>
                <?php
                foreach ($takencourses as $takencourse) {
                    echo "<option value='{$takencourse['cour_id']}'>{$takencourse['cour_id']}</option>";
                }
                ?>
            </select>
            <br>
            <label>Course Id:</label>
            <input type="text" name="new_cour"><br>
            <label>Semester:</label>
            <input type="text" name="tc_semester"><br>
            <label for="tc_is_passed">Course Is Passed?</label>
            <select name="tc_is_passed" id="tc_is_passed">
                <option value=""></option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>

            <br>
            <button type="submit" name="update_record">Update/Add</button>
            <button type="submit" name="delete_record">Delete</button>
        </form>
    </section>

    <!-- Fetch and display the student's internships -->
    <?php
    $sql = "SELECT internships.* FROM internships 
        INNER JOIN studentinternships ON internships.intshp_id = studentinternships.intshp_id 
        WHERE studentinternships.user_id = '$id'";
    $result = $conn->query($sql);
    ?>

    
    <section>
        <h3>Internships</h3>
        <h4>All Internships</h4>
        <table border="1">
            <tr>
                <th>Internship Id</th>
                <th>Internship Name</th>
                <th>Internship Status</th>
                <!-- Add other columns as needed -->
            </tr>

            <?php
            // Replace with your actual function to fetch internships
            // $internships = getAllInternships($id); 

            foreach ($internships as $internship) {
                echo "<tr>";
                echo "<td><span>{$internship['internship_id']}</span></td>";
                echo "<td><span>{$internship['user_id']}</span></td>";
                echo "<td><span>{$internship['stin_app_status']}</span></td>";
                // Add other columns as needed
                echo "</tr>";
            }
            ?>

        </table>
        <br>
    <h4>Modify Your Internship</h4>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="selected_table" value="studentinternships">

        <label>Select Internship:</label>
        <select name="selected_record_id">
            <option value="add_new">Add new Internship</option>
            <?php
            foreach ($internships as $internship) {
                echo "<option value='{$internship['internship_id']}'>{$internship['internship_id']}</option>";
            }
            ?>
        </select>
        <br>
        <label>Internship Id:</label>
        <input type="text" name="internship_id"><br>
        <label>Internship Name:</label>
        <input type="text" name="internship_name"><br>
        <label>Internship Status:</label>
        <input type="text" name="stin_app_status"><br>
        <!-- Add other fields as needed -->
        <input type="submit" name="submit" value="Submit">
    </form>
    </section>

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
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["intshp_id"] . "</td><td>" . $row["intshp_name"] . "</td><td>" . $row["intshp_year"] . "</td></tr>";
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
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["app_id"] . "</td><td>" . $row["app_date_applied"] . "</td><td>" . $row["app_purpose"] . "</td><td>" . $row["app_resume"] . "</td><td>" . $row["app_type"] . "</td>";
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