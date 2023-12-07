<?php
$servername = "localhost";  // Usually "localhost" if the database is on the same server as your PHP script
$username = "root"; // user for server: id21543832_root
$password = ""; // pw for server: Password1!
$dbname = "id21543832_data";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// echo "Connected successfully";

?>
<style> /* Cool hack to pad every page */
    body {
        padding: 1em;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd !important;
        padding: 8px;
    }

    th {
        background-color: #500000;
        color: white;
    }
</style>