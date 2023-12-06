<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must log in first";
    exit;
}

require 'utils/connect.php';

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
    WHERE programenrollments.user_id = ${id} AND programenrollments.pe_enroll_pending = 0
);";

$result = $conn->query($sql);
$enrolled_programs = $result->fetch_all();

// get pending programs
$sql = "
SELECT * FROM programs
WHERE programs.prog_id IN ( 
    SELECT programenrollments.prog_id 
    FROM programenrollments 
    WHERE programenrollments.user_id = ${id} AND programenrollments.pe_enroll_pending = 1
);";
$result = $conn->query($sql);
$pending_programs = $result->fetch_all();

// get available programs
$sql = "
SELECT * FROM programs 
WHERE programs.prog_id NOT IN ( 
    SELECT programenrollments.prog_id 
    FROM programenrollments 
    WHERE programenrollments.user_id = ${id} 
);";
$result = $conn->query($sql);
$available_programs = $result->fetch_all();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Applications</title>
    <link rel="stylesheet" href="/bootstrap-5.0.2-dist/css/bootstrap.min.css">
</head>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    th {
        background-color: #500000;
        color: white;
    }
</style>

<body>
    <h1>Student Applications</h1>
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
            $action_url = 'view_appliation.php';
        } else if ($title == 'Available Programs') {
            $action_label = 'Apply';
            $action_url = 'application.php';
        }

        echo "<h2>$title</h2>";
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
</body>

</html>