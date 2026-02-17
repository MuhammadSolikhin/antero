<?php
require_once __DIR__ . '/../config/database.php';

echo "Adding certificate_file column to coach_trainings...\n";

// Check if column exists first to avoid error
$check = $conn->query("SHOW COLUMNS FROM coach_trainings LIKE 'certificate_file'");
if ($check->num_rows == 0) {
    if ($conn->query("ALTER TABLE coach_trainings ADD certificate_file VARCHAR(255) NULL AFTER description")) {
        echo "SUCCESS: certificate_file column added.\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
} else {
    echo "NOTICE: Column already exists.\n";
}

// Create upload directory if not exists
$target_dir = __DIR__ . "/../assets/uploads/trainings/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
    echo "SUCCESS: Created upload directory: $target_dir\n";
} else {
    echo "NOTICE: Upload directory already exists.\n";
}

echo "Done.\n";
?>