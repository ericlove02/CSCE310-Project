<?php
require_once "../utils/connect.php";
require_once "../utils/middleware.php";
require "../utils/notification.php";

// select all entities from a table
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

// update a table given the table name and a dict of values
function updateRecord($conn, $tableName, $recordId, $recordData)
{
    $updateValues = '';
    $id_key = '';

    // parse value pairs
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
        makeToast("Error updating record: " . $conn->error, false);
    } else {
        makeToast("Event successfully updated", true);
    }
}

// insert into given table name with dict of values
function addRecord($conn, $tableName, $recordData)
{
    foreach ($recordData as $key => $value) {
        if ($value == "add_new") {
            unset($recordData[$key]);
        }
    }
    $columns = implode(', ', array_keys($recordData));
    $values = "'" . implode("', '", array_values($recordData)) . "'";
    $sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
    if ($conn->query($sql) !== TRUE) {
        makeToast("Error adding new record: " . $conn->error, false);
    } else {
        makeToast("New event added", true);
    }
}

// check if page was psoted to
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // if posted to update a record
    if (isset($_POST['update_record'])) {
        $selectedRecordId = $_POST['selected_record_id'];
        $recordData = array();

        // handle data based on which table was selected
        switch ($_POST['selected_table']) {
            case 'events':
                $recordData['event_id'] = $_POST['selected_record_id'];
                $recordData['event_name'] = $_POST['record_name'];
                $recordData['event_location'] = $_POST['record_loc'];
                break;
            default:
                // default case
                makeToast("Invalid table selected", false);
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
            makeToast("Error: Cannot delete a new record.", false);
        } else {
            switch ($_POST['selected_table']) {
                case 'events':
                    $sql = "DELETE FROM $selectedTable WHERE event_id = $selectedRecordId";
                    break;
                default:
                    // default case
                    makeToast("Invalid table selected", false);
                    break;
            }
            if ($conn->query($sql) !== TRUE) {
                makeToast("Error deleting record: " . $conn->error, false);
            } else {
                makeToast("Event deleted successfully.", true);
            }
        }
    }
}

// get all records from each table
$events = getAllRecords($conn, 'events');
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">Admin Page</span>

            <!-- Navbar links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="applications.php">Applications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="certifications.php">Certifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="programs.php">Programs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stats.php">Stats</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                </ul>
            </div>

            <!-- Logout button on right side -->
            <div class="navbar-nav ms-auto">
                <a href="../utils/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>
    <section>
        <h3>Event Management</h3>
        <table border="1">
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Location</th>
                <th>Attendance</th>
                <th></th>
            </tr>

            <?php
            foreach ($events as $event) {
                echo "<tr>";
                echo "<td><span>{$event['event_id']}</span></td>";
                echo "<td><span>{$event['event_name']}</span></td>";
                echo "<td><span>{$event['event_location']}</span></td>";
                echo "<td>", $conn->execute_query('SELECT count(user_id) as count FROM attendedevents WHERE event_id = ?', [$event['event_id']])->fetch_assoc()['count'], " people </td>";
                echo "<td><a href='event_attendance.php?id={$event['event_id']}' style='right:0px;position:'><button class='btn btn-dark'>Edit attendance</button></a></td>";
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
    <?php
    // Close connection
    $conn->close();
    ?>
</body>

</html>