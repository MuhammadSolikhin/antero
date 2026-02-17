<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: reports.php");
    exit();
}

$type = $_POST['data_type'];
$format = $_POST['format'];

// Title Mapping
$titles = [
    'students' => 'Laporan Data Siswa',
    'achievements' => 'Laporan Data Prestasi',
    'coaches' => 'Laporan Data Pelatih',
    'dojangs' => 'Laporan Data Dojang'
];
$title = $titles[$type] ?? 'Laporan';

// Query Logic
$data = [];
$columns = [];

if ($type == 'students') {
    $sql = "SELECT s.nama_lengkap, u.username, d.nama_dojang, s.tingkatan_sabuk, s.tempat_lahir, s.tanggal_lahir, s.alamat_domisili 
            FROM students s 
            JOIN users u ON s.user_id = u.id 
            JOIN dojangs d ON s.dojang_id = d.id 
            ORDER BY s.nama_lengkap ASC";
    $result = $conn->query($sql);
    $columns = ['Nama Lengkap', 'Username', 'Asal Dojang', 'Sabuk', 'TTL', 'Alamat'];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['nama_lengkap'],
            $row['username'],
            $row['nama_dojang'],
            $row['tingkatan_sabuk'],
            $row['tempat_lahir'] . ', ' . $row['tanggal_lahir'],
            $row['alamat_domisili']
        ];
    }
} elseif ($type == 'achievements') {
    $sql = "SELECT s.nama_lengkap, d.nama_dojang, s.tingkatan_sabuk, 
            a.nama_kejuaraan, a.tingkat, a.juara_ke, a.status 
            FROM achievements a 
            JOIN students s ON a.student_id = s.id 
            JOIN dojangs d ON s.dojang_id = d.id 
            WHERE a.status = 'approved'
            ORDER BY d.nama_dojang ASC, s.nama_lengkap ASC";
    $result = $conn->query($sql);
    $columns = ['Nama Siswa', 'Asal Dojang', 'Sabuk', 'Kejuaraan', 'Tingkat', 'Juara', 'Status'];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['nama_lengkap'],
            $row['nama_dojang'],
            $row['tingkatan_sabuk'],
            $row['nama_kejuaraan'],
            $row['tingkat'],
            $row['juara_ke'],
            strtoupper($row['status'])
        ];
    }
} elseif ($type == 'coaches') {
    $sql = "SELECT * FROM coaches ORDER BY nama_pelatih ASC";
    $result = $conn->query($sql);
    $columns = ['Nama Pelatih', 'Tingkatan', 'Sertifikat'];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['nama_pelatih'],
            $row['tingkatan'],
            $row['info_sertifikat']
        ];
    }
} elseif ($type == 'dojangs') {
    $sql = "SELECT d.nama_dojang, d.alamat, c.nama_pelatih 
            FROM dojangs d 
            LEFT JOIN coaches c ON d.coach_id = c.id 
            ORDER BY d.nama_dojang ASC";
    $result = $conn->query($sql);
    $columns = ['Nama Dojang', 'Alamat', 'Pelatih'];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['nama_dojang'],
            $row['alamat'],
            $row['nama_pelatih'] ?? '-'
        ];
    }
}

// FORMAT OUTPUT
if ($format == 'excel') {
    // Determine number of columns
    // Columns array + 1 for No
    $colCount = count($columns) + 1;
    // Calculate range for AutoFilter (e.g., R1C1:R1C6)
    $range = "R1C1:R1C" . $colCount;

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename={$title}_" . date('Y-m-d') . ".xls");

    // XML Header
    echo '<?xml version="1.0"?>';
    echo '<?mso-application progid="Excel.Sheet"?>';
    ?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
        xmlns:html="http://www.w3.org/TR/REC-html40">
        <Styles>
            <Style ss:ID="Header">
                <Font ss:Bold="1" ss:Color="#FFFFFF" /><Interior ss:Color="#4472C4" ss:Pattern="Solid" /><Alignment ss:Horizontal="Center" /><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" /><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" /><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" /><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" /></Borders>
            </Style>
            <Style ss:ID="Data">
                <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" /><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" /><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" /><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" /></Borders>
            </Style>
        </Styles>
        <Worksheet ss:Name="Sheet1">
            <Table>
                <Row>
                    <Cell ss:StyleID="Header"><Data ss:Type="String">No</Data></Cell>
                    <?php foreach ($columns as $col): ?>
                        <Cell ss:StyleID="Header"><Data ss:Type="String"><?php echo $col; ?></Data></Cell>
                    <?php endforeach; ?>
                </Row>

                <?php
                $no = 1;
                foreach ($data as $row):
                    ?>
                    <Row>
                        <Cell ss:StyleID="Data"><Data ss:Type="Number"><?php echo $no++; ?></Data></Cell>
                        <?php foreach ($row as $cell):
                            // Sanitize string for XML to prevent breaks
                            $cell = htmlspecialchars($cell ?? '', ENT_XML1, 'UTF-8');
                            ?>
                            <Cell ss:StyleID="Data"><Data ss:Type="String"><?php echo $cell; ?></Data></Cell>
                        <?php endforeach; ?>
                    </Row>
                <?php endforeach; ?>

            </Table>
            <x:WorksheetOptions>
                <x:FilterOnPaste />
                <x:Selected />
                <x:Panes>
                    <x:Pane>
                        <x:Number>3</x:Number>
                        <x:ActiveRow>1</x:ActiveRow>
                    </x:Pane>
                </x:Panes>
            </x:WorksheetOptions>
            <x:AutoFilter x:Range="<?php echo $range; ?>" />
        </Worksheet>
    </Workbook>
    <?php
    exit();

} elseif ($format == 'pdf') {
    // Print View for PDF Check
    ?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <title><?php echo $title; ?></title>
        <style>
            body {
                font-family: sans-serif;
                padding: 20px;
            }

            h2 {
                text-align: center;
                margin-bottom: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
                font-size: 12px;
            }

            th {
                background-color: #f2f2f2;
            }

            .meta {
                margin-bottom: 20px;
                font-size: 14px;
            }

            @media print {
                @page {
                    size: landscape;
                }

                body {
                    padding: 0;
                }

                .no-print {
                    display: none;
                }
            }
        </style>
    </head>

    <body onload="window.print()">
        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="window.history.back()">Kembali</button>
            <button onclick="window.print()">Cetak / Simpan PDF</button>
        </div>

        <h2><?php echo $title; ?></h2>

        <div class="meta">
            Dicetak pada: <?php echo date('d F Y H:i'); ?><br>
            Oleh: <?php echo $_SESSION['username']; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <?php foreach ($columns as $col)
                        echo "<th>$col</th>"; ?>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <?php foreach ($row as $cell)
                            echo "<td>$cell</td>"; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>

    </html>
    <?php
}
?>