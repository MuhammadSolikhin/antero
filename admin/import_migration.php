<?php
require_once '../config/database.php';

// File to import
$sql_file = '../ezyro_40832602_prestasi.sql';

if (!file_exists($sql_file)) {
    die("Error: File SQL '$sql_file' tidak ditemukan.");
}

// Tables to process (Truncate and Insert)
// We DO NOT drop tables, to preserve new columns/tables created locally.
$tables = [
    'achievements',
    'club_info',
    'coaches',
    'coach_trainings',
    'dojangs',
    'flyers',
    'news',
    'password_resets',
    'students',
    'student_belt_history',
    'users'
];

echo "<h3>Mulai Proses Import Data...</h3>";
echo "Mode: <strong>Preserve Structure</strong> (Hanya Import Data)<br><hr>";

try {
    $conn->autocommit(FALSE); // Start Transaction
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // 1. Truncate Tables
    foreach ($tables as $table) {
        echo "Mengosongkan tabel: <strong>$table</strong>... ";
        if ($conn->query("TRUNCATE TABLE $table")) {
            echo "<span style='color:green'>OK</span><br>";
        } else {
            echo "<span style='color:red'>Gagal (" . $conn->error . ")</span><br>";
            throw new Exception("Gagal truncate $table");
        }
    }

    echo "<hr>";

    // 2. Read and Parse SQL File
    $handle = fopen($sql_file, "r");
    $query = "";
    $count = 0;

    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            // Skip comments and empty lines
            if (empty($line) || substr($line, 0, 2) == '--' || substr($line, 0, 2) == '/*') {
                continue;
            }

            $query .= $line;

            // If query ends with semicolon, execute it
            if (substr(trim($line), -1) == ';') {

                // Only execute INSERT statements
                if (stripos($query, 'INSERT INTO') === 0) {

                    // Simple check if it's one of our tables
                    $valid_table = false;
                    foreach ($tables as $t) {
                        if (stripos($query, "`$t`") !== false || stripos($query, " $t ") !== false) {
                            $valid_table = true;
                            break;
                        }
                    }

                    if ($valid_table) {
                        // FIX: Remove 'instagram', 'tiktok', 'youtube' from dojangs INSERT
                        if (stripos($query, "INSERT INTO `dojangs`") !== false) {
                            // Convert standard INSERT syntax to associative array logic is hard with regex
                            // Instead, simple string replacement for the column definition and then matching values?
                            // No, typically dumps follow `INSERT INTO table (col1, col2) VALUES (val1, val2)`

                            // Let's assume the dump structure: 
                            // INSERT INTO `dojangs` (`id`, `nama_dojang`, `alamat`, `google_maps`, `instagram`, `tiktok`, `youtube`, `created_at`) VALUES

                            $query = str_replace("`instagram`, `tiktok`, `youtube`, ", "", $query);

                            // Now we must remove the corresponding values. The values are tricky because they can be NULL or strings.
                            // However, looking at the dump, they appear at index 4, 5, 6 (0-indexed) or just before created_at.
                            // The values are typically: ..., 'url', 'url', 'url', 'timestamp' )
                            // Or ..., NULL, NULL, NULL, 'timestamp' )

                            // Robust regex replacement for VALUES (...)
                            $query = preg_replace_callback('/VALUES\s*(.*)/s', function ($matches) {
                                $values_part = $matches[1];
                                // Split by tuples like (...), (...)
                                $tuples = preg_split('/\),\s*\(/', trim($values_part, '();'));

                                $new_tuples = [];
                                foreach ($tuples as $tuple) {
                                    $tuple = trim($tuple, '()');
                                    // Split fields by comma, respecting quotes
                                    $fields = str_getcsv($tuple, ",", "'");

                                    // Dojags fields: id, nama, alamat, gmaps, ig, tiktok, yt, created_at
                                    // We want to remove indices 4, 5, 6
                                    if (count($fields) >= 8) {
                                        unset($fields[4]); // ig
                                        unset($fields[5]); // tiktok
                                        unset($fields[6]); // yt
                                    }

                                    // Reconstruct
                                    $new_fields = [];
                                    foreach ($fields as $f) {
                                        if ($f === 'NULL')
                                            $new_fields[] = "NULL";
                                        else
                                            $new_fields[] = "'" . addslashes($f) . "'";
                                    }
                                    $new_tuples[] = "(" . implode(", ", $new_fields) . ")";
                                }
                                return "VALUES " . implode(", ", $new_tuples) . ";";
                            }, $query);
                        }

                        if ($conn->query($query)) {
                            $count++;
                        } else {
                            echo "<div style='color:red; margin-bottom:5px;'>Error Import: " . substr($query, 0, 100) . "... <br>Message: " . $conn->error . "</div>";
                        }
                    }
                }

                $query = ""; // Reset query buffer
            }
        }
        fclose($handle);
    }

    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    $conn->commit();

    echo "<hr><h4>Import Selesai!</h4>";
    echo "Total Query INSERT berhasil: <strong>$count</strong><br>";
    echo "<a href='dashboard.php'>Kembali ke Dashboard</a>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<h3 style='color:red'>Terjadi Error Fatal: " . $e->getMessage() . "</h3>";
}
?>