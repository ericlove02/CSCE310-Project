<?php
require_once "utils/connect.php";

if (@$_POST['doChangeUser']) {
    session_start();
    $_SESSION['user_id'] = $_POST['user_id'];
    header("Location: student.php");
    return;
}

function getTableRowCount($conn, $tableName)
{
    $sql = "SELECT COUNT(*) AS count FROM $tableName";
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    return $row['count'];
}

function getAllRecords($conn, $tableName)
{
    $sql = "SELECT * FROM $tableName";
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
    foreach ($recordData as $column => $value) {
        if (strpos($column, '_id') !== false) {
            $id_key = $column;
            continue;
        }
        if ($value != '') {
            $updateValues .= "$column = '$value', ";
        }
    }
    $updateValues = rtrim($updateValues, ', ');

    $sql = "UPDATE $tableName SET $updateValues WHERE $id_key=$recordId";
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

function generateReport($conn, $selectedReport)
{
    switch ($selectedReport) {
        case 'report_cldp_stus':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS enrolled_students,
                        COUNT(DISTINCT CASE WHEN pe.pe_enroll_pending = true THEN s.user_id END) AS pending_enrollments
                    FROM
                        students s
                    JOIN
                        programenrollments pe ON s.user_id = pe.user_id
                    JOIN
                        programs p ON pe.prog_id = p.prog_id
                    WHERE
                        p.prog_name = 'CLDP'";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        case 'report_viceroy_stus':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS enrolled_students,
                        COUNT(DISTINCT CASE WHEN pe.pe_enroll_pending = true THEN s.user_id END) AS pending_enrollments
                    FROM
                        students s
                    JOIN
                        programenrollments pe ON s.user_id = pe.user_id
                    JOIN
                        programs p ON pe.prog_id = p.prog_id
                    WHERE
                        p.prog_name = 'VICEROY'";
        case 'report_pathways_stus':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS enrolled_students,
                        COUNT(DISTINCT CASE WHEN pe.pe_enroll_pending = true THEN s.user_id END) AS pending_enrollments
                    FROM
                        students s
                    JOIN
                        programenrollments pe ON s.user_id = pe.user_id
                    JOIN
                        programs p ON pe.prog_id = p.prog_id
                    WHERE
                        p.prog_name = 'Pathways'";
        case 'report_cybercorps_stus':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS enrolled_students,
                        COUNT(DISTINCT CASE WHEN pe.pe_enroll_pending = true THEN s.user_id END) AS pending_enrollments
                    FROM
                        students s
                    JOIN
                        programenrollments pe ON s.user_id = pe.user_id
                    JOIN
                        programs p ON pe.prog_id = p.prog_id
                    WHERE
                        p.prog_name = 'CyberCorps: Scholarship for Service'";
        case 'report_dod_stus':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS enrolled_students,
                        COUNT(DISTINCT CASE WHEN pe.pe_enroll_pending = true THEN s.user_id END) AS pending_enrollments
                    FROM
                        students s
                    JOIN
                        programenrollments pe ON s.user_id = pe.user_id
                    JOIN
                        programs p ON pe.prog_id = p.prog_id
                    WHERE
                        p.prog_name = 'DoD Cybersecurity Scholarship'";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        case 'report_complete_all':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS students
                    FROM
                        students s
                    WHERE
                        (SELECT COUNT(*) FROM takencourses tc WHERE tc.user_id = s.user_id) = (SELECT COUNT(*) FROM courses)
                        AND
                        (SELECT COUNT(*) FROM studentcerts sc WHERE sc.user_id = s.user_id) = (SELECT COUNT(*) FROM certifications)";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        //NOTE: NEED to add a race col for students
        case 'report_minority':
            $sql = "SELECT
                COUNT(DISTINCT s.user_id) AS students
            FROM
                students s
            JOIN
                studentraces sr ON s.user_id = sr.user_id
            JOIN
                races r ON sr.race_id = r.race_id
            WHERE
                r.race_name != 'White'";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
        case 'report_fed_interns':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS students
                    FROM
                        students s
                    JOIN
                        studentinternships si ON s.user_id = si.user_id
                    JOIN
                        internships i ON si.intshp_id = i.intshp_id
                    WHERE
                        i.intshp_is_federal = '1'";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        case 'report_majors':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS students,
                        s.stu_major
                    FROM
                        students s
                    GROUP BY
                        s.stu_major";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        case 'report_intern_locs':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS students,
                        i.intshp_state,
                        i.intshp_year
                    FROM
                        students s
                    JOIN
                        studentinternships si ON s.user_id = si.user_id
                    JOIN
                        internships i ON si.intshp_id = i.intshp_id
                    GROUP BY
                        i.intshp_state, i.intshp_year";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        default:
            return "Invalid report selected";
    }
}

// check if page was psoted to
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_app'])) {
        $selectedRecordId = $_POST['selected_record_id'];
        $isApproved = $_POST['is_approved'];

        // get application details based on app_id
        $sqlSelect = "SELECT * FROM applications WHERE app_id = $selectedRecordId";
        $resultSelect = $conn->query($sqlSelect);

        if ($resultSelect) {
            $applicationDetails = $resultSelect->fetch_assoc();

            if ($isApproved) {
                // insert into programenrollments
                $user_id = $applicationDetails['user_id'];
                $prog_id = $applicationDetails['prog_id'];

                $sqlInsert = "INSERT INTO programenrollments (user_id, prog_id) VALUES ($user_id, $prog_id)";
                $resultInsert = $conn->query($sqlInsert);

                if (!$resultInsert) {
                    die("Insert into programenrollments failed: " . $conn->error);
                }
            }

            // delete the applciations either way
            $sqlDelete = "DELETE FROM applications WHERE app_id = $selectedRecordId";
            $resultDelete = $conn->query($sqlDelete);

            if (!$resultDelete) {
                die("Delete from applications failed: " . $conn->error);
            }

            echo "Application updated successfully.";
        } else {
            die("Select application details failed: " . $conn->error);
        }
    }
    if (isset($_POST['generate_report'])) {
        $selectedReport = $_POST['selected_report'];
        $reportData = generateReport($conn, $selectedReport);
    }
    if (isset($_POST['update_record'])) {
        $selectedRecordId = $_POST['selected_record_id'];
        $recordData = array();

        // handle data based on selected table
        switch ($_POST['selected_table']) {
            case 'users':
                $recordData['user_id'] = $_POST['selected_record_id'];
                $recordData['f_name'] = $_POST['first_name'];
                $recordData['l_name'] = $_POST['last_name'];
                $recordData['m_initial'] = $_POST['m_initial'];
                $recordData['phone'] = $_POST['phone'];
                $recordData['password'] = $_POST['password'];
                $recordData['is_admin'] = $_POST['is_admin'];
                break;
            case 'events':
                $recordData['event_id'] = $_POST['selected_record_id'];
                $recordData['event_name'] = $_POST['record_name'];
                $recordData['event_location'] = $_POST['record_loc'];
                break;
            case 'trainings':
                $recordData['train_id'] = $_POST['selected_record_id'];
                $recordData['train_name'] = $_POST['record_name'];
                break;
            case 'certifications':
                $recordData['cert_id'] = $_POST['selected_record_id'];
                $recordData['cert_name'] = $_POST['record_name'];
                break;
            case 'programs':
                $recordData['prog_id'] = $_POST['selected_record_id'];
                $recordData['prog_name'] = $_POST['record_name'];
                break;
            case 'courses':
                $recordData['cour_id'] = $_POST['selected_record_id'];
                $recordData['cour_name'] = $_POST['record_name'];
                break;

            case 'summercamps':
                $recordData['camp_id'] = $_POST['selected_record_id'];
                $recordData['sc_name'] = $_POST['record_name'];
                $recordData['sc_year'] = $_POST['record_year'];
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
                case 'users':
                    $sql = "DELETE FROM $selectedTable WHERE user_id = $selectedRecordId";
                    break;
                case 'events':
                    $sql = "DELETE FROM $selectedTable WHERE event_id = $selectedRecordId";
                    break;
                case 'trainings':
                    $sql = "DELETE FROM $selectedTable WHERE train_id = $selectedRecordId";
                    break;
                case 'certifications':
                    $sql = "DELETE FROM $selectedTable WHERE cert_id = $selectedRecordId";
                    break;
                case 'programs':
                    $sql = "DELETE FROM $selectedTable WHERE prog_id = $selectedRecordId";
                    break;
                case 'courses':
                    $sql = "DELETE FROM $selectedTable WHERE cour_id = $selectedRecordId";
                    break;
                case 'summercamps':
                    $sql = "DELETE FROM $selectedTable WHERE camp_id = $selectedRecordId";
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

// get all records from each table
$events = getAllRecords($conn, 'events');
$trainings = getAllRecords($conn, 'trainings');
$certifications = getAllRecords($conn, 'certifications');
$programs = getAllRecords($conn, 'programs');
// $summercamps = getAllRecords($conn, 'summercamps');
$courses = getAllRecords($conn, 'courses');
$users = getAllRecords($conn, 'users');

$sql = "SELECT applications.*, users.*, programs.* 
            FROM applications 
            JOIN users ON applications.user_id = users.user_id 
            JOIN programs ON applications.prog_id = programs.prog_id";
$result = $conn->query($sql);
$applications = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="/bootstrap-5.0.2-dist/css/bootstrap.min.css">
</head>

<body>
    <h1>Admin Page</h1>
    <section>
        <h3>Stats</h3>
        <ul>
            <?php
            // list of tables to get stats
            $tables = array(
                "students",
                "events",
                "attendedevents",
                "trainings",
                "studenttrainings",
                "certifications",
                "studentcerts",
                "programs",
                "programenrollments",
                // "summercamps",
                "internships",
                "studentinternships",
                "courses",
                "takencourses",
                "applications"
            );

            // loop through list
            foreach ($tables as $table) {
                $rowCount = getTableRowCount($conn, $table);
                $tableName = array(
                    "attendedevents" => "attended events",
                    "studenttrainings" => "student trainings",
                    "studentcerts" => "student certifications",
                    "programenrollments" => "program enrollments",
                    "takencourses" => "taken courses",
                    "studentinternships" => "student internships"
                )[$table] ?? $table;

                echo "<li>Total number of $tableName: $rowCount</li>";
            }
            ?>
        </ul>

        <h3>Reports</h3>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label>Select Report:</label>
            <select name="selected_report">
                <option value="report_cldp_stus">Number of total Cyber Leader Development Program students</option>
                <option value="report_viceroy_stus">Number of total VICEROY students</option>
                <option value="report_pathways_stus">Number of total Pathways students</option>
                <option value="report_cybercorps_stus">Number of total CyberCorps: Scholarship for Service students
                </option>
                <option value="report_dod_stus">Number of total DoD Cybersecurity Scholarship Program students</option>
                <option value="report_complete_all">Number of students to complete all course and certification
                    opportunities</option>
                <option value="report_strat_foreign">Number of students electing to take additional strategic foreign
                    language courses</option>
                <option value="report_crypto">Number of students electing to take other cryptography and cryptographic
                    mathematics courses</option>
                <option value="report_data_sci">Number of students electing to carry additional data science and related
                    courses</option>
                <option value="report_enroll_dod_cour">Number of students to enroll in DoD 8570.01M preparation training
                    courses</option>
                <option value="report_complete_dod_cour">Number of students to complete DoD 8570.01M preparation
                    training courses</option>
                <option value="report_enroll_dod_exam">Number of students to complete a DoD 8570.01M certification
                    examination</option>
                <option value="report_minority">Minority participation</option>
                <option value="report_k_12_sc">Number of K-12 students enrolled in summer camps</option>
                <option value="report_fed_interns">Number of students pursuing federal internships</option>
                <option value="report_majors">Student majors</option>
                <option value="report_intern_locs">Student internship locations</option>
            </select>
            <button type="submit" name="generate_report" class="btn btn-dark">Generate</button>
        </form>

        <?php
        // display generate report content
        if (isset($reportData)) {
            echo "<h4>Generated Report</h4>";
            if ($reportData == null) {
                echo "<p>No data to display</p>";
            } elseif (is_array($reportData)) {
                echo "<table border='1'>";
                echo "<tr>";
                foreach ($reportData[0] as $column => $value) {
                    $formattedColumnName = ucwords(str_replace('_', ' ', $column));
                    echo "<th>{$formattedColumnName}</th>";
                }
                echo "</tr>";
                foreach ($reportData as $row) {
                    echo "<tr>";
                    foreach ($row as $column => $value) {
                        echo "<td>{$value}</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                // if just one item show just the piece of data
                echo "<p>{$reportData}</p>";
            }
        }
        ?>

    </section>
    <hr />
    <section>
        <h3>User Authentication and Roles</h3>
        <table border="1">
            <tr>
                <th>User Id</th>
                <th>Email</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Middle Initial</th>
                <th>Phone</th>
                <th>Password</th>
                <th>Is Admin</th>
            </tr>

            <?php

            foreach ($users as $user) {
                echo "<tr>";
                echo "<td><span>{$user['user_id']}</span></td>";
                echo "<td><span>{$user['email']}</span></td>";
                echo "<td><span>{$user['f_name']}</span></td>";
                echo "<td><span>{$user['l_name']}</span></td>";
                echo "<td><span>{$user['m_initial']}</span></td>";
                echo "<td><span>{$user['phone']}</span></td>";
                echo "<td><span>{$user['password']}</span></td>";
                echo "<td><span>" . ($user['is_admin'] ? 'Yes' : 'No') . "</span></td>";
                echo "<td><form method='post' action='", htmlspecialchars($_SERVER["PHP_SELF"]), "'> <input type='hidden' name='doChangeUser' value='1'> <input type='hidden' name='user_id' value='", $user['user_id'], "'> <button class='btn btn-dark'type='submit' name='submit'>Switch</button> </form></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <br>
        <h4>Modify User</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="users">

            <label>Select User:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new User</option>
                <?php
                foreach ($users as $user) {
                    echo "<option value='{$user['user_id']}'>{$user['user_id']} - {$user['email']}</option>";
                }
                ?>
            </select>
            <br>

            <label>First Name:</label>
            <input type="text" name="first_name"><br>
            <label>Last Name:</label>
            <input type="text" name="last_name"><br>
            <label>Middle Initial:</label>
            <input type="text" name="m_initial"><br>
            <label>Phone Number:</label>
            <input type="text" name="phone"><br>
            <label>Password:</label>
            <input type="text" name="password"><br>
            <label for="is_admin">Is Admin?</label>
            <select name="is_admin" id="is_admin">
                <option value=""></option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>

            <br>
            <button type="submit" name="update_record" class="btn btn-dark">Update/Add</button>
            <button type="submit" name="delete_record" class="btn btn-dark">Delete</button>
        </form>
    </section>
    <hr />
    <section>
        <h3>Program Information Management</h3>
        <table border="1">
            <tr>
                <th>Program Id</th>
                <th>Program Name</th>
            </tr>

            <?php
            foreach ($programs as $program) {
                echo "<tr>";
                echo "<td><span>{$program['prog_id']}</span></td>";
                echo "<td><span>{$program['prog_name']}</span></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <br>
        <h4>Modify Program</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="programs">

            <label>Select Program:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new Program</option>
                <?php
                foreach ($programs as $program) {
                    echo "<option value='{$program['prog_id']}'>{$program['prog_id']} - {$program['prog_name']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Program Name:</label>
            <input type="text" name="record_name"><br>

            <br>
            <button type="submit" name="update_record" class="btn btn-dark">Update/Add</button>
            <button type="submit" name="delete_record" class="btn btn-dark">Delete</button>
        </form>
    </section>
    <hr />
    <section>
        <h3>Event Management</h3>
        <table border="1">
            <tr>
                <th>Event Id</th>
                <th>Event Name</th>
                <th>Event Location</th>
            </tr>

            <?php
            foreach ($events as $event) {
                echo "<tr>";
                echo "<td><span>{$event['event_id']}</span></td>";
                echo "<td><span>{$event['event_name']}</span></td>";
                echo "<td><span>{$event['event_location']}</span></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <br>
        <h4>Modify Event</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="events">

            <label>Select Event:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new Event</option>
                <?php
                foreach ($events as $event) {
                    echo "<option value='{$event['event_id']}'>{$event['event_id']} - {$event['event_name']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Event Name:</label>
            <input type="text" name="record_name"><br>
            <label>Event Location:</label>
            <input type="text" name="record_loc"><br>

            <br>
            <button type="submit" name="update_record" class="btn btn-dark">Update/Add</button>
            <button type="submit" name="delete_record" class="btn btn-dark">Delete</button>
        </form>
    </section>
    <hr />
    <section>
        <h3>Review Applications</h3>
        <table border="1">
            <tr>
                <th>Application Id</th>
                <th>User First Name</th>
                <th>User Last Name</th>
                <th>Program Name</th>
                <th>Purpose Statement</th>
                <th>Uncompleted Certifications</th>
                <th>Completed Certifications</th>
            </tr>

            <?php
            foreach ($applications as $application) {
                echo "<tr>";
                echo "<td><span>{$application['app_id']}</span></td>";
                echo "<td><span>{$application['f_name']}</span></td>";
                echo "<td><span>{$application['l_name']}</span></td>";
                echo "<td><span>{$application['prog_name']}</span></td>";
                echo "<td><span>{$application['app_purpose_statement']}</span></td>";
                echo "<td><span>{$application['uncom_cert']}</span></td>";
                echo "<td><span>{$application['com_cert']}</span></td>";
                echo "</tr>";
            }
            ?>
        </table>

        <br>
        <h4>Modify Application</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="applications">

            <label>Select Application:</label>
            <select name="selected_record_id">
                <?php
                foreach ($applications as $application) {
                    echo "<option value='{$application['app_id']}'>{$application['app_id']}</option>";
                }
                ?>
            </select>
            <br>

            <select name="is_approved" id="is_approved">
                <option value=""></option>
                <option value="1">Approve</option>
                <option value="0">Deny</option>
            </select>

            <br>
            <button type="submit" name="update_app" class="btn btn-dark">Update Application</button>
        </form>
    </section>
    <hr />
    <section>
        <h3>Trainings</h3>
        <table border="1">
            <tr>
                <th>Training Id</th>
                <th>Training Name</th>
            </tr>

            <?php
            foreach ($trainings as $training) {
                echo "<tr>";
                echo "<td><span>{$training['train_id']}</span></td>";
                echo "<td><span>{$training['train_name']}</span></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <br>
        <h4>Modify Training</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="trainings">

            <label>Select Training:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new Training</option>
                <?php
                foreach ($trainings as $training) {
                    echo "<option value='{$training['train_id']}'>{$training['train_id']} - {$training['train_name']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Training Name:</label>
            <input type="text" name="record_name"><br>

            <br>
            <button type="submit" name="update_record" class="btn btn-dark">Update/Add</button>
            <button type="submit" name="delete_record" class="btn btn-dark">Delete</button>
        </form>
    </section>
    <hr />
    <section>
        <h3>Certifications</h3>
        <table border="1">
            <tr>
                <th>Certification Id</th>
                <th>Certification Name</th>
            </tr>

            <?php
            foreach ($certifications as $certification) {
                echo "<tr>";
                echo "<td><span>{$certification['cert_id']}</span></td>";
                echo "<td><span>{$certification['cert_name']}</span></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <br>
        <h4>Modify Certification</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="certifications">

            <label>Select Certification:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new Certification</option>
                <?php
                foreach ($certifications as $certification) {
                    echo "<option value='{$certification['cert_id']}'>{$certification['cert_id']} - {$certification['cert_name']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Certification Name:</label>
            <input type="text" name="record_name"><br>

            <br>
            <button type="submit" name="update_record" class="btn btn-dark">Update/Add</button>
            <button type="submit" name="delete_record" class="btn btn-dark">Delete</button>
        </form>
    </section>
    <!-- <hr />
    <section>
        <h3>Summer Camps</h3>
        <table border="1">
            <tr>
                <th>Summer Camp Id</th>
                <th>Summer Camp Name</th>
                <th>Summer Camp Year</th>
            </tr>

            <?php
            foreach ($summercamps as $summercamp) {
                echo "<tr>";
                echo "<td><span>{$summercamp['camp_id']}</span></td>";
                echo "<td><span>{$summercamp['sc_name']}</span></td>";
                echo "<td><span>{$summercamp['sc_year']}</span></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <br>
        <h4>Modify Summer Camp</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="summercamps">

            <label>Select Summer Camp:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new Summer Camp</option>
                <?php
                foreach ($summercamps as $summercamp) {
                    echo "<option value='{$summercamp['camp_id']}'>{$summercamp['camp_id']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Summer Camp Name:</label>
            <input type="text" name="record_name"><br>
            <label>Summer Camp Year:</label>
            <input type="text" name="record_year"><br>

            <br>
            <button type="submit" name="update_record">Update/Add</button>
            <button type="submit" name="delete_record">Delete</button>
        </form>
    </section> -->
    <hr />
    <section>
        <h3>Courses</h3>
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

        <br>
        <h4>Modify Course</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="courses">

            <label>Select Course:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new Course</option>
                <?php
                foreach ($courses as $course) {
                    echo "<option value='{$course['cour_id']}'>{$course['cour_id']} - {$course['cour_name']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Course Name:</label>
            <input type="text" name="record_name"><br>

            <br>
            <button type="submit" name="update_record" class="btn btn-dark">Update/Add</button>
            <button type="submit" name="delete_record" class="btn btn-dark">Delete</button>
        </form>
    </section>
    <?php
    // Close connection
    $conn->close();
    ?>
</body>

</html>