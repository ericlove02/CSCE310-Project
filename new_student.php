<?php

require_once "utils/connect.php";

session_start();



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
    // Make sure there are no other users with the same email

    $has_existing_user = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($has_existing_user->num_rows > 0) {
        echo "User already exists with email ${email}<br>";
        http_response_code(500);
        return;
    }

    // create the user
    $conn->execute_query("INSERT INTO Users(email, password, f_name, l_name, m_initial, phone, is_admin) VALUES (?,?,?,?,?,?,FALSE)", [$email, $password, $f_name, $l_name, $m_initial, $phone]);
    echo "User created successfully<br>";

    // get the user id of the user
    $user_id = ($conn->query("SELECT user_id FROM users WHERE email = '$email'")->fetch_assoc())["user_id"];

    // insert into students
    $stu_result = $conn->execute_query(
        "INSERT INTO STUDENTS VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$stu_uin, $user_id, $stu_gender, $stu_hisp_latino, $stu_uscitizen, $stu_firstgen, $stu_dob, $stu_discord, $stu_school, $stu_classification, $stu_grad_expect, $stu_major, $stu_major2, $stu_minor, $stu_minor2, $stu_gpa, $stu_in_rotc, $stu_in_corp, $stu_in_cyber_club, $stu_in_women_cyber]
    );

    echo "${stu_result}";

    echo "Student created successfully<br>";

    // Redirect to student page
    $_SESSION["user_id"] = $user_id;
    $_SESSION["password"] = $password;
    $_SESSION["is_admin"] = false;
    header("Location: student.php");

}

$conn->close();


function checkboxToInt($val)
{
    //$val_type = gettype($val);
    if (is_null($val)) {
        return 0;
    } else {
        return 1;
    }
}


// UI generators
function createInput($key, $label)
{
    // echo "<label for=\"$key\">${label}</label><br> <input type=\"text\" name=\"${key}\"><br>";
    echo "${label}: <input type=\"text\" name=\"${key}\"><br>";
}

function createCheckbox($key, $label)
{
    // echo "<label for=\"$key\">${label}</label><br> <input type=\"checkbox\" name=\"${key}\"><br>";
    echo "${label}: <input type=\"checkbox\" name=\"${key}\"><br>";
}

?>

<!DOCTYPE html>
<html>

<body>

    <form method="post">
        <?php

        createInput("email", "Email");
        createInput("f_name", "First Name");
        createInput("l_name", "Last Name");
        createInput("m_initial", "Middle Initial");
        createInput("phone", "Phone");
        createInput("password", "Password");
        // is_admin
        
        // student stuff
        createInput("stu_uin", "UIN"); // new
        
        createInput("stu_gender", "Gender");
        createCheckbox("stu_hisp_latino", "Hispanic?");
        createCheckbox("stu_uscitizen", "US Citizen?");
        createCheckbox("stu_firstgen", "First generation college student?");

        createInput("stu_dob", "Date of Birth");
        createInput("stu_discord", "Discord username");
        createInput("stu_school", "School");
        createInput("stu_classification", "Student classification");
        createInput("stu_grad_expect", "Expected graduation date");
        createInput("stu_major", "Student major");
        createInput("stu_major2", "Student major 2");
        createInput("stu_minor", "Student minor");
        createInput("stu_minor2", "Student minor 2"); // new
        
        createInput("stu_gpa", "Student GPA"); // new
        createCheckbox("stu_in_rotc", "In ROTC?"); // new 
        createCheckbox("stu_in_corp", "In Corps of Cadets?"); // new
        createCheckbox("stu_in_cyber_club", "In Cybersecurity Club?"); // new
        createCheckbox("stu_in_women_cyber", "In Women in Cybersecurity?"); // new
        


        // new fields
        ?>


        <input type="submit">
    </form>
</body>

</html>