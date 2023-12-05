<?php
require_once "utils/connect.php";

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

    if ($conn->query($sql) !== TRUE) {
        echo "Error adding new record: " . $conn->error;
    } else {
        echo "New entry to $tableName added";
    }
}

// check if page was psoted to
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_record'])) {
        $selectedRecordId = $_POST['selected_record_id'];
        $recordData = array();

        // handle data based on selected table
        switch ($_POST['selected_table']) {
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
$summercamps = getAllRecords($conn, 'summercamps');
$courses = getAllRecords($conn, 'courses');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
</head>

<body>
    <h2>Admin Page</h2>
    <section>
        <h3>Stats</h3>

        <ul>
            <?php
            // list of tables to get stats
            $tables = array(
                "students",
                "attendedevents",
                "events",
                "studenttrainings",
                "studentcerts",
                "programs",
                "programenrollments",
                "applications"
            );

            // loop through list
            foreach ($tables as $table) {
                $rowCount = getTableRowCount($conn, $table);
                $tableName = array(
                    "attendedevents" => "attended events",
                    "studenttrainings" => "student trainings",
                    "studentcerts" => "student certifications",
                    "programenrollments" => "program enrollments"
                )[$table] ?? $table;
                
                echo "<li>Total number of $tableName: $rowCount</li>";
            }
            ?>
        </ul>
    </section>
    <hr />
    <section>
        <h3>Events</h3>
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
                    echo "<option value='{$event['event_id']}'>{$event['event_id']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Event Name:</label>
            <input type="text" name="record_name"><br>
            <label>Event Location:</label>
            <input type="text" name="record_loc"><br>

            <br>
            <button type="submit" name="update_record">Update/Add</button>
            <button type="submit" name="delete_record">Delete</button>
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
                    echo "<option value='{$training['train_id']}'>{$training['train_id']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Training Name:</label>
            <input type="text" name="record_name"><br>

            <br>
            <button type="submit" name="update_record">Update/Add</button>
            <button type="submit" name="delete_record">Delete</button>
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
        <h4>Modify Training</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="certifications">

            <label>Select Certification:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new Certification</option>
                <?php
                foreach ($certifications as $certification) {
                    echo "<option value='{$certification['cert_id']}'>{$certification['cert_id']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Certification Name:</label>
            <input type="text" name="record_name"><br>

            <br>
            <button type="submit" name="update_record">Update/Add</button>
            <button type="submit" name="delete_record">Delete</button>
        </form>
    </section>
    <hr />
    <!-- Repeat similar sections for programs, summercamps, and courses -->
    <section>
        <h3>Programs</h3>
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
        <h4>Modify Training</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selected_table" value="programs">

            <label>Select Program:</label>
            <select name="selected_record_id">
                <option value="add_new">Add new Program</option>
                <?php
                foreach ($programs as $program) {
                    echo "<option value='{$program['prog_id']}'>{$program['prog_id']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Program Name:</label>
            <input type="text" name="record_name"><br>

            <br>
            <button type="submit" name="update_record">Update/Add</button>
            <button type="submit" name="delete_record">Delete</button>
        </form>
    </section>
    <hr />
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
    </section>
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
                    echo "<option value='{$course['cour_id']}'>{$course['cour_id']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Course Name:</label>
            <input type="text" name="record_name"><br>

            <br>
            <button type="submit" name="update_record">Update/Add</button>
            <button type="submit" name="delete_record">Delete</button>
        </form>
    </section>
    <?php
    // Close connection
    $conn->close();
    ?>
</body>

</html>