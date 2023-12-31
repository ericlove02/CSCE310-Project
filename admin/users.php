<?php
/**
 * Main Author: Eric Love
 * Co-Author: Mateo Ruiz 
 */

require_once "../utils/connect.php";
require_once "../utils/middleware.php";
require "../utils/notification.php";
require "../utils/helpers.php";

// switch to new account from users button press
if (@$_POST['doChangeUser']) {
    session_start();
    // store the admins id so they can return
    $_SESSION['admin_id'] = $_SESSION['user_id'];
    // store the user id that they are checking out
    $_SESSION['user_id'] = $_POST['user_id'];
    echo '<script type="text/javascript">
            window.location = "../student/info.php";
        </script>';
    return;
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
                if ($_POST['selected_record_id'] == "add_new") {
                    $recordData['user_id'] = $_POST['user_id'];
                }
                $recordData['email'] = $_POST['email'];
                $recordData['f_name'] = $_POST['first_name'];
                $recordData['l_name'] = $_POST['last_name'];
                $recordData['m_initial'] = $_POST['m_initial'];
                $recordData['phone'] = $_POST['phone'];
                $recordData['password'] = $_POST['password'];
                $recordData['is_admin'] = $_POST['is_admin'];
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
            $tables = [
                'applications',
                'attendedevents',
                'programenrollments',
                'studentcerts',
                'studentinternships',
                'takencourses',
                'user_documents',
                'students',
                'users'
            ];

            try {
                $conn->begin_transaction();

                foreach ($tables as $table) {
                    $sql = "DELETE FROM $table WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $selectedRecordId);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to delete records from $table");
                    }
                }

                $conn->commit();
                makeToast("User records deleted successfully", true);
            } catch (Exception $e) {
                $conn->rollBack();
                makeToast("Error: " . $e->getMessage(), false);
            }
        }
    }
}

// get all records from each table
$users = getAllRecords($conn, 'users');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="icon" href="../tamu.ico" type="image/x-icon">
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
                <th>Go to Student View</th>
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
            <label>User Id (UIN):</label>
            <input type="text" name="user_id" placeholder="Only for new users"><br>
            <label>Email:</label>
            <input type="text" name="email"><br>
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
    <?php
    // Close connection
    $conn->close();
    ?>
</body>

</html>