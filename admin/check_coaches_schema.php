<?php
require_once __DIR__ . '/../config/database.php';

$result = $conn->query("DESCRIBE coaches");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
}
?>