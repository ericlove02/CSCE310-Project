<?php
session_start();
require_once "utils/connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"]; // Email
    $f_name = $_POST["f_name"]; // First Name
    $l_name = $_POST["l_name"]; // Last Name
    $m_initial = $_POST["m_initial"]; // Middle Initial
    $phone = $_POST["phone"]; // Phone
    $password = $_POST["password"]; // Password

    $stu_uin = $_POST["stu_uin"]; // UIN
    $stu_gender = $_POST["stu_gender"]; // Gender
    $stu_hisp_latino = checkboxToInt(@$_POST["stu_hisp_latino"]); // Hispanic?
    $stu_uscitizen = checkboxToInt(@$_POST["stu_uscitizen"]); // US Citizen?
    $stu_firstgen = checkboxToInt(@$_POST["stu_firstgen"]); // First generation college student?

    $stu_dob = $_POST["stu_dob"]; // Date of Birth
    $stu_discord = $_POST["stu_discord"]; // Discord username
    $stu_school = $_POST["stu_school"]; // School
    $stu_classification = $_POST["stu_classification"]; // Student classification
    $stu_grad_expect = $_POST["stu_grad_expect"]; // Expected graduation date
    $stu_major = $_POST["stu_major"]; // Student major
    $stu_major2 = $_POST["stu_major2"]; // Student major 2
    $stu_minor = $_POST["stu_minor"]; // Student minor
    $stu_minor2 = $_POST["stu_minor2"]; // Student minor 2

    $stu_gpa = $_POST["stu_gpa"]; // Student gpa
    $stu_in_rotc = checkBoxToInt(@$_POST["stu_in_rotc"]); // Student in rotc
    $stu_in_corp = checkBoxToInt(@$_POST["stu_in_corp"]); // Student in corps
    $stu_in_cyber_club = checkBoxToInt(@$_POST["stu_in_cyber_club"]); // Student in cs club
    $stu_in_women_cyber = checkBoxToInt(@$_POST["stu_in_women_cyber"]); // Student in women in cybersec

    // Make sure there are no other users with the same email / uin
    $has_existing_user = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($has_existing_user->num_rows > 0) {
        echo "User already exists with email ${email}<br>";
        http_response_code(500);
        return;
    }

    $has_existing_uin = $conn->query("SELECT * FROM users WHERE user_id = $stu_uin");
    if ($has_existing_uin->num_rows > 0) {
        echo "User already exists with uin ${stu_uin}<br>";
        http_response_code(500);
        return;
    }

    // create the user
    $conn->execute_query("INSERT INTO users(user_id, email, password, f_name, l_name, m_initial, phone, is_admin) VALUES (?,?,?,?,?,?,?,FALSE)", [$stu_uin, $email, $password, $f_name, $l_name, $m_initial, $phone]);
    echo "User created successfully<br>";

    // insert into students
    $stu_result = $conn->execute_query(
        "INSERT INTO students VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$stu_uin, $stu_gender, $stu_hisp_latino, $stu_uscitizen, $stu_firstgen, $stu_dob, $stu_discord, $stu_school, $stu_classification, $stu_grad_expect, $stu_major, $stu_major2, $stu_minor, $stu_minor2, $stu_gpa, $stu_in_rotc, $stu_in_corp, $stu_in_cyber_club, $stu_in_women_cyber]
    );

    echo "${stu_result}";

    echo "Student created successfully<br>";

    // Redirect to student page
    $_SESSION["user_id"] = $stu_uin;
    $_SESSION["password"] = $password;
    $_SESSION["is_admin"] = false;
    echo '<script type="text/javascript">
        window.location = "student/info.php";
      </script>';
}

$conn->close();


function checkboxToInt($val)
{
    if (is_null($val)) {
        return 0;
    } else {
        return 1;
    }
}

