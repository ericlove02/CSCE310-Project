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
function updateEvent($conn, $eventData)
{
    foreach ($eventData as $eventId => $values) {
        $eventName = $values['event_name'];
        $eventLocation = $values['event_location'];

        $sql = "UPDATE events SET event_name='$eventName', event_location='$eventLocation' WHERE event_id=$eventId";
        if ($conn->query($sql) !== TRUE) {
            echo "Error updating record: " . $conn->error;
        }
    }

    echo "Records updated successfully";
}

// Check if the form is submitted for updating
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_event'])) {
    // Get the array of event data
    $eventData = array();

    if (isset($_POST['event_name']) && isset($_POST['event_location'])) {
        foreach ($_POST['event_name'] as $eventId => $eventName) {
            $eventLocation = $_POST['event_location'][$eventId];
            $eventData[$eventId] = array(
                'event_name' => $eventName,
                'event_location' => $eventLocation
            );
        }
    }

    // Update the events
    updateEvent($conn, $eventData);
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
    <h2>Admin Page - Stats</h2>

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
        <h3>Add New Event</h3>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label>Event Name:</label>
            <input type="text" name="new_event_name" required><br>

            <label>Event Location:</label>
            <input type="text" name="new_event_location" required><br>

            <br>
            <button type="submit" name="add_event">Add Event</button>
        </form>
    </section>
    <section>
        <h3>Update Events</h3>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <table border="1">
                <tr>
                    <th>Event Name</th>
                    <th>Event Location</th>
                </tr>

                <?php
                // Loop through events and display each row with input boxes
                foreach ($events as $event) {
                    echo "<tr>";
                    echo "<td><input type='text' name='event_name[{$event['event_id']}]' value='{$event['event_name']}'></td>";
                    echo "<td><input type='text' name='event_location[{$event['event_id']}]' value='{$event['event_location']}'></td>";
                    echo "<input type='hidden' name='event_id[{$event['event_id']}]' value='{$event['event_id']}'>"; // Hidden input for event_id
                    echo "</tr>";
                }
                ?>

            </table>

            <br>
            <button type="submit" name="update_event">Update Events</button>
        </form>
    </section>

    <?php
    // Close connection
    $conn->close();
    ?>
</body>

</html>