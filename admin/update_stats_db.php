<?php
require_once '../config/database.php';

echo "<h2>Users Statistics Columns Check</h2>";

// Check columns
$columns = [];
$result = $conn->query("SHOW COLUMNS FROM users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

// Add login_count
if (!in_array('login_count', $columns)) {
    echo "Column 'login_count' is MISSING. Adding...<br>";
    $sql = "ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0";
    if ($conn->query($sql)) {
        echo "<span style='color:green; font-weight:bold;'>SUCCESS: Column 'login_count' added.</span><br>";
    } else {
        echo "<span style='color:red; font-weight:bold;'>ERROR: " . $conn->error . "</span><br>";
    }
} else {
    echo "<span style='color:green;'>Column 'login_count' already exists.</span><br>";
}

// Add last_login
if (!in_array('last_login', $columns)) {
    echo "Column 'last_login' is MISSING. Adding...<br>";
    $sql = "ALTER TABLE users ADD COLUMN last_login DATETIME DEFAULT NULL";
    if ($conn->query($sql)) {
        echo "<span style='color:green; font-weight:bold;'>SUCCESS: Column 'last_login' added.</span><br>";
    } else {
        echo "<span style='color:red; font-weight:bold;'>ERROR: " . $conn->error . "</span><br>";
    }
} else {
    echo "<span style='color:green;'>Column 'last_login' already exists.</span><br>";
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