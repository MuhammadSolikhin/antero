<?php
require_once __DIR__ . '/../config/database.php';

// 1. Modify students.dojang_id to allow NULL
echo "1. Modifying dojang_id column to allow NULL...\n";
if ($conn->query("ALTER TABLE students MODIFY dojang_id INT NULL")) {
    echo "SUCCESS: Column modified.\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}

// 2. Drop existing foreign key
echo "2. Dropping existing foreign key constraint (students_ibfk_2)...\n";
// Note: Sometimes constraint names vary. We confirmed it is students_ibfk_2 via check_schema.php previously.
if ($conn->query("ALTER TABLE students DROP FOREIGN KEY students_ibfk_2")) {
    echo "SUCCESS: Foreign key dropped.\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
    // Try to find it if name is different? For now assume it's correct based on previous step.
}

// 3. Add new foreign key with ON DELETE SET NULL
echo "3. Adding new foreign key constraint with ON DELETE SET NULL...\n";
$sql = "ALTER TABLE students 
        ADD CONSTRAINT students_fk_dojang 
        FOREIGN KEY (dojang_id) REFERENCES dojangs(id) 
        ON DELETE SET NULL";

if ($conn->query($sql)) {
    echo "SUCCESS: New foreign key added.\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}

echo "Done.\n";
?>