<?php
require_once "utils/connect.php";

function get_attendance($event_id, $user_id)
{
    global $conn;
    $result = $conn->execute_query('SELECT * FROM attendedevents WHERE event_id = ? AND user_id = ?', [$event_id, $user_id]);
    return $result->fetch_assoc();
}
function add_attendance($event_id, $user_id)
{
    global $conn;
    if (!get_attendance($event_id, $user_id)) {
        $result = $conn->execute_query('INSERT INTO attendedevents (event_id, user_id) VALUES (?, ?)', [$event_id, $user_id]);
    } else {
        echo "User $user_id is already attending the event.";
    }
}

$event_data = $conn->execute_query("SELECT * FROM events WHERE event_id = ?", [$_GET["id"]])->fetch_assoc();

$UIN_list = preg_replace(['/[^0-9]+/', '/, $/'], [', ', ''], $_POST['UINlist'] ?? '');

if ($UIN_list != '') {
    if ($_POST['submit'] == 'add') {
        $sql = 'SELECT * FROM users WHERE user_id IN (' . $UIN_list . ');';
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            add_attendance($event_data['event_id'], $row['user_id']);
        }
    } elseif ($_POST['submit'] == 'remove') {
        $sql = 'DELETE FROM attendedevents WHERE user_id IN (' . $UIN_list . ') AND event_id = ' . $event_data['event_id'];
        $conn->query($sql);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Attendance</title>
    <link rel="stylesheet" href="/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #500000;
        }

        .center-box {
            background-color: white;
            padding: 20px;
            margin: auto;
            margin-top: 50px;
            max-width: 600px;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <a href="admin.php" class="btn btn-dark">Back to Admin Page</a>
    <div class="container center-box">
        <h1 class="text-center">
            <?php echo $event_data['event_name']; ?>
        </h1>
        <h3 class="text-center">Location:
            <?php echo $event_data['event_location']; ?>
        </h3>

        <div class="mb-3">
            <h2>List of UINs:</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                <textarea name="UINlist" class="form-control"><?php echo @$_POST['UINlist']; ?></textarea>
                <button type="submit" name="submit" class="btn btn-dark mt-2" value='add'>Add UIN to attendance</button>
                <button type="submit" name="submit" class="btn btn-dark mt-2" value='remove'>Remove UINs from
                    attendance</button>
            </form>
        </div>

        <h4 class="text-center">Attendees:</h4>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $event_attendees = $conn->execute_query("SELECT * FROM users WHERE user_id in (SELECT user_id FROM attendedevents WHERE event_id = ?)", [$_GET["id"]]);
                while ($row = $event_attendees->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['user_id']}</td>";
                    echo "<td>{$row['f_name']}</td>";
                    echo "<td>{$row['l_name']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>

</html>