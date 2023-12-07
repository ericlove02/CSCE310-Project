<?php
require_once "utils/connect.php";
require "utils/notification.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // set the session vars
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['is_admin'] = $row['is_admin'];

        // redirect to correct page
        if ($row['is_admin'] == 1) {
            header("Location: admin/applications.php");
        } else {
            if (isset($_SESSION['admin_id']))
                unset($_SESSION['admin_id']);
            header("Location: student/info.php");
        }
    } else {
        makeToast("Invalid email or password.", false);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #500000;
            /* maroon */
            color: black;
            text-align: center;
            padding: 50px;
        }

        .center-box {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            display: inline-block;
        }

        h2 {
            margin-bottom: 30px;
        }

        form {
            max-width: 300px;
            margin: auto;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
        }

        a {
            display: block;
            margin-top: 20px;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="center-box">
        <h2>Login</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label>Email:</label>
            <input type="text" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn btn-dark">Sign In</button>
        </form>

        <a href="new_student.php" class="btn btn-dark">Sign Up</a>
    </div>
</body>

</html>