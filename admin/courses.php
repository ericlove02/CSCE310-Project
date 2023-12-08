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
            case 'courses':
                $recordData['cour_id'] = $_POST['selected_record_id'];
                $recordData['cour_name'] = $_POST['record_name'];
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
            echo '<script>alert("Error: Cannot delete a new record.");</script>';
        } else {
            switch ($_POST['selected_table']) {
                case 'courses':
                    $sql = "DELETE FROM $selectedTable WHERE cour_id = $selectedRecordId";
                    break;
            }
            if ($conn->query($sql) !== TRUE) {
                echo "Error deleting record: " . $conn->error;
            } else {
                makeToast("Course deleted successfully.", true);
            }
        }
    }
}

// get all records from each table
$courses = getAllRecords($conn, 'courses');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../tamu.ico" type="image/x-icon">
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