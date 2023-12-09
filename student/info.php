<?php
require '../utils/connect.php';
require '../utils/middleware.php';
require "../utils/notification.php";

$id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE user_id = '$id'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$stu_row = $conn->query("SELECT * FROM students WHERE user_id = '$id'")->fetch_assoc();

if (isset($_SESSION['update_success']) && $_SESSION['update_success']) {
    makeToast("successfully updated!", true);
    unset($_SESSION['update_success']);
} else if (isset($_SESSION['update_success']) && !$_SESSION['update_success']) {
    makeToast("failed to update", false);
    unset($_SESSION['update_success']);
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="icon" href="../tamu.ico" type="image/x-icon">
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
                    <?php if ($stu_row): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="courses.php">Courses</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="info.php">Information</a>
                    </li>
                    <?php if ($stu_row): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="internships.php">Internships</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="programs.php">Programs</a>
                        </li>
                    <?php endif; ?>
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
        <?php
        // Check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo "You must log in first";
            exit;
        } ?>
        <h2>
            <?php
            if ($stu_row) {
                echo "Student Info";
            } else {
                echo "Admin Info";
            }
            ?>
        </h2>
        <section>
            <form method="post" action="../utils/update.php">
                <?php
                function createInput($row, $key, $label, $bootstrapClass = "")
                {
                    $val = $row[$key];
                    echo "<div class=\"form-group ${bootstrapClass}\">${label}: <input type=\"text\" class=\"form-control\" name=\"${key}\" value=\"${val}\"></div>";
                }

                function createCheckbox($row, $key, $label)
                {
                    $val = $row[$key] == 1 ? 'checked' : '';
                    echo "${label}:   <input type='hidden' value='0' name='${key}'>    <input type=\"checkbox\" name=\"${key}\" value='1' {$val}><br>";
                }
                ?>
                <span>UIN:
                    <?php echo $id ?>
                </span><br>
                <div class="form-row row">
                    <?php
                    createInput($row, "email", "Email", "col-md-4");
                    createInput($row, "phone", "Phone", "col-md-4");
                    createInput($row, "password", "Password", "col-md-4");
                    ?>
                </div>
                <div class="form-row row">
                    <?php
                    createInput($row, "f_name", "First Name", "col-md-4");
                    createInput($row, "m_initial", "Middle Initial", "col-md-2");
                    createInput($row, "l_name", "Last Name", "col-md-6"); ?>
                </div>
                <?php
                // Don't try to show show student info if they are not a student
                if ($stu_row):
                    ?>
                    <div class="form-row row">
                        <?php
                        createInput($stu_row, "stu_gender", "Gender", "col-md-4");
                        createInput($stu_row, "stu_dob", "Date of Birth", "col-md-4");
                        ?>
                    </div>
                    <?php
                    createCheckbox($stu_row, "stu_hisp_latino", "Hispanic?");
                    createCheckbox($stu_row, "stu_uscitizen", "US Citizen?");
                    createCheckbox($stu_row, "stu_firstgen", "First generation college student?");
                    ?>
                    <div class="form-row row">
                        <?php
                        createInput($stu_row, "stu_discord", "Discord username", "col-md-3");
                        createInput($stu_row, "stu_school", "School", "col-md-4");
                        createInput($stu_row, "stu_classification", "Student classification", "col-md-3");
                        createInput($stu_row, "stu_grad_expect", "Expected graduation date", "col-md-2");
                        ?>
                    </div>
                    <div class="form-row row">
                        <?php
                        createInput($stu_row, "stu_major", "Student major", "col-md-3");
                        createInput($stu_row, "stu_major2", "Student major 2", "col-md-3");
                        createInput($stu_row, "stu_minor", "Student minor", "col-md-3");
                        createInput($stu_row, "stu_minor2", "Student minor 2", "col-md-3"); // new
                        ?>
                    </div>
                    <?php
                    createInput($stu_row, "stu_gpa", "Student GPA", "col-md-2"); // new
                    createCheckbox($stu_row, "stu_in_rotc", "In ROTC?"); // new
                    createCheckbox($stu_row, "stu_in_corp", "In Corps of Cadets?"); // new
                    createCheckbox($stu_row, "stu_in_cyber_club", "In Cybersecurity Club?"); // new
                    createCheckbox($stu_row, "stu_in_women_cyber", "In Women in Cybersecurity?"); // new
                    ?>
                <?php endif; ?>
                <button type="submit" name="submit" class="btn btn-dark">Update</button>
                <button type="submit" name="deactivate" class="btn btn-dark">
                    Deactivate Account
                </button>
            </form>
        </section>

        <?php if ($stu_row): ?>
            <!-- File modification -->
            <br>
            <section>
                <table border="1">
                    <tr>
                        <th>User Documents</th>
                    </tr>

                    <?php
                    $result = $conn->execute_query("SELECT file_id, user_id, filename, mimetype FROM user_documents WHERE user_id = ?", [$id]);
                    if (!$result) {
                        die("Query failed: " . $conn->error);
                    }

                    $files = [];

                    while ($file = $result->fetch_assoc()) {
                        $files[] = $file;
                    }

                    foreach ($files as $file) {
                        echo "<tr>";
                        echo "<td><a target='_blank' href='../utils/document.php?serve={$file['file_id']}'>{$file['filename']}</span></td>";
                        echo "</tr>";
                    }
                    ?>

                </table>

                <form action="../utils/document.php?return=/student/info.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="fileUpload" value="1">
                    <select name="selectedFileId">
                        <option value="-1">Upload new file</option>
                        <?php
                        foreach ($files as $file) {
                            echo "<option value='{$file['file_id']}'>{$file['filename']}</option>";
                        }
                        ?>
                    </select> <br>
                    <input type="file" name="file" id="fileToUpload"> <br />
                    <input type="submit" value="Upload/Replace File" name="submit" class="btn btn-dark">
                    <input type="submit" value="Delete File" name="submit" class="btn btn-dark">


                </form>
            </section>
        <?php endif; ?>
    </div>
</body>


</html>