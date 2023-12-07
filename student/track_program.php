<?php
require_once "../utils/connect.php";
require_once "../utils/middleware.php";

// Assuming you have a session started and the user's ID is stored in $_SESSION['user_id']
$userId = $_SESSION['user_id'];
$prog_id = $_GET["id"];

$sql = "SELECT prog_name FROM programs WHERE prog_id = $prog_id";
$result = $conn->query($sql);

if (!$result) {
    // Check for errors in the query
    die("Error: " . $conn->error);
}

// Check if any rows were returned
if ($result->num_rows > 0) {
    $prog_name = $result->fetch_all(MYSQLI_ASSOC)[0]["prog_name"];
} else {
    // No rows found for the given prog_id
    die("Program not found for ID: $prog_id");
}

// fetch students certifications
$sqlCertificates = "SELECT * FROM certifications c JOIN studentcerts sc ON c.cert_id = sc.cert_id WHERE sc.user_id = $userId AND sc.sc_affliliated_program_id = $prog_id";
$resultCertificates = $conn->query($sqlCertificates);
$certificates = $resultCertificates->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedRecordId = $_POST['selected_record_id'];
    if (isset($_POST['add_cert'])) {
        if (count($certificates) > 0) {
            // Display alert and exit if count is greater than 0
            echo '<script>alert("Cannot add a certification. Only one certification per program.");</script>';
            exit;
        }
        // Get the current date
        $currentDate = date("Y-m-d");

        // Insert into studentcerts table
        $sql = "INSERT INTO studentcerts (cert_id, user_id, sc_date_started, sc_date_completed, sc_affliliated_program_id)
            VALUES ('$selectedRecordId', '$userId', '$currentDate', '', '$prog_id')";
        // Execute the SQL query
        $conn->query($sql);
    }

    if (isset($_POST['drop_cert'])) {
        if (count($certificates) > 0 && $selectedRecordId != $certificates[0]["cert_id"]) {
            echo '<script>alert("Certificate to drop must be attached to your program.");</script>';
            exit;
        }
        // Delete from studentcerts table
        $sql = "DELETE FROM studentcerts 
            WHERE cert_id = '$selectedRecordId' 
            AND user_id = '$userId' 
            AND sc_affliliated_program_id = '$prog_id'";
        // Execute the SQL query
        $conn->query($sql);

    }

    $currentDate = date("Y-m-d");

    if (isset($_POST['comp_cert'])) {
        // Update sc_date_completed in studentcerts table
        $sql = "UPDATE studentcerts 
            SET sc_date_completed = '$currentDate'
            WHERE cert_id = '$selectedRecordId' 
            AND user_id = '$userId' 
            AND sc_affliliated_program_id = '$prog_id'";
        // Execute the SQL query
        $conn->query($sql);
    }

    // refetch certificates to get changes
    $sqlCertificates = "SELECT * FROM certifications c JOIN studentcerts sc ON c.cert_id = sc.cert_id WHERE sc.user_id = $userId AND sc.sc_affliliated_program_id = $prog_id";
    $resultCertificates = $conn->query($sqlCertificates);
    $certificates = $resultCertificates->fetch_all(MYSQLI_ASSOC);
}

$sql = "SELECT * FROM certifications";
$result = $conn->query($sql);
$certifications = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Program Page</title>
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
    <div style="padding:1rem">
        <a href="programs.php" class="btn btn-dark" style="margin-top: 10px;">Back to Student</a>
        <h1>
            Your
            <?php echo $prog_name ?> Status
        </h1>
        <section>
            <h3>My
                <?php echo $prog_name ?> Certificates
            </h3>
            <table border="1">
                <tr>
                    <th>Certification Id</th>
                    <th>Certification Name</th>
                    <th>Date Started</th>
                    <th>Date Completed</th>
                </tr>
                <?php
                // Display certificates
                foreach ($certificates as $certificate) {
                    echo "<tr>";
                    echo "<td><span>{$certificate['cert_id']}</span></td>";
                    echo "<td><span>{$certificate['cert_name']}</span></td>";
                    echo "<td><span>{$certificate['sc_date_started']}</span></td>";
                    echo "<td><span>{$certificate['sc_date_completed']}</span></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </section>
        <br>
        <section>
            <h4>All Certificates
            </h4>
            <table border="1">
                <tr>
                    <th>Certification Id</th>
                    <th>Certification Name</th>
                </tr>
                <?php
                // Display all certificates
                foreach ($certifications as $certificate) {
                    echo "<tr>";
                    echo "<td><span>{$certificate['cert_id']}</span></td>";
                    echo "<td><span>{$certificate['cert_name']}</span></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </section>
        <br>
        <section>
            <h4>Modify Certificates</h4>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $prog_id; ?>">
                <label>Select Certificate:</label>
                <select name="selected_record_id">
                    <?php
                    foreach ($certifications as $certificate) {
                        echo "<option value='{$certificate['cert_id']}'>{$certificate['cert_id']} - {$certificate['cert_name']}</option>";
                    }
                    ?>
                </select>
                <br>
                <div style="padding-top: 1rem">
                    <button type="submit" name="add_cert" class="btn btn-dark">Add Cert</button>
                    <button type="submit" name="drop_cert" class="btn btn-dark">Drop Cert</button>
                    <button type="submit" name="comp_cert" class="btn btn-dark">Complete Cert</button>
                </div>
            </form>
        </section>
    </div>
</body>

</html>