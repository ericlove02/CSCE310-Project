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

    // Make sure there are no other users with the same email

    $has_existing_user = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($has_existing_user->num_rows > 0) {
        echo "User already exists with email ${email}<br>";
        http_response_code(500);
        return;
    }

    // create the user
    $conn->execute_query("INSERT INTO Users(email, f_name, l_name, m_initial, phone, is_admin) VALUES (?,?,?,?,?,FALSE)", [$email, $f_name, $l_name, $m_initial, $phone]);
    echo "User created successfully<br>";

    // get the user id of the user
    $user_id = ($conn->query("SELECT user_id FROM users WHERE email = '$email'")->fetch_assoc())["user_id"];

    // insert into students
    $stu_result = $conn->execute_query("INSERT INTO STUDENTS VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)", [$user_id, $stu_gender, $stu_hisp_latino, $stu_uscitizen, $stu_firstgen, $stu_dob, $stu_discord, $stu_school, $stu_classification, $stu_grad_expect, $stu_major, $stu_major2, $stu_minor]);

    echo "${stu_result}";

    echo "Student created successfully<br>";

    // Redirect to student page
    $_SESSION["user_id"]=$user_id;
    $_SESSION["password"]=$password;
    $_SESSION["is_admin"]=false;
    header("Location: student.php");


    
    /* $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // set the session vars
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['is_admin'] = $row['is_admin'];

        // redirect to correct page
        if ($row['is_admin'] == 1) {
            header("Location: admin.php");
        } else {
            header("Location: student.php");
        }
    } else {
        echo "Invalid email or password.";
    } */
}

$conn->close();


function checkboxToInt($val) {
    //$val_type = gettype($val);
    if(is_null($val)) {
        return 0;
    } else {
        return 1;
    }
}


// UI generators
function createInput($key, $label) {
    echo "${label}: <input type=\"text\" name=\"${key}\"><br>";
}

function createCheckbox($key, $label) {
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
?>


<input type="submit">
</form>
</body>
    </html>