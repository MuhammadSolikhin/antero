<?php
require_once '../config/database.php';

echo "<h2>Updating Database Schema...</h2>";

// 1. Add status column to students table
$sql1 = "SHOW COLUMNS FROM students LIKE 'status'";
$result1 = $conn->query($sql1);
if ($result1->num_rows == 0) {
    $sql = "ALTER TABLE students ADD COLUMN status ENUM('aktif', 'tidak aktif') DEFAULT 'aktif' AFTER tingkatan_sabuk";
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>[SUCCESS] Added 'status' column to students table.</p>";
    } else {
        echo "<p style='color:red;'>[ERROR] Failed to add 'status' column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:orange;'>[INFO] Column 'status' already exists.</p>";
}

// 2. Modify dojang_id to allow NULL
$sql2 = "ALTER TABLE students MODIFY dojang_id INT(11) NULL";
if ($conn->query($sql2)) {
    echo "<p style='color:green;'>[SUCCESS] Modified 'dojang_id' to allow NULL.</p>";
} else {
    echo "<p style='color:red;'>[ERROR] Failed to modify 'dojang_id': " . $conn->error . "</p>";
}

// 3. Drop and Re-add Foreign Key for dojang_id (students_ibfk_2) to handle SET NULL logic usually, 
// but since we just want it to be nullable, we need to ensure the FK constraint allows it. 
// Standard FK usually just checks existence. Let's try to update it to SET NULL on delete if possible, 
// or just leave it as is but nullable. 
// If the user deletes a Dojang, we want the student's dojang_id to become NULL (so they go to trash).
// So we need ON DELETE SET NULL.

// Check existing foreign key name. Usually it is students_ibfk_2 based on standard creation order, 
// but let's try to drop it safely.
$conn->query("ALTER TABLE students DROP FOREIGN KEY students_ibfk_2");

// Add it back with ON DELETE SET NULL
$sql3 = "ALTER TABLE students ADD CONSTRAINT students_ibfk_2 FOREIGN KEY (dojang_id) REFERENCES dojangs (id) ON DELETE SET NULL";
if ($conn->query($sql3)) {
    echo "<p style='color:green;'>[SUCCESS] Updated Foreign Key 'students_ibfk_2' to ON DELETE SET NULL.</p>";
} else {
    echo "<p style='color:red;'>[ERROR] Failed to update Foreign Key: " . $conn->error . "</p>";
}

echo "<p>Done.</p>";
?>