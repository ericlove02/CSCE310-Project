<?php
require_once "../utils/connect.php";


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
        echo "Error updating record: " . $conn->error;
    } else {
        echo "Entry in $tableName updated";
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
        echo "Error adding new record: " . $conn->error;
    } else {
        echo "New entry to $tableName added";
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
$programs = getAllRecords($conn, 'programs');
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
    <?php
    // Close connection
    $conn->close();
    ?>
</body>

</html>