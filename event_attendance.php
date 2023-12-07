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
        echo "User $user_id is already attending event.";
    }
}


$event_data = $conn->execute_query("SELECT * FROM events WHERE event_id = ?", [$_GET["id"]])->fetch_assoc();


$UIN_list = preg_replace(['/[^0-9]+/'], [', '], $_POST['UINlist'] ?? '');

if ($UIN_list != '') {
    if ($_POST['submit'] == 'add') {
        $sql = 'SELECT * FROM users WHERE user_id IN (' . $UIN_list . ');';
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {

            add_attendance($event_data['event_id'], $row['user_id']);
        }
        // echo $row['f_name'], " ", $row['l_name'];
    } elseif ($_POST['submit'] == 'remove') {
        $sql = 'DELETE FROM attendedevents WHERE user_id IN (' . $UIN_list . ') AND event_id = ' . $event_data['event_id'];
        // echo $sql;
        $conn->query($sql);
    }

}
echo "<h1> ", $event_data['event_name'], "</h1>";
echo "<h3> Location: ", $event_data['event_location'], "</h3>";


$event_attendees = $conn->execute_query("SELECT * FROM users WHERE user_id in (SELECT user_id FROM attendedevents WHERE event_id = ?)", [$_GET["id"]]);
echo "Attendees:<br>";
while ($row = $event_attendees->fetch_assoc()) {
    echo $row['user_id'], ' - ', $row['f_name'], " ", $row['l_name'], "<br>";
}

?>
<h2>List of UINs:<h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">


            <textarea name="UINlist">
<?php
echo @$_POST['UINlist'];
// insert old list 
?>
</textarea><br>
            <button type="submit" name="submit" class="btn btn-dark" value='add'>Add UIN to attendance</button>
            <button type="submit" name="submit" class="btn btn-dark" value='remove'>Remove UINs from attendance</button>
        </form>