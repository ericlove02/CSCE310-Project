<?php
require '../utils/connect.php';
require '../utils/middleware.php';

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

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">Student Page</span>

            <!-- Navbar links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="info.php">Information</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="internships.php">Internships</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="programs.php">Programs</a>
                    </li>
                </ul>
            </div>

            <!-- Logout button on right side -->
            <div class="navbar-nav ms-auto">
                <?php
                // check if admin_id is not set to show the logout button
                if (isset($_SESSION['admin_id'])) {
                    echo '<a href="../admin/users.php" class="btn btn-danger">Return to Admin</a>';
                } else {
                    echo '<a href="../utils/logout.php" class="btn btn-danger">Logout</a>';
                }
                ?>
            </div>
        </div>
    </nav>

    <?php
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "You must log in first";
        exit;
    } ?>
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

</body>


</html>