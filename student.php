<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must log in first";
    exit;
}

if (isset($_SESSION['admin_id'])) {
    echo '<a href="admin.php" class="btn btn-danger">Return to Admin</a>';
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
            $column = fixJoinTableVariables($column);
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
    $fixedRecordData = array();
    foreach ($recordData as $columnName => $value) {
        $fixedColumnName = fixJoinTableVariables($columnName);
        $fixedRecordData[$fixedColumnName] = $value;
    }
    $columns = implode(', ', array_keys($fixedRecordData));
    $filteredValues = array_filter(array_values($recordData), function ($value) {
        return $value !== "add_new";
    });

    $values = "'" . implode("', '", $filteredValues) . "'";
    $sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
    // echo $sql;
    if ($conn->query($sql) !== TRUE) {
        echo "Error adding new record: " . $conn->error;
    } else {
        echo "New entry to $tableName added";
    }
}

function fixJoinTableVariables($columnName)
{
    // jank fix for joined tables
    if ($columnName == "new_cour") {
        return "cour_id";
    } elseif ($columnName == "new_intshp") {
        return "intshp_id";
    } else {
        return $columnName;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_record'])) {
        $selectedRecordId = $_POST['selected_record_id'] != null ? $_POST['selected_record_id'] : "add_new";
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
            case 'studentinternships':
                $recordData['intshp_id'] = $_POST['selected_record_id'];
                $recordData['new_intshp'] = $_POST['new_intshp'];
                $recordData['user_id'] = $id;
                $recordData['stin_app_status'] = $_POST['app_status'];
                break;
            case 'internships':
                $recordData['intshp_name'] = $_POST['intshp_name'];
                $recordData['intshp_year'] = $_POST['intshp_year'];
                $recordData['intshp_state'] = $_POST['intshp_state'];
                $recordData['intshp_country'] = $_POST['intshp_country'];
                $recordData['intshp_is_federal'] = $_POST['intshp_is_federal'];
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
                case 'studentinternships':
                    $sql = "DELETE FROM $selectedTable WHERE intshp_id = $selectedRecordId AND user_id = $id";
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
$studentinternships = getAllRecords($conn, 'studentinternships', $id, 'internships', 'intshp_id');
$internships = getAllRecords($conn, 'internships');

?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Information</title>
    <link rel="stylesheet" href="/bootstrap-5.0.2-dist/css/bootstrap.min.css">
</head>

<body>
    <?php
    // check if admin_id is not set to show the logout button
    if (!isset($_SESSION['admin_id'])) {
        echo '<a href="utils/logout.php" class="btn btn-danger">Logout</a>';
    }
    ?>
    <h2>Student Information</h2>
    <form method="post" action="utils/update.php">
        <?php


        function createInput($row, $key, $label, $bootstrapClass = "")
        {
            $val = $row[$key];
            echo "<div class=\"form-group ${bootstrapClass}\">${label}: <input type=\"text\" class=\"form-control\" name=\"${key}\" value=\"${val}\"></div>";
        }

        function createCheckbox($row, $key, $label)
        {
            $val = $row[$key] == 1 ? 'checked' : '';
            echo "${label}:   <input type='hidden' value='0' name='${key}'>    <input type=\"checkbox\" name=\"${key}\" value='1' {$val}><br>";
        }
        ?>
        <span>UIN:
            <?php echo $id ?>
        </span><br>
        <div class="form-row row">
            <?php
            createInput($row, "email", "Email", "col-md-4");
            createInput($row, "phone", "Phone", "col-md-4");
            createInput($row, "password", "Password", "col-md-4");
            ?>
        </div>
        <div class="form-row row">
            <?php
            createInput($row, "f_name", "First Name", "col-md-4");
            createInput($row, "m_initial", "Middle Initial", "col-md-2");
            createInput($row, "l_name", "Last Name", "col-md-6"); ?>
        </div>
        <?php

        // is_admin
        
        // Don't try to show show student info if they are not a student
        if ($stu_row != true)
            die("<br><b>Not a student<b><br>");

        // student stuff
        // createInput($stu_row, "stu_uin", "UIN"); // new
        ?>
        <div class="form-row row">
            <?php
            createInput($stu_row, "stu_gender", "Gender", "col-md-4");
            createInput($stu_row, "stu_dob", "Date of Birth", "col-md-4");
            ?>
        </div>
        <?php
        createCheckbox($stu_row, "stu_hisp_latino", "Hispanic?");
        createCheckbox($stu_row, "stu_uscitizen", "US Citizen?");
        createCheckbox($stu_row, "stu_firstgen", "First generation college student?");
        ?>
        <div class="form-row row">
            <?php
            createInput($stu_row, "stu_discord", "Discord username", "col-md-3");
            createInput($stu_row, "stu_school", "School", "col-md-4");
            createInput($stu_row, "stu_classification", "Student classification", "col-md-3");
            createInput($stu_row, "stu_grad_expect", "Expected graduation date", "col-md-2");
            ?>
        </div>
        <div class="form-row row">
            <?php
            createInput($stu_row, "stu_major", "Student major", "col-md-3");
            createInput($stu_row, "stu_major2", "Student major 2", "col-md-3");
            createInput($stu_row, "stu_minor", "Student minor", "col-md-3");
            createInput($stu_row, "stu_minor2", "Student minor 2", "col-md-3"); // new
            ?>
        </div>
        <?php
        createInput($stu_row, "stu_gpa", "Student GPA", "col-md-2"); // new
        createCheckbox($stu_row, "stu_in_rotc", "In ROTC?"); // new
        createCheckbox($stu_row, "stu_in_corp", "In Corps of Cadets?"); // new
        createCheckbox($stu_row, "stu_in_cyber_club", "In Cybersecurity Club?"); // new
        createCheckbox($stu_row, "stu_in_women_cyber", "In Women in Cybersecurity?"); // new
        ?>

        <button type="submit" name="submit" class="btn btn-dark">Update
        </button>
        <button type="submit" name="submit" class="btn btn-dark"
            onclick="return confirm('Are you sure you want to deactivate your account?')">
            Deactivate Account</button>
    </form>

    <!-- File modification -->
    <br>
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
            <input type="submit" value="Upload/Replace File" name="submit" class="btn btn-dark">
            <input type="submit" value="Delete File" name="submit" class="btn btn-dark">


        </form>
    </section>
    <hr />
    <section>
        <h3>My Programs & Applications</h3>
        <?php


        $id = $_SESSION['user_id'];

        // get user
        $sql = "SELECT * FROM users WHERE user_id = '$id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        // get enrollment status 
        $sql = "
SELECT * FROM programs
WHERE programs.prog_id IN ( 
    SELECT programenrollments.prog_id 
    FROM programenrollments 
    WHERE programenrollments.user_id = ${id} 
);";

        $result = $conn->query($sql);
        $enrolled_programs = $result->fetch_all();

        // get pending programs (application but not enrolled)
        $sql = "
SELECT * FROM programs
WHERE programs.prog_id NOT IN( 
    SELECT programenrollments.prog_id 
    FROM programenrollments 
    WHERE programenrollments.user_id = ${id} 
) AND programs.prog_id IN (
    SELECT applications.prog_id
    FROM applications
    WHERE applications.user_id = ${id}
);";
        $result = $conn->query($sql);
        $pending_programs = $result->fetch_all();

        // get available programs
        $sql = "
SELECT * FROM programs
WHERE programs.prog_id NOT IN( 
    SELECT programenrollments.prog_id 
    FROM programenrollments 
    WHERE programenrollments.user_id = ${id} 
) AND programs.prog_id NOT IN(
    SELECT applications.prog_id
    FROM applications
    WHERE applications.user_id = ${id} 
);";
        $result = $conn->query($sql);
        $available_programs = $result->fetch_all();

        $conn->close();
        ?>


        <?php
        function generateProgramTable($title, $programs)
        {
            $action_label = '';
            $action_url = '';

            if ($title == 'Enrolled Programs') {
                $action_label = 'View Progress';
                $action_url = 'track_program.php';
            } else if ($title == 'Pending Programs') {
                $action_label = 'View Applications';
                $action_url = 'application.php';
            } else if ($title == 'Available Programs') {
                $action_label = 'Apply';
                $action_url = 'application.php';
            }

            echo "<h3>$title</h2>";
            echo "<table>";
            echo "<tr>";
            echo "<th>Program ID</th>";
            echo "<th>Program Name</th>";
            echo "<th>Actions</th>";
            echo "</tr>";

            foreach ($programs as $program) {
                echo "<tr>";
                echo "<td>" . $program[0] . "</td>";
                echo "<td>" . $program[1] . "</td>";
                echo "<td>";
                echo "<a href='" . $action_url . "?id=" . $program[0] . "'><button class='btn btn-dark'>" . $action_label . "</button></a>";
                echo "</td>";
                echo "</tr>";
            }

            if (count($programs) == 0) {
                echo "<tr><td colspan='3'>No results</td></tr>";
            }

            echo "</table>";
        }

        generateProgramTable("Enrolled Programs", $enrolled_programs);
        generateProgramTable("Pending Programs", $pending_programs);
        generateProgramTable("Available Programs", $available_programs);
        ?>
    </section>
    <hr />
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
        <h4>Your Courses</h4>
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
            <button type="submit" name="update_record" class="btn btn-dark">Update/Add</button>
            <button type="submit" name="delete_record" class="btn btn-dark">Delete</button>
        </form>
    </section>

    <!-- Fetch and display the student's internships -->
    <hr />
    <section>
        <h3>Internships</h3>
        <h4>All Internships</h4>
        <table border="1">
            <tr>
                <th>Internship Id</th>
                <th>Internship Name</th>
                <th>Internship Year</th>
                <th>Internship State</th>
                <th>Internship Country</th>
                <th>Internship Is Federal?</th>
            </tr>

            <?php
            foreach ($internships as $internship) {
                echo "<tr>";
                echo "<td><span>{$internship['intshp_id']}</span></td>";
                echo "<td><span>{$internship['intshp_name']}</span></td>";
                echo "<td><span>{$internship['intshp_year']}</span></td>";
                echo "<td><span>{$internship['intshp_state']}</span></td>";
                echo "<td><span>{$internship['intshp_country']}</span></td>";
                echo "<td><span>" . ($internship['intshp_is_federal'] ? 'Yes' : 'No') . "</span></td>";
                echo "</tr>";
            }
            ?>

        </table>
        <br>
        <h4>Add an Internship</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="internships">
            <input type="hidden" name="selected_record_id" value="add_new">
            <label>Internship Name:</label>
            <input type="text" name="intshp_name"><br>
            <label>Internship Year:</label>
            <input type="text" name="intshp_year"><br>
            <label>Internship State:</label>
            <input type="text" name="intshp_state"><br>
            <label>Internship Country:</label>
            <input type="text" name="intshp_country"><br>
            <label>Internship Is Federal?:</label>
            <select name="intshp_is_federal" id="intshp_is_federal">
                <option value=""></option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>

            <br>
            <button type="submit" name="update_record" class="btn btn-dark">Add</button>
        </form>
        <h4>Your Internships</h4>
        <table border="1">
            <tr>
                <th>Internship Id</th>
                <th>Internship Application Status</th>
            </tr>

            <?php
            foreach ($studentinternships as $studentinternship) {
                echo "<tr>";
                echo "<td><span>{$studentinternship['intshp_id']}</span></td>";
                echo "<td><span>{$studentinternship['stin_app_status']}</span></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <br>
        <h4>Modify Your Internships</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="studentinternships">

            <label>Select Internship:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new Student Internship</option>
                <?php
                foreach ($studentinternships as $studentinternship) {
                    echo "<option value='{$studentinternship['intshp_id']}'>{$studentinternship['intshp_id']}</option>";
                }
                ?>
            </select>
            <br>
            <label>Internship Id:</label>
            <input type="text" name="new_intshp"><br>
            <label>Application Status:</label>
            <input type="text" name="app_status"><br>

            <br>
            <button type="submit" name="update_record" class="btn btn-dark">Update/Add</button>
            <button type="submit" name="delete_record" class="btn btn-dark">Delete</button>
        </form>
    </section>

</body>


</html>