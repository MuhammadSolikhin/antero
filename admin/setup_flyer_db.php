<?php
require_once '../config/database.php';

echo "<h2>Setting up Flyers Table</h2>";

$sql = "CREATE TABLE IF NOT EXISTS flyers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<span style='color:green;'>Table 'flyers' created or already exists.</span><br>";
} else {
    echo "<span style='color:red;'>Error creating table: " . $conn->error . "</span><br>";
}

// Create uploads directory
$target_dir = "../assets/uploads/flyers/";
if (!is_dir($target_dir)) {
    if (mkdir($target_dir, 0777, true)) {
        echo "<span style='color:green;'>Directory 'assets/uploads/flyers/' created.</span><br>";
    } else {
        echo "<span style='color:red;'>Failed to create directory.</span><br>";
    }
} else {
    echo "Directory 'assets/uploads/flyers/' already exists.<br>";
}
?>