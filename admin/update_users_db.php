<?php
require_once '../config/database.php';

echo "<h2>Users Table Check</h2>";

// Check columns
$columns = [];
$result = $conn->query("SHOW COLUMNS FROM users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

if (!in_array('foto_profil', $columns)) {
    echo "Column 'foto_profil' is MISSING in 'users'. Attempting to add...<br>";
    $sql = "ALTER TABLE users ADD COLUMN foto_profil VARCHAR(255) DEFAULT NULL";
    if ($conn->query($sql)) {
        echo "<span style='color:green; font-weight:bold;'>SUCCESS: Column 'foto_profil' added.</span><br>";
    } else {
        echo "<span style='color:red; font-weight:bold;'>ERROR: " . $conn->error . "</span><br>";
    }
} else {
    echo "<span style='color:green;'>Column 'foto_profil' already exists in 'users'.</span><br>";
}

// Create Upload Directory
$target_dir = "../assets/uploads/profiles/";
if (!is_dir($target_dir)) {
    if (mkdir($target_dir, 0777, true)) {
        echo "Created directory: $target_dir <br>";
    } else {
        echo "Failed to create directory: $target_dir <br>";
    }
} else {
    echo "Directory $target_dir exists.<br>";
}

echo "<hr>";
echo "<h3>Current Columns in 'users' table:</h3>";
$result = $conn->query("SHOW COLUMNS FROM users");
echo "<ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
}
echo "</ul>";
?>