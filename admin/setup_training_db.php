<?php
require_once __DIR__ . '/../config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS student_trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    certificate_file VARCHAR(255) NOT NULL,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    admin_note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table student_trainings created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
?>