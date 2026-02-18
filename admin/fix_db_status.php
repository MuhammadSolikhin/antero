<?php
require_once '../config/database.php';

echo "<h2>Fixing Database Schema...</h2>";

// 1. Force Add status column to students table
// Using simple query execution without IF NOT EXISTS check which is usually safer if we catch error, but distinct query is fine too.
$sql = "ALTER TABLE students ADD COLUMN status ENUM('aktif', 'tidak aktif') DEFAULT 'aktif' AFTER tingkatan_sabuk";

if ($conn->query($sql)) {
    echo "<p style='color:green;'>[SUCCESS] Added 'status' column to students table.</p>";
} else {
    echo "<p style='color:red;'>[ERROR] Failed to add 'status' column: " . $conn->error . "</p>";
}

// 2. Double check
$res = $conn->query("SHOW COLUMNS FROM students LIKE 'status'");
if ($res->num_rows > 0) {
    echo "<p style='color:green;'>[CONFIRMED] Column 'status' now exists.</p>";
} else {
    echo "<p style='color:red;'>[FAILED] Column 'status' still missing.</p>";
}
?>