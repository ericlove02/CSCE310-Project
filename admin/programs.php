<?php
require_once "../utils/connect.php";
require_once "../utils/middleware.php";
require "../utils/notification.php";
require "../utils/helpers.php";

// check if page was psoted to
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // if posted to update a record
    if (isset($_POST['update_record'])) {
        $selectedRecordId = $_POST['selected_record_id'];
        $recordData = array();

        // handle data based on which table was selected
        switch ($_POST['selected_table']) {
            case 'programs':
                $recordData['prog_id'] = $_POST['selected_record_id'];
                $recordData['prog_name'] = $_POST['record_name'];
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
                case 'programs':
                    $sql = "DELETE FROM $selectedTable WHERE prog_id = $selectedRecordId";
                    break;
                default:
                    // default case
                    makeToast("Invalid table selected", false);
                    break;
            }
            if ($conn->query($sql) !== TRUE) {
                makeToast("Error deleting record: " . $conn->error, false);
            } else {
                makeToast("Program successfully deleted", true);
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
    <div id="noti"></div>
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