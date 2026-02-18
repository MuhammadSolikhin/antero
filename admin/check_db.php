<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$response = [];

// Check columns in students table
$columns = [];
$res = $conn->query("SHOW COLUMNS FROM students");
while ($row = $res->fetch_assoc()) {
    $columns[] = $row['Field'];
}
$response['columns'] = $columns;
$response['has_status'] = in_array('status', $columns);

echo json_encode($response, JSON_PRETTY_PRINT);
?>