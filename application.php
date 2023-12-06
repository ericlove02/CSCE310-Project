<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must log in first";
    exit;
}

require 'utils/connect.php';

// get user
$id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = '$id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// get id from url
$prog_id = $_GET['id'];

// get program
$sql = "SELECT programs.prog_name, programs.prog_id FROM programs WHERE programs.prog_id = '$prog_id'";
$result = $conn->query($sql);
$program = $result->fetch_assoc();

// check if post request was made
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prog_id = $_POST['prog_id'];
    $user_id = $_POST['user_id'];
    $application = $_POST['application'];
    $oncom_cert = $_POST['oncom_cert'];
    $oncom_cert = $_POST['com_cert'];

    $sql = "INSERT INTO applications (prog_id, user_id, , oncom_cert, oncom_cert) VALUES ('$prog_id', '$user_id', '$application', '$oncom_cert', '$oncom_cert')";

    if ($conn->query($sql) === TRUE) {
        echo "Application submitted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Program Application</title>
    </head>

    <body>
        <h1>Applications for <?php echo $program['prog_name']?></h1>
        <form method="post" action="application.php">
            <input type="hidden" name="prog_id" value="<?php echo $program['prog_id']?>">
            <input type="hidden" name="user_id" value="<?php echo $id?>">
            <label>Why do you want to join this program?</label><br>
            <textarea name="application" rows="10" cols="50"></textarea><br>

            <label>Are you currently enrolled in other uncompleted certifications sponsored by the Cybersecurity Center? </label> <br>
            <textarea name="oncom_cert" rows="5" cols="50"></textarea><br> 

            <label>Have you completed any cybersecurity industry certifications via the Cybersecurity Center?  </label> <br>
            <textarea name="com_cert" rows="5" cols="50"></textarea><br> 
            
            <button type="submit">Submit</button>
        </form> 
    </body>
</html>