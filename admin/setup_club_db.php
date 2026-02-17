<?php
require_once '../config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS club_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    club_name VARCHAR(100) DEFAULT 'Ankero Taekwondo Club',
    instagram VARCHAR(255),
    tiktok VARCHAR(255),
    youtube VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table club_info created successfully. ";
} else {
    echo "Error creating table: " . $conn->error;
}

$sql_insert = "INSERT INTO club_info (id, club_name) SELECT 1, 'Ankero Taekwondo Club' WHERE NOT EXISTS (SELECT 1 FROM club_info WHERE id = 1)";
if ($conn->query($sql_insert) === TRUE) {
    echo "Default data inserted/checked successfully.";
} else {
    echo "Error inserting data: " . $conn->error;
}
?>