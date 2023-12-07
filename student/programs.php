<?php
require '../utils/connect.php';
require '../utils/middleware.php';
require "../utils/notification.php";
require "../utils/helpers.php";

$id = $_SESSION['user_id'];
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
        <h3>My Programs & Applications</h3>
        <?php


        $id = $_SESSION['user_id'];

        // get user
        $sql = "SELECT * FROM users WHERE user_id = '$id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        // get enrollment status 
        $sql = "
SELECT * FROM programs
WHERE programs.prog_id IN ( 
    SELECT programenrollments.prog_id 
    FROM programenrollments 
    WHERE programenrollments.user_id = ${id} 
);";

        $result = $conn->query($sql);
        $enrolled_programs = $result->fetch_all();

        // get pending programs (application but not enrolled)
        $sql = "
SELECT * FROM programs
WHERE programs.prog_id NOT IN( 
    SELECT programenrollments.prog_id 
    FROM programenrollments 
    WHERE programenrollments.user_id = ${id} 
) AND programs.prog_id IN (
    SELECT applications.prog_id
    FROM applications
    WHERE applications.user_id = ${id}
);";
        $result = $conn->query($sql);
        $pending_programs = $result->fetch_all();

        // get available programs
        $sql = "
SELECT * FROM programs
WHERE programs.prog_id NOT IN( 
    SELECT programenrollments.prog_id 
    FROM programenrollments 
    WHERE programenrollments.user_id = ${id} 
) AND programs.prog_id NOT IN(
    SELECT applications.prog_id
    FROM applications
    WHERE applications.user_id = ${id} 
);";
        $result = $conn->query($sql);
        $available_programs = $result->fetch_all();

        $conn->close();
        ?>


        <?php
        function generateProgramTable($title, $programs)
        {
            $action_label = '';
            $action_url = '';

            if ($title == 'Enrolled Programs') {
                $action_label = 'View Progress';
                $action_url = 'track_program.php';
            } else if ($title == 'Pending Programs') {
                $action_label = 'View Applications';
                $action_url = 'application.php';
            } else if ($title == 'Available Programs') {
                $action_label = 'Apply';
                $action_url = 'application.php';
            }

            echo "<h3>$title</h2>";
            echo "<table>";
            echo "<tr>";
            echo "<th>Program ID</th>";
            echo "<th>Program Name</th>";
            echo "<th>Actions</th>";
            echo "</tr>";

            foreach ($programs as $program) {
                echo "<tr>";
                echo "<td>" . $program[0] . "</td>";
                echo "<td>" . $program[1] . "</td>";
                echo "<td>";
                echo "<a href='" . $action_url . "?id=" . $program[0] . "'><button class='btn btn-dark'>" . $action_label . "</button></a>";
                echo "</td>";
                echo "</tr>";
            }

            if (count($programs) == 0) {
                echo "<tr><td colspan='3'>No results</td></tr>";
            }

            echo "</table>";
        }

        generateProgramTable("Enrolled Programs", $enrolled_programs);
        generateProgramTable("Pending Programs", $pending_programs);
        generateProgramTable("Available Programs", $available_programs);
        ?>
    </section>

</body>


</html>