<?php
require_once '../config/database.php';

// Add foto_sertifikat column to students table
$check_col = $conn->query("SHOW COLUMNS FROM students LIKE 'foto_sertifikat'");
if ($check_col->num_rows == 0) {
    if ($conn->query("ALTER TABLE students ADD COLUMN foto_sertifikat VARCHAR(255) AFTER tingkatan_sabuk")) {
        echo "Column 'foto_sertifikat' added successfully.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "Column 'foto_sertifikat' already exists.<br>";
}

// Create uploads directory if not exists
$target_dir = "../assets/uploads/certificates/";
if (!is_dir($target_dir)) {
    if (mkdir($target_dir, 0777, true)) {
        echo "Directory created: " . $target_dir . "<br>";
    } else {
        echo "Failed to create directory: " . $target_dir . "<br>";
    }
} else {
    echo "Directory already exists: " . $target_dir . "<br>";
}

echo "Database update script completed.";
?>