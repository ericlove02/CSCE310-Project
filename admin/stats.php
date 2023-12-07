<?php
require_once "../utils/connect.php";
require_once "../utils/middleware.php";

// get count of rows from table name
function getTableRowCount($conn, $tableName)
{
    $sql = "SELECT COUNT(*) AS count FROM $tableName";
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    return $row['count'];
}

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

function generateProgramReport($conn, $prog_id)
{
    $sql = "SELECT 
            COUNT(DISTINCT pe.user_id) AS enrolled_students
            FROM 
                programenrollments pe
            JOIN 
                programs p ON pe.prog_id = p.prog_id
            WHERE 
                p.prog_id = $prog_id";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// generate reports 
function generateReport($conn, $selectedReport)
{
    switch ($selectedReport) {
        case 'report_complete_all':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS students
                    FROM
                        students s
                    WHERE
                        (SELECT COUNT(*) FROM takencourses tc WHERE tc.user_id = s.user_id) = (SELECT COUNT(*) FROM courses)
                        AND
                        (SELECT COUNT(*) FROM studentcerts sc WHERE sc.user_id = s.user_id) = (SELECT COUNT(*) FROM certifications)";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        //NOTE: NEED to add a race col for students
        case 'report_minority':
            $sql = "SELECT
                COUNT(DISTINCT s.user_id) AS students
            FROM
                students s
            JOIN
                studentraces sr ON s.user_id = sr.user_id
            JOIN
                races r ON sr.race_id = r.race_id
            WHERE
                r.race_name != 'White'";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        case 'report_fed_interns':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS students
                    FROM
                        students s
                    JOIN
                        studentinternships si ON s.user_id = si.user_id
                    JOIN
                        internships i ON si.intshp_id = i.intshp_id
                    WHERE
                        i.intshp_is_federal = '1'";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        case 'report_majors':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS students,
                        s.stu_major
                    FROM
                        students s
                    GROUP BY
                        s.stu_major";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        case 'report_intern_locs':
            $sql = "SELECT
                        COUNT(DISTINCT s.user_id) AS students,
                        i.intshp_state,
                        i.intshp_year
                    FROM
                        students s
                    JOIN
                        studentinternships si ON s.user_id = si.user_id
                    JOIN
                        internships i ON si.intshp_id = i.intshp_id
                    GROUP BY
                        i.intshp_state, i.intshp_year";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        default:
            return "Invalid report selected";
    }
}

// check if page was psoted to
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // if posted to generate a report
    if (isset($_POST['generate_report'])) {
        $selectedReport = $_POST['selected_report'];
        if (strpos($selectedReport, "program_") !== false) {
            $prog_id = str_replace("program_", "", $selectedReport);
            $reportData = generateProgramReport($conn, $prog_id);
        } else {
            $reportData = generateReport($conn, $selectedReport);
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
        <h3>Stats</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Table</th>
                    <th>Total Count</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // list of tables to get stats
                $tables = array(
                    "students",
                    "events",
                    "attendedevents",
                    "trainings",
                    "studenttrainings",
                    "certifications",
                    "studentcerts",
                    "programs",
                    "programenrollments",
                    "internships",
                    "studentinternships",
                    "courses",
                    "takencourses",
                    "applications"
                );

                // loop through list
                foreach ($tables as $table) {
                    $rowCount = getTableRowCount($conn, $table);
                    $tableName = array(
                        "students" => "Students",
                        "events" => "Events",
                        "attendedevents" => "Attended Events",
                        "trainings" => "Trainings",
                        "studenttrainings" => "Student Trainings",
                        "certifications" => "Certifications",
                        "studentcerts" => "Student Certifications",
                        "programs" => "Programs",
                        "programenrollments" => "Program Enrollments",
                        "internships" => "Internships",
                        "studentinternships" => "Student Internships",
                        "courses" => "Courses",
                        "takencourses" => "Taken Courses",
                        "applications" => "Applications"
                    )[$table] ?? $table;


                    echo "<tr>";
                    echo "<td>{$tableName}</td>";
                    echo "<td>{$rowCount}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>


        <h2>Reports</h2>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label>Select Report:</label>
            <select name="selected_report">
                <?php
                // list out all of the programs that have been created
                foreach ($programs as $program) {
                    echo "<option value='program_{$program['prog_id']}'>Number of students in the {$program['prog_name']} program</option>";
                }
                ?>
                <option value="report_complete_all">Number of students to complete all course and certification
                    opportunities</option>
                <option value="report_strat_foreign">Number of students electing to take additional strategic foreign
                    language courses</option>
                <option value="report_crypto">Number of students electing to take other cryptography and cryptographic
                    mathematics courses</option>
                <option value="report_data_sci">Number of students electing to carry additional data science and related
                    courses</option>
                <option value="report_enroll_dod_cour">Number of students to enroll in DoD 8570.01M preparation training
                    courses</option>
                <option value="report_complete_dod_cour">Number of students to complete DoD 8570.01M preparation
                    training courses</option>
                <option value="report_enroll_dod_exam">Number of students to complete a DoD 8570.01M certification
                    examination</option>
                <option value="report_minority">Minority participation</option>
                <option value="report_k_12_sc">Number of K-12 students enrolled in summer camps</option>
                <option value="report_fed_interns">Number of students pursuing federal internships</option>
                <option value="report_majors">Student majors</option>
                <option value="report_intern_locs">Student internship locations</option>
            </select>
            <button type="submit" name="generate_report" class="btn btn-dark">Generate</button>
        </form>

        <?php
        // display generate report content
        if (isset($reportData)) {
            echo "<h4>Generated Report</h4>";
            if ($reportData == null) {
                // if no data returned
                echo "<p>No data to display</p>";
            } elseif (is_array($reportData)) {
                // if an array returned show as table
                echo "<table border='1'>";
                echo "<tr>";
                foreach ($reportData[0] as $column => $value) {
                    $formattedColumnName = ucwords(str_replace('_', ' ', $column));
                    echo "<th>{$formattedColumnName}</th>";
                }
                echo "</tr>";
                foreach ($reportData as $row) {
                    echo "<tr>";
                    foreach ($row as $column => $value) {
                        echo "<td>{$value}</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                // if just one item show just the piece of data
                echo "<p>{$reportData}</p>";
            }
        }
        ?>

    </section>
    <?php
    // Close connection
    $conn->close();
    ?>
</body>

</html>