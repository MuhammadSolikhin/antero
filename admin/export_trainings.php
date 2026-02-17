<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

// Filename
$filename = "Data_Pelatihan_Diklat_" . date('Ymd') . ".xls";

// Headers for download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Manual Table Construction for Excel (Simple HTML approach works fine for basic .xls)
?>
<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Asal Dojang</th>
            <th>Nama Pelatihan / Diklat</th>
            <th>Tahun</th>
            <th>Status</th>
            <th>Tanggal Submit</th>
            <th>Catatan Admin</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT st.*, s.nama_lengkap, d.nama_dojang 
                FROM student_trainings st 
                JOIN students s ON st.student_id = s.id 
                JOIN dojangs d ON s.dojang_id = d.id
                WHERE st.status = 'verified' 
                ORDER BY st.year DESC, st.created_at DESC";

        // Only exporting verified data usually makes sense, but user said "export excel" so verified is safe assumption.
        // Or should I export ALL? Usually "Export Data" implies confirmed data. 
        // Let's stick to Verified for now as that's most useful.
        
        $result = $conn->query($sql);
        $no = 1;
        while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td>
                    <?php echo $no++; ?>
                </td>
                <td>
                    <?php echo $row['nama_lengkap']; ?>
                </td>
                <td>
                    <?php echo $row['nama_dojang']; ?>
                </td>
                <td>
                    <?php echo $row['name']; ?>
                </td>
                <td>
                    <?php echo $row['year']; ?>
                </td>
                <td>Verified</td>
                <td>
                    <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                </td>
                <td>
                    <?php echo $row['admin_note']; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>