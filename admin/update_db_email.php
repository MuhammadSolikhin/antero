<?php
require_once '../config/database.php';

// 1. Add email column to users table if not exists
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
if ($check_col->num_rows == 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL AFTER username")) {
        echo "Column 'email' added to 'users' tableSuccessfully.<br>";
    } else {
        echo "Error adding column email: " . $conn->error . "<br>";
    }
} else {
    echo "Column 'email' already exists in 'users' table.<br>";
}

// 2. Create password_resets table
$sql_resets = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_resets)) {
    echo "Table 'password_resets' created (or already exists) successfully.<br>";
} else {
    echo "Error creating table 'password_resets': " . $conn->error . "<br>";
}

echo "Database update completed.";
?>