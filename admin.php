<?php
require_once "utils/connect.php";

function getTableRowCount($conn, $tableName)
{
    $sql = "SELECT COUNT(*) AS count FROM $tableName";
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Function to fetch all rows from the events table
function getAllEvents($conn)
{
    $sql = "SELECT * FROM events";
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $events = array();
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    return $events;
}

// Function to update a row in the events table
function updateEvent($conn, $eventId, $eventName, $eventLocation)
{
    $sql = "UPDATE events SET event_name='$eventName', event_location='$eventLocation' WHERE event_id=$eventId";
    if ($conn->query($sql) !== TRUE) {
        echo "Error updating record: " . $conn->error;
    } else {
        echo "Event updated";
    }
}

// Function to add a new event to the events table
function addEvent($conn, $eventName, $eventLocation)
{
    $sql = "INSERT INTO events (event_name, event_location) VALUES ('$eventName', '$eventLocation')";
    if ($conn->query($sql) !== TRUE) {
        echo "Error adding new record: " . $conn->error;
    } else {
        echo "New event added";
    }
}

// Check if the form is submitted for updating or adding
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_event'])) {
        $selectedEventId = $_POST['selected_event_id'];
        $eventName = $_POST['event_name'];
        $eventLocation = $_POST['event_location'];

        if ($selectedEventId == 'add_new') {
            // Add new event
            addEvent($conn, $eventName, $eventLocation);
        } else {
            // Update existing event
            updateEvent($conn, $selectedEventId, $eventName, $eventLocation);
        }
    }
}

// Fetch all events from the events table
$events = getAllEvents($conn);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
</head>

<body>
    <h2>Admin Page</h2>
    <section>
        <h3>Stats</h3>

        <ul>
            <?php
            // list of tables to get stats
            $tables = array(
                "students",
                "attendedevents",
                "events",
                "studenttrainings",
                "studentcerts",
                "programs",
                "programenrollments",
                "applications"
            );

            // loop through list
            foreach ($tables as $table) {
                $rowCount = getTableRowCount($conn, $table);
                echo "<li>Total number of $table: $rowCount</li>";
            }
            ?>
        </ul>
    </section>
    <section>
        <h3> Events</h3>
        <table border="1">
            <tr>
                <th>Event Id</th>
                <th>Event Name</th>
                <th>Event Location</th>
            </tr>

            <?php
            foreach ($events as $event) {
                echo "<tr>";
                echo "<td><span>{$event['event_id']}</span></td>";
                echo "<td><span>{$event['event_name']}</span></td>";
                echo "<td><span>{$event['event_location']}</span></td>";
                echo "</tr>";
            }
            ?>

        </table>

        <br>
        <h4>Update/Add Event</h4>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label>Select Event:</label>
            <select name="selected_event_id">
                <option value="add_new">Add new Event</option>
                <?php
                // Loop through events and display each event_id in the dropdown
                foreach ($events as $event) {
                    echo "<option value='{$event['event_id']}'>{$event['event_id']}</option>";
                }
                ?>
            </select>
            <br>

            <label>Event Name:</label>
            <input type="text" name="event_name" required><br>

            <label>Event Location:</label>
            <input type="text" name="event_location" required><br>

            <br>
            <button type="submit" name="update_event">Update/Add Event</button>
        </form>
    </section>

    <?php
    // Close connection
    $conn->close();
    ?>
</body>

</html>