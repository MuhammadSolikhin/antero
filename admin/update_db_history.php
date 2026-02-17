<?php
require_once '../config/database.php';

// 1. Create table `student_belt_history`
$sql_create = "CREATE TABLE IF NOT EXISTS student_belt_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    tingkatan_sabuk VARCHAR(100) NOT NULL,
    foto_sertifikat VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if ($conn->query($sql_create)) {
    echo "Table 'student_belt_history' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
    exit();
}

// 2. Migrate existing data
// We only want to migrate if the history table is empty to avoid duplicates on re-runs
$check_empty = $conn->query("SELECT COUNT(*) as count FROM student_belt_history")->fetch_assoc();

if ($check_empty['count'] == 0) {
    echo "Migrating existing belt data...<br>";

    $students = $conn->query("SELECT id, tingkatan_sabuk, foto_sertifikat FROM students WHERE tingkatan_sabuk IS NOT NULL");

    $count = 0;
    while ($row = $students->fetch_assoc()) {
        $sid = $row['id'];
        $sabuk = $conn->real_escape_string($row['tingkatan_sabuk']);
        $cert = $row['foto_sertifikat'] ? "'" . $conn->real_escape_string($row['foto_sertifikat']) . "'" : "NULL";

        $insert = "INSERT INTO student_belt_history (student_id, tingkatan_sabuk, foto_sertifikat) VALUES ($sid, '$sabuk', $cert)";

        if ($conn->query($insert)) {
            $count++;
        } else {
            echo "Failed to migrate student ID $sid: " . $conn->error . "<br>";
        }
    }
    echo "Migration completed. $count records migrated.<br>";
} else {
    echo "Data already migrated (table not empty).<br>";
}

echo "Database update for belt history completed.";
?>