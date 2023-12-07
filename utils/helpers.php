<?php
function getAllRecords($conn, $tableName, $id = null, $join_table = null, $join_on = null)
{
    if ($join_table && $join_on) {
        $sql = "SELECT * FROM $tableName
                JOIN $join_table ON $tableName.$join_on = $join_table.$join_on
                WHERE $tableName.user_id = '$id'";
    } elseif ($id) {
        $sql = "SELECT * FROM $tableName WHERE user_id = '$id'";
    } else {
        $sql = "SELECT * FROM $tableName";
    }
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $records = array();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    return $records;
}

function updateRecord($conn, $tableName, $recordId, $recordData)
{
    $updateValues = '';
    $keyValues = '';
    foreach ($recordData as $column => $value) {
        if (strpos($column, '_id') !== false) {
            $keyValues .= "$column = '$value' AND ";
        } elseif ($value != '') {
            $column = fixJoinTableVariables($column);
            $updateValues .= "$column = '$value', ";
        }
    }
    $updateValues = rtrim($updateValues, ', ');
    $keyValues = rtrim($keyValues, 'AND ');

    $sql = "UPDATE $tableName SET $updateValues WHERE $keyValues";
    if ($conn->query($sql) !== TRUE) {
        makeToast("Error updating record: " . $conn->error, false);
    } else {
        makeToast("Entry in $tableName updated", true);
    }
}

function addRecord($conn, $tableName, $recordData)
{
    $fixedRecordData = array();
    foreach ($recordData as $columnName => $value) {
        $fixedColumnName = fixJoinTableVariables($columnName);
        $fixedRecordData[$fixedColumnName] = $value;
    }
    $columns = implode(', ', array_keys($fixedRecordData));
    $filteredValues = array_filter(array_values($recordData), function ($value) {
        return $value !== "add_new";
    });

    $values = "'" . implode("', '", $filteredValues) . "'";
    $sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
    if ($conn->query($sql) !== TRUE) {
        makeToast("Error adding new record: " . $conn->error, false);
    } else {
        makeToast("New entry to $tableName added", true);
    }
}

function fixJoinTableVariables($columnName)
{
    // jank fix for joined tables
    if ($columnName == "new_cour") {
        return "cour_id";
    } elseif ($columnName == "new_intshp") {
        return "intshp_id";
    } else {
        return $columnName;
    }
}

?>