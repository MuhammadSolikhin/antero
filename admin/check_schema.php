<?php
require_once __DIR__ . '/../config/database.php';

$result = $conn->query("DESCRIBE students");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . "\n";
    }
}

echo "\nConstraint Info (All constraints for students table):\n";
$sql = "SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, DELETE_RULE 
        FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
        WHERE TABLE_NAME = 'students'";

$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "Constraint: " . $row['CONSTRAINT_NAME'] . " | Ref Table: " . $row['REFERENCED_TABLE_NAME'] . " | On Delete: " . $row['DELETE_RULE'] . "\n";
    }
} else {
    echo "Error fetching constraints: " . $conn->error;
}
?>