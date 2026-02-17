<?php
require_once '../config/database.php';

// Create table `student_dojang_history`
$sql_create = "CREATE TABLE IF NOT EXISTS student_dojang_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    old_dojang_id INT DEFAULT NULL,
    new_dojang_id INT DEFAULT NULL,
    change_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    reason TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (old_dojang_id) REFERENCES dojangs(id) ON DELETE SET NULL,
    FOREIGN KEY (new_dojang_id) REFERENCES dojangs(id) ON DELETE SET NULL
)";

if ($conn->query($sql_create)) {
    echo "Table 'student_dojang_history' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
    exit();
}

echo "Database update for dojang history completed.";
?>