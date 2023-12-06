<?php

/* Handle uploading, downloading, and deleting user documents */
/*


GET methods:
/utils/document.php?serve=<file_id>
/utils/document.php?delete=<file_id>

POST / file upload:

<form action="document.php" method="post" enctype="multipart/form-data">
  Select file to upload:
  <input type="hidden" name="fileUpload" value="1">
  <input type="file" name="file" id="fileToUpload">
  <input type="submit" value="Upload File" name="submit">
</form>

*/

require_once "connect.php";
session_start();
$id = $_SESSION['user_id']; 

if(@$_GET['serve'] != NULL) {
    $result = $conn->execute_query("SELECT data, mimetype, filename FROM user_documents WHERE file_id = ?", [$_GET['serve']]);
    $row = $result->fetch_assoc();
    if($row) {
        $mime = $row['mimetype'];
        $filename = $row['filename'];
        
        header("Content-type: $mime"); // application/pdf
        header("Content-Disposition: inline; filename=\"$filename\"");

        echo $row['data'];
    } else {
        die("File not found");
    }
    //$_GET['serve'];
}

if(@$_GET['delete'] != NULL) {
    $result = $conn->execute_query("DELETE FROM user_documents WHERE file_id = ?", [$_GET['delete']]);
    if($result) {
        echo "File deleted successfully";
        return;
    }
}

if(@$_POST['fileUpload'] != NULL) {
    foreach($_FILES as $key => $file) {

        $sql = "INSERT INTO user_documents (user_id, filename, mimetype, data) VALUES (?, ?, ?, ?);";
        $result = $conn->execute_query($sql, [$id, $file['name'], $file['type'], file_get_contents($file['tmp_name'])]);
        if($result !== TRUE) {
            die("Failed to upload file");
        }

    }
}

?>

<form action="document.php" method="post" enctype="multipart/form-data">
  Select file to upload:
  <input type="hidden" name="fileUpload" value="1">
  <input type="file" name="file" id="fileToUpload">
  <input type="submit" value="Upload File" name="submit">
</form>