<?php

function fetch_application($user_id, $prog_id)
{
    require '../utils/connect.php';

    $sql = "SELECT * FROM applications WHERE user_id = '$user_id' AND prog_id = '$prog_id'";
    $result = $conn->query($sql);
    $application = $result->fetch_assoc();

    $conn->close();

    return $application;
}

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must log in first";
    exit;
}

require '../utils/connect.php';

// check if post request was made
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['req_type'] == "PUT") {
    $prog_id = $_POST['prog_id'];
    $user_id = $_POST['user_id'];
    $purpose_statement = $_POST['purpose_statement'];
    $oncom_cert = $_POST['oncom_cert'];
    $com_cert = $_POST['com_cert'];

    // check if the user has already applied
    $application = fetch_application($user_id, $prog_id);
    if ($application) {
        $sql = $conn->prepare("UPDATE applications SET app_purpose_statement = ?, uncom_cert = ?, com_cert = ? WHERE user_id = $user_id AND prog_id = $prog_id");
        $result = $sql->execute([$purpose_statement, $oncom_cert, $com_cert]);
    }

    $sql = $conn->prepare("INSERT INTO APPLICATIONS (prog_id, user_id, app_purpose_statement, uncom_cert, com_cert) VALUES ($prog_id, $user_id, ?, ?, ?);");
    $result = $sql->execute([$purpose_statement, $oncom_cert, $com_cert]);

    if ($result) {
        echo "Application submitted successfully";
        header("Location: programs.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['req_type'] == "DELETE") {
    $prog_id = $_POST['prog_id'];
    $user_id = $_POST['user_id'];

    $sql = "DELETE FROM applications WHERE user_id = $user_id AND prog_id = $prog_id";
    $result = $conn->query($sql);

    if ($result) {
        echo "Application deleted successfully";
        header("Location: programs.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
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

    // if the user has already applied, populate the form with their previous answers
    $application = fetch_application($id, $prog_id);
    if ($application) {
        $purpose_statement = $application['app_purpose_statement'];
        $uncom_cert = $application['uncom_cert'];
        $com_cert = $application['com_cert'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Application</title>
    <link rel="stylesheet" href="/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <button class="btn btn-dark" onclick='history.back()'>Go Back</button>
    <h1>Your Application for
        <?php echo $program['prog_name'] ?>
    </h1>
    <form method="POST" action="application.php">
        <input type="hidden" name="req_type" value="PUT">
        <input type="hidden" name="prog_id" value="<?php echo $program['prog_id'] ?>">
        <input type="hidden" name="user_id" value="<?php echo $id ?>">
        <div class="mb-3">
            <label class="form-label">Why do you want to join this program?</label>
            <textarea name="purpose_statement" rows="5"
                required><?php echo isset($purpose_statement) ? $purpose_statement : '' ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Are you currently enrolled in other uncompleted certifications sponsored by the
                Cybersecurity Center? (Optional)</label>
            <textarea name="oncom_cert" rows="3"><?php echo isset($uncom_cert) ? $uncom_cert : '' ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Have you completed any cybersecurity industry certifications via the Cybersecurity
                Center? (Optional)</label>
            <textarea name="com_cert" rows="3"><?php echo isset($com_cert) ? $com_cert : '' ?></textarea>
        </div>

        <button class="btn btn-dark" type="submit">
            <?php echo isset($purpose_statement) ? 'Update' : 'Submit' ?>
        </button>
    </form>

    <?php
    if ($application) {
        echo '<form action="application.php" method="POST">
            <input type="hidden" name="req_type" value="DELETE">
            <input type="hidden" name="prog_id" value="' . $program['prog_id'] . '">
            <input type="hidden" name="user_id" value="' . $id . '">
            <button class="btn btn-danger" type="submit" value="Delete">Delete</button>
        </form>';
    }
    ?>
</body>

</html>