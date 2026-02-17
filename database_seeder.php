<?php
require_once 'config/database.php';

echo "<h3>Seeding Database...</h3>";

// Function to reset tables (DISABLE FOREIGN KEY CHECKS)
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$tables = ['users', 'dojangs', 'students', 'achievements', 'coaches'];
foreach ($tables as $table) {
    $conn->query("TRUNCATE TABLE $table");
    echo "Reset table: $table <br>";
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "<hr>";

// 1. Seed Users
echo "<b>Seeding Users...</b><br>";
$password = password_hash('password', PASSWORD_DEFAULT);

// Admin
$conn->query("INSERT INTO users (username, password, role) VALUES ('admin', '$password', 'admin')");
echo "- Created Admin: admin / password <br>";

// Students
$students_data = [];
for ($i = 1; $i <= 5; $i++) {
    $username = "siswa$i";
    $conn->query("INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'siswa')");
    $students_data[$i] = $conn->insert_id; // Map index to user_id
    echo "- Created Student: $username / password <br>";
}
echo "<hr>";

// 2. Seed Dojangs
echo "<b>Seeding Dojangs...</b><br>";
$dojangs = [
    ['Garuda Taekwondo Club', 'Jl. Merdeka No. 45, Jakarta Pusat'],
    ['Satria Dojang', 'Jl. Sudirman No. 10, Jakarta Selatan'],
    ['Dragon Fist Academy', 'Jl. Gatot Subroto No. 88, Jakarta Timur']
];
$dojang_ids = [];
foreach ($dojangs as $d) {
    $conn->query("INSERT INTO dojangs (nama_dojang, alamat) VALUES ('$d[0]', '$d[1]')");
    $dojang_ids[] = $conn->insert_id;
    echo "- Created Dojang: $d[0] <br>";
}
echo "<hr>";

// 3. Seed Students Biodata
echo "<b>Seeding Student Biodata...</b><br>";
$names = ['Budi Santoso', 'Siti Aminah', 'Rudi Hartono', 'Dewi Sartika', 'Andi Pratama'];
$belts = ['Putih', 'Kuning', 'Hijau', 'Biru', 'Merah'];
$dates = ['2005-01-01', '2006-05-12', '2007-08-17', '2005-11-20', '2006-02-14'];

foreach ($students_data as $i => $user_id) {
    $name = $names[$i-1];
    $dojang_id = $dojang_ids[array_rand($dojang_ids)];
    $belt = $belts[$i-1];
    $bday = $dates[$i-1];
    
    $sql = "INSERT INTO students (user_id, dojang_id, nama_lengkap, tempat_lahir, tanggal_lahir, tingkatan_sabuk, alamat_domisili) 
            VALUES ($user_id, $dojang_id, '$name', 'Jakarta', '$bday', '$belt', 'Jl. Contoh No. $i')";
    $conn->query($sql);
    $student_db_ids[] = $conn->insert_id; // For achievements
    echo "- Created Biodata for: $name <br>";
}
echo "<hr>";

// 4. Seed Achievements
echo "<b>Seeding Achievements...</b><br>";
$achievements = [
    ['Kejuaraan Pelajar Jakarta', 'Daerah', 1, 'approved'],
    ['Piala Walikota 2024', 'Daerah', 2, 'approved'],
    ['Kejuaraan Nasional Junior', 'Nasional', 3, 'pending'],
    ['Open Tournament International', 'Internasional', 1, 'pending'],
    ['Invitasi Taekwondo Antar Klub', 'Daerah', 1, 'rejected']
];

foreach ($student_db_ids as $sid) {
    // Each student gets random achievements
    $rand_limit = rand(1, 3);
    for($k=0; $k<$rand_limit; $k++) {
        $ach = $achievements[array_rand($achievements)];
        $sql = "INSERT INTO achievements (student_id, nama_kejuaraan, tingkat, juara_ke, file_sertifikat, status, created_at) 
                VALUES ($sid, '{$ach[0]}', '{$ach[1]}', '{$ach[2]}', 'dummy.jpg', '{$ach[3]}', NOW())";
        $conn->query($sql);
    }
    echo "- Added random achievements for Student ID $sid <br>";
}
echo "<hr>";

// 5. Seed Coaches
echo "<b>Seeding Coahces...</b><br>";
$coaches = [
    ['Master Shifu', 'Nasional', 'Ahli Strategi Kyorugi', 'Pelatih Timnas Junior 2020'],
    ['Coach Lee', 'Internasional', 'Spesialis Poomsae', 'Wasit International 2019'],
    ['Sabem Sarah', 'Daerah', 'Fisik & Stamina', 'Mantan Atlet PON']
];

foreach ($coaches as $c) {
    $sql = "INSERT INTO coaches (nama_pelatih, tingkatan, kompetensi, riwayat_pelatihan, foto_pelatih) 
            VALUES ('{$c[0]}', '{$c[1]}', '{$c[2]}', '{$c[3]}', '')";
    $conn->query($sql);
    echo "- Created Coach: {$c[0]} <br>";
}
echo "<hr>";
echo "<h3>Seeding Completed Successfully! <a href='index.php'>Go Home</a></h3>";
?>
