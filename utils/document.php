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

if (@$_GET['serve'] != NULL) {
    $result = $conn->execute_query("SELECT data, mimetype, filename FROM user_documents WHERE file_id = ?", [$_GET['serve']]);
    $row = $result->fetch_assoc();
    if ($row) {
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



if (@$_POST['fileUpload'] != NULL) {

    if ($_POST['selectedFileId'] != -1) {
        foreach ($_FILES as $key => $file) {
            if ($_POST['submit'] == 'Delete File') {
                $result = $conn->execute_query("DELETE FROM user_documents WHERE file_id = ?", [$_POST['selectedFileId']]);
                if ($result) {
                    echo "File deleted successfully";
                }
            } else {
                $sql = "UPDATE user_documents SET data = ?, filename = ?, mimetype = ? WHERE file_id = ?";
                $result = $conn->execute_query($sql, [file_get_contents($file['tmp_name']), $file['name'], $file['type'], $_POST['selectedFileId']]);
                echo 'Sucessfully updated file';
                if ($result !== TRUE) {
                    die("Failed to upload file");
                }
            }
        }
    } else {
        if ($_POST['submit'] == 'Delete File') {
            die("Choose a file to delete");
        } else {
            foreach ($_FILES as $key => $file) {

                $sql = "INSERT INTO user_documents (user_id, filename, mimetype, data) VALUES (?, ?, ?, ?);";
                $result = $conn->execute_query($sql, [$id, $file['name'], $file['type'], file_get_contents($file['tmp_name'])]);
                if ($result !== TRUE) {
                    die("Failed to upload file");
                }

            }
        }
    }
}


if (@$_GET['return']) {
    header("Location: " . urldecode($_GET['return']));
}

?>