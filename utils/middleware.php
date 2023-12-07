<?php
function access_denied() {
    echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
    
    echo '
    <div class="d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div>
            <h1 class="text-center"> Access Denied </h1>
            <p class="text-center"> You do not have permission to access this page. Either sign in to a page that does or contact your admin. </p>
            <div class="text-center"> 
                <button class="btn btn-dark" onclick="history.back()">Go Back</button>
            </div>
        </div>
    </div>';
}

if (!isset($_SESSION['user_id'])) {
    access_denied();
    exit;
}

$current_url = $_SERVER['REQUEST_URI'];
if (strpos($current_url, 'admin/') !== false) {
    $id = $_SESSION['user_id'];
    $sql = "SELECT is_admin FROM users WHERE user_id = $id";
    $result = $conn->query($sql);
    $is_admin = $result->fetch_all(MYSQLI_ASSOC)[0]["is_admin"];

    if (!$is_admin) {
        access_denied();
        exit;
    }
}
?>