// UI generators
function createInput($key, $label, $bootstrapClass = "")
{
    echo "<div class=\"form-group ${bootstrapClass}\">${label}: <input type=\"text\" class=\"form-control\" name=\"${key}\"></div>";
}
function createDefaultInput($key, $label, $default, $bootstrapClass = "")
{
    echo "<div class=\"form-group ${bootstrapClass}\">${label}: <input type=\"text\" class=\"form-control\" name=\"${key}\" value=\"${default}\"></div>";
}

function createCheckbox($key, $label)
{
    echo "${label}: <input type=\"checkbox\" name=\"${key}\"><br>";
}

function createDate($key, $label, $bootstrapClass = null)
{
    echo "<div class=\"form-group ${bootstrapClass}\">${label}: <input type=\"date\" class=\"form-control\" name=\"${key}\"></div>";
}

function createSelection($key, $label, $assoc_name_value)
{
    echo "${label}: <select name=\"$key\">";
    foreach ($assoc_name_value as $name => $value) {
        echo "<option value=\"${value}\">${name}</option>";
    }
    echo "</select><br>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>New student</title>
    <link rel="icon" href="tamu.ico" type="image/x-icon">
    <link rel="stylesheet" href="/bootstrap-5.0.2-dist/css/bootstrap.min.css">
</head>
<style>
    body {
        background-color: #500000;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
    }

    .form-container {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0px 0px 20px 0px #000000;
    }

    h2 {
        color: #50000;
        text-align: center;
    }

    .back-button {
        position: absolute;
        top: 10px;
        left: 10px;
    }
</style>

<body>
    <a href="index.php" class="btn btn-dark back-button">Back to Login</a>
    <div class="form-container">
        <h2>New Student</h2>
        <form method="post">
            <div class="form-row row">
                <?php
                createInput("email", "Email", "col-md-3");
                createInput("password", "Password", "col-md-3");
                createInput("stu_uin", "UIN", "col-md-3");
                createInput("phone", "Phone", "col-md-3");
                ?>
            </div>
            <div class="form-row row">
                <?php
                createInput("f_name", "First Name", "col-md-5");
                createInput("l_name", "Last Name", "col-md-2");
                createInput("m_initial", "Middle Initial", "col-md-5");
                ?>
            </div>
            <div class="form-row row">
                <?php
                createInput("stu_gender", "Gender", "col-md-4");
                createDate("stu_dob", "Date of Birth", "col-md-4");
                createInput("stu_discord", "Discord username", "col-md-4");
                ?>
            </div>
            <?php
            createSelection("stu_classification", "Classification", array("K-12" => "K-12", "Undergraduate" => "Undergraduate"));
            ?>
            <div class="form-row row">
                <?php
                createDefaultInput("stu_school", "School", "Texas A&M University", "col-md-4");
                createDate("stu_grad_expect", "Expected graduation date", "col-md-4");
                ?>
            </div>
            <?php

            // is_admin
            
            // student stuff
            

            createCheckbox("stu_hisp_latino", "Hispanic?");
            createCheckbox("stu_uscitizen", "US Citizen?");
            createCheckbox("stu_firstgen", "First generation college student?");
            ?>
            <div class="form-row row">
                <?php
                createInput("stu_major", "Student major", "col-md-3");
                createDefaultInput("stu_major2", "Student major 2", "N/A", "col-md-3");
                createDefaultInput("stu_minor", "Student minor", "N/A", "col-md-2");
                createDefaultInput("stu_minor2", "Student minor 2", "N/A", "col-md-2"); // new
                createInput("stu_gpa", "Student GPA", "col-md-2"); // new
                ?>
            </div>
            <?php
            createCheckbox("stu_in_rotc", "In ROTC?"); // new 
            createCheckbox("stu_in_corp", "In Corps of Cadets?"); // new
            createCheckbox("stu_in_cyber_club", "In Cybersecurity Club?"); // new
            createCheckbox("stu_in_women_cyber", "In Women in Cybersecurity?"); // new
            
            // new fields
            ?>

            <input type="submit" class="btn btn-dark">
        </form>
    </div>
</body>

</html>