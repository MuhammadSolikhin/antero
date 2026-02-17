<?php
require_once '../config/database.php';

echo "<h2>Database Fixer</h2>";

// 1. Check students table columns
echo "Checking 'students' table structure...<br>";
$columns = [];
$result = $conn->query("SHOW COLUMNS FROM students");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

if (!in_array('foto_sertifikat', $columns)) {
    echo "Column 'foto_sertifikat' is MISSING. Attempting to add...<br>";
    $sql = "ALTER TABLE students ADD COLUMN foto_sertifikat VARCHAR(255) AFTER tingkatan_sabuk";
    if ($conn->query($sql)) {
        echo "<span style='color:green; font-weight:bold;'>SUCCESS: Column 'foto_sertifikat' added.</span><br>";
    } else {
        echo "<span style='color:red; font-weight:bold;'>ERROR: " . $conn->error . "</span><br>";
    }
} else {
    echo "<span style='color:green;'>Column 'foto_sertifikat' already exists.</span><br>";
}

// 2. Create Upload Directory
$target_dir = "../assets/uploads/certificates/";
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
echo "<h3>Current Columns in 'students' table:</h3>";
$result = $conn->query("SHOW COLUMNS FROM students");
echo "<ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
}
echo "</ul>";

echo "<br><a href='dojang_detail.php?id=12'>Go Back to Dojang Detail</a>"; // Replace ID with generic or just link
?>