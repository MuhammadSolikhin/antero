<?php
require_once '../config/database.php';

echo "<h2>Debug Student Query</h2>";

$search = '';
$where = "WHERE s.is_deleted = 0 AND s.dojang_id IS NOT NULL"; // Matching students.php logic

$sql = "SELECT s.*, d.nama_dojang, u.username FROM students s 
                          JOIN dojangs d ON s.dojang_id = d.id 
                          JOIN users u ON s.user_id = u.id 
                          $where
                          ORDER BY s.nama_lengkap ASC LIMIT 1";

echo "<p>SQL: $sql</p>";

$result = $conn->query($sql);
if ($result) {
    echo "<p>Rows: " . $result->num_rows . "</p>";
    if ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r(array_keys($row));
        echo "</pre>";

        echo "<p>Status value: " . (isset($row['status']) ? $row['status'] : 'UNDEFINED') . "</p>";
    } else {
        echo "No rows found.";
    }
} else {
    echo "Query Error: " . $conn->error;
}

echo "<h3>Table Structure</h3>";
$res = $conn->query("DESCRIBE students");
while ($r = $res->fetch_assoc()) {
    echo $r['Field'] . " - " . $r['Type'] . "<br>";
}
?>