<?php
$servername = "localhost";  // Usually "localhost" if the database is on the same server as your PHP script
$username = "root";
$password = "";
$dbname = "id21543832_data";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully";

?>