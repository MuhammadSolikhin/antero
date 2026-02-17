<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$student = $conn->query("SELECT id FROM students WHERE user_id = $user_id")->fetch_assoc();


if (!$student) {
    $_SESSION['swal_icon'] = 'warning';
    $_SESSION['swal_title'] = 'Perhatian!';
    $_SESSION['swal_text'] = 'Silakan isi Biodata terlebih dahulu sebelum menginput prestasi.';
    header("Location: biodata.php");
    exit();
}

$student_id = $student['id'];

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kejuaraan = $_POST['nama_kejuaraan'];
    $tahun = $_POST['championship_year'];
    $tingkat = $_POST['tingkat'];
    $juara = $_POST['juara_ke'];

    // File Upload
    $target_dir = "../assets/uploads/";
    if (!is_dir($target_dir))
        mkdir($target_dir, 0777, true);

    // Check if file is selected (Double check server side)
    if (empty($_FILES["file_sertifikat"]["name"])) {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = 'Wajib mengupload bukti sertifikat!';
    } else {
        $file_name = time() . '_' . basename($_FILES["file_sertifikat"]["name"]);
        $target_file = $target_dir . $file_name;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi file (basic)
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "pdf") {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Gagal!';
            $_SESSION['swal_text'] = 'Hanya file JPG, JPEG, PNG & PDF yang diperbolehkan.';
            $uploadOk = 0;
        }

        if ($uploadOk) {
            if (move_uploaded_file($_FILES["file_sertifikat"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO achievements (student_id, nama_kejuaraan, championship_year, tingkat, juara_ke, file_sertifikat, status) 
                        VALUES ($student_id, '$nama_kejuaraan', '$tahun', '$tingkat', '$juara', '$file_name', 'pending')";
                if ($conn->query($sql)) {
                    $_SESSION['swal_icon'] = 'success';
                    $_SESSION['swal_title'] = 'Berhasil!';
                    $_SESSION['swal_text'] = 'Data prestasi berhasil dikirim! Menunggu verifikasi admin.';
                } else {
                    $_SESSION['swal_icon'] = 'error';
                    $_SESSION['swal_title'] = 'Error!';
                    $_SESSION['swal_text'] = $conn->error;
                }
            } else {
                $_SESSION['swal_icon'] = 'error';
                $_SESSION['swal_title'] = 'Gagal!';
                $_SESSION['swal_text'] = 'Gagal mengupload file.';
            }
        }
    }
    header("Location: achievement.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// List Data
// Pagination Logic
$limit = 3;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Get Total Records
$total_results = $conn->query("SELECT COUNT(*) as count FROM achievements WHERE student_id = $student_id")->fetch_assoc()['count'];
$total_pages = ceil($total_results / $limit);

// Get Paginated Data
$prestasi = $conn->query("SELECT * FROM achievements WHERE student_id = $student_id ORDER BY created_at DESC LIMIT $start, $limit");
?>

<div class="container py-5">
    <div class="row">
        <!-- Form Input -->
        <div class="col-md-5 mb-4">
            <div class="glass-card p-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-plus-circle-dotted text-primary"></i> Input Prestasi Baru</h4>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nama Kejuaraan</label>
                        <input type="text" name="nama_kejuaraan" class="form-control" required
                            placeholder="Contoh: Popda 2024">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Kejuaraan</label>
                        <input type="number" name="championship_year" class="form-control" required placeholder="2024"
                            min="2000" max="2099" value="<?php echo date('Y'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tingkatan</label>
                        <select name="tingkat" class="form-select" required>
                            <option value="Daerah">Daerah</option>
                            <option value="Nasional">Nasional</option>
                            <option value="Internasional">Internasional</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Juara Ke-</label>
                        <select name="juara_ke" class="form-select" required>
                            <option value="1">Juara 1 🥇</option>
                            <option value="2">Juara 2 🥈</option>
                            <option value="3">Juara 3 🥉</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Sertifikat / Piagam</label>
                        <input type="file" name="file_sertifikat" class="form-control" required>
                        <div class="form-text">Format: JPG/PNG/PDF. Max 2MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">KIRIM UTK VERIFIKASI</button>
                </form>
            </div>
        </div>

        <!-- History/List -->
        <div class="col-md-7">
            <div class="glass-card p-4 mb-3">
                <h4 class="fw-bold mb-0">Riwayat Prestasi</h4>
            </div>
            <?php if ($prestasi->num_rows > 0): ?>
                <?php while ($row = $prestasi->fetch_assoc()): ?>
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title fw-bold mb-1"><?php echo $row['nama_kejuaraan']; ?> <small
                                            class="text-muted fw-normal">(<?php echo $row['championship_year']; ?>)</small></h5>
                                    <p class="text-muted mb-1">
                                        <span class="badge bg-secondary"><?php echo $row['tingkat']; ?></span>
                                        <span class="badge bg-warning text-dark">Juara <?php echo $row['juara_ke']; ?></span>
                                    </p>
                                    <small class="text-muted"><i class="bi bi-calendar"></i>
                                        <?php echo date('d M Y', strtotime($row['created_at'])); ?></small>
                                </div>
                                <div class="text-end">
                                    <?php
                                    $badges = [
                                        'pending' => 'bg-warning text-dark',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger'
                                    ];
                                    $labels = [
                                        'pending' => 'Menunggu',
                                        'approved' => 'Valid',
                                        'rejected' => 'Ditolak'
                                    ];
                                    ?>
                                    <span
                                        class="badge <?php echo $badges[$row['status']]; ?>"><?php echo $labels[$row['status']]; ?></span>
                                    <div class="mt-2">
                                        <a href="../assets/uploads/<?php echo $row['file_sertifikat']; ?>" target="_blank"
                                            class="btn btn-sm btn-outline-info rounded-pill"><i class="bi bi-eye"></i> Bukti</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

                <!-- Pagination UI -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php if ($page <= 1) {
                                echo 'disabled';
                            } ?>">
                                <a class="page-link rounded-pill px-3 me-2"
                                    href="<?php if ($page <= 1) {
                                        echo '#';
                                    } else {
                                        echo "?page=" . ($page - 1);
                                    } ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if ($page == $i) {
                                    echo 'active';
                                } ?>">
                                    <a class="page-link rounded-circle mx-1 d-flex align-items-center justify-content-center"
                                        style="width: 35px; height: 35px;" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php if ($page >= $total_pages) {
                                echo 'disabled';
                            } ?>">
                                <a class="page-link rounded-pill px-3 ms-2"
                                    href="<?php if ($page >= $total_pages) {
                                        echo '#';
                                    } else {
                                        echo "?page=" . ($page + 1);
                                    } ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">Belum ada data prestasi. Ayo input sekarang!</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>