<?php
require '../utils/connect.php';
require '../utils/middleware.php';
require "../utils/notification.php";
require "../utils/helpers.php";

$id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE user_id = '$id'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$stu_row = $conn->query("SELECT * FROM students WHERE user_id = '$id'")->fetch_assoc();

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
            makeToast("Cannot delete a new record", false);
        } else {
            switch ($_POST['selected_table']) {
                case 'studentinternships':
                    $sql = "DELETE FROM $selectedTable WHERE intshp_id = $selectedRecordId AND user_id = $id";
                    break;
                default:
                    // default case
                    echo "Invalid table selected";
                    break;
            }
            if ($conn->query($sql) !== TRUE) {
                makeToast("Error deleting record: " . $conn->error, false);
            } else {
                makeToast("Internship deleted", true);
            }
        }
    }
}

// get all record from the table for the user
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
            <span class="navbar-brand">User Page</span>

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
                <th>Internship Name</th>
                <th>Internship Year</th>
                <th>Internship Application Status</th>
            </tr>

            <?php
            foreach ($studentinternships as $studentinternship) {
                echo "<tr>";
                echo "<td><span>{$studentinternship['intshp_id']}</span></td>";
                echo "<td><span>{$studentinternship['intshp_name']}</span></td>";
                echo "<td><span>{$studentinternship['intshp_year']}</span></td>";
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