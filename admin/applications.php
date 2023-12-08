<?php
require_once "../utils/connect.php";
require_once "../utils/middleware.php";
require "../utils/notification.php";

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

            makeToast("Application updated successfully.", true);
        } else {
            die("Select application details failed: " . $conn->error);
        }
    }
}

// get all aplications along with their user and program data
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
                    echo "<option value='{$application['app_id']}'>{$application['app_id']} - {$application['f_name']} {$application['l_name']}, {$application['prog_name']}</option>";
                }
                ?>
            </select>
            <br>

            <select name="is_approved" id="is_approved">
                <option value=""></option>
                <option value="1">Approve</option>
                <option value="0">Deny</option>
            </select>

            <br><br>
            <button type="submit" name="update_app" class="btn btn-dark">Update Application</button>
        </form>
    </section>
    <?php
    // Close connection
    $conn->close();
    ?>
</body>

</html>