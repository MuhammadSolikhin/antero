<?php
require_once __DIR__ . '/../config/database.php';
$result = $conn->query("DESCRIBE students");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
