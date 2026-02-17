<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$filename = "data_siswa_terhapus_" . date('Ymd') . ".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Fetch Data
$students = $conn->query("SELECT s.*, d.nama_dojang, u.username FROM students s 
                          JOIN dojangs d ON s.dojang_id = d.id 
                          JOIN users u ON s.user_id = u.id 
                          WHERE s.is_deleted = 1
                          ORDER BY s.deleted_at DESC");

?>
<!DOCTYPE html>
<html>

<head>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h3>Data Siswa Terhapus (Trash)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Dojang Terakhir</th>
                <th>Tingkatan Sabuk</th>
                <th>Tempat Lahir</th>
                <th>Tanggal Lahir</th>
                <th>Alamat Domisili</th>
                <th>Tanggal Dihapus</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if ($students->num_rows > 0):
                while ($row = $students->fetch_assoc()):
                    ?>
                    <tr>
                        <td>
                            <?php echo $no++; ?>
                        </td>
                        <td>
                            <?php echo $row['username']; ?>
                        </td>
                        <td>
                            <?php echo $row['nama_lengkap']; ?>
                        </td>
                        <td>
                            <?php echo $row['nama_dojang']; ?>
                        </td>
                        <td>
                            <?php echo $row['tingkatan_sabuk']; ?>
                        </td>
                        <td>
                            <?php echo $row['tempat_lahir']; ?>
                        </td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($row['tanggal_lahir'])); ?>
                        </td>
                        <td>
                            <?php echo $row['alamat_domisili']; ?>
                        </td>
                        <td>
                            <?php echo date('d/m/Y H:i', strtotime($row['deleted_at'])); ?>
                        </td>
                    </tr>
                <?php
                endwhile;
            else:
                ?>
                <tr>
                    <td colspan="9" style="text-align:center;">Tidak ada data.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>