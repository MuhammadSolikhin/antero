<?php
/**
 * Migration: Add tinggi_badan and berat_badan columns to students and coaches tables.
 * Run this file once via browser or CLI.
 */
require_once '../config/database.php';

$queries = [
    "ALTER TABLE `students` ADD COLUMN `tinggi_badan` DECIMAL(5,1) DEFAULT NULL AFTER `alamat_domisili`",
    "ALTER TABLE `students` ADD COLUMN `berat_badan` DECIMAL(5,1) DEFAULT NULL AFTER `tinggi_badan`",
    "ALTER TABLE `coaches` ADD COLUMN `tinggi_badan` DECIMAL(5,1) DEFAULT NULL AFTER `foto_pelatih`",
    "ALTER TABLE `coaches` ADD COLUMN `berat_badan` DECIMAL(5,1) DEFAULT NULL AFTER `tinggi_badan`",
];

echo "<h3>Migration: Add Tinggi Badan & Berat Badan</h3>";

foreach ($queries as $sql) {
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>✅ OK: " . htmlspecialchars($sql) . "</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ " . $conn->error . " — " . htmlspecialchars($sql) . "</p>";
    }
}

echo "<p><strong>Migration complete!</strong></p>";
?>
