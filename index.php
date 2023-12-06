<?php
require_once "utils/connect.php";

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
            header("Location: admin.php");
        } else {
            header("Location: student.php");
        }
    } else {
        echo "Invalid email or password.";
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
</head>

<body>
    <h2>Login</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label>Email:</label>
        <input type="text" name="email" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <button type="submit" class="btn btn-dark">Sign In</button>
    </form>

    <br>

    <a href="new_student.php"><button class="btn btn-dark">Sign Up</button></a>
</body>

</html>