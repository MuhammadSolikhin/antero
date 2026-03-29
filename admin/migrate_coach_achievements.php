<?php
/**
 * Migration: Create coach_achievements table.
 * Run this file once via browser or CLI.
 */
require_once '../config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS `coach_achievements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `coach_id` int NOT NULL,
  `nama_kejuaraan` varchar(150) NOT NULL,
  `championship_year` int DEFAULT NULL,
  `tingkat` enum('Daerah','Nasional','Internasional') NOT NULL,
  `juara_ke` int NOT NULL,
  `file_sertifikat` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `coach_id` (`coach_id`),
  CONSTRAINT `coach_achievements_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

echo "<h3>Migration: Create coach_achievements table</h3>";

if ($conn->query($sql)) {
    echo "<p style='color:green;'>✅ Table coach_achievements created successfully!</p>";
} else {
    echo "<p style='color:orange;'>⚠️ " . $conn->error . "</p>";
}

echo "<p><strong>Migration complete!</strong></p>";
?>